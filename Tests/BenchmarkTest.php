<?php
namespace Library\Core\Tests;

use Library\Core\Database\Pdo;
use Library\Core\Entity\Generator;
use Library\Core\Test;
use Library\Core\Tests\Dummy\Entities\Collection\DummyCollection;
use Library\Core\Tests\Dummy\Entities\Dummy;


/**
 * Class Benchmark
 * @package Library\Core
 */
class BenchmarkTest extends Test
{

    protected $aBenchmark = array();

    public function testInsert()
    {
        # First generate 1000 Dummy
        $this->aBenchmark['create_1000'] = $this->generateDummies(1000);
        # First generate 10 000 Dummy
        $this->aBenchmark['create_10000'] = $this->generateDummies(10000);

//        # First generate 100 000 Dummy
//        $this->aBenchmark['create_100000'] = $this->generateDummies(100000);

        # Make some assertions to ensure there's no step effect
        $this->assertTrue(
            $this->aBenchmark['create_1000'] * 100 > $this->aBenchmark['create_10000'],
            'Low performance detected, step on creating Dummy entities.'
        );

        # Render benchmark results
        $this->renderResults();
    }

    public function testLoad()
    {
        $this->aBenchmark['load_1000'] = $this->loadDummies(1000);
        $this->aBenchmark['load_10000'] = $this->loadDummies(10000);
var_dump($this->aBenchmark['load_1000'] * 100, $this->aBenchmark['load_10000']);
        $this->assertTrue(
            $this->aBenchmark['load_1000'] * 100 > $this->aBenchmark['load_10000'],
            'Low performance detected, step on loading Dummy entities.'
        );
    }

    protected function renderResults()
    {
        $sOutput = "";
        foreach ($this->aBenchmark as $sBenchmarkTest => $sBenchmarkResult) {
            $sOutput .= "\nRendering time for " . $sBenchmarkTest . ': ' . $sBenchmarkResult . "milliseconds \n";
        }
        echo $sOutput;
    }

    public static function tearDownAfterClass()
    {
        # Truncate tables
        $aLog = array();
        $sQueries = 'SET FOREIGN_KEY_CHECKS=0;
            TRUNCATE TABLE `dummy`;
            TRUNCATE TABLE `dummy1`;
            TRUNCATE TABLE `dummy2`;
            TRUNCATE TABLE `dummy3`;
            TRUNCATE TABLE `dummy4`;
            TRUNCATE TABLE `dummyDummy3`;
            SET FOREIGN_KEY_CHECKS=1;';
        foreach (explode(';', $sQueries) as $sQuery) {
            $aLog[] = $oStatement = Pdo::dbQuery($sQuery);
        }
        return (bool) (in_array(false, $aLog) === false);
    }

    protected function generateDummies($iIterationNumber)
    {
        $iGenTime = microtime(true);

        #Generate 1000 entities then benchmark loading
        $oGenerator = new Generator(new Dummy(), $iIterationNumber);

        if ($oGenerator->process(new Dummy(), $iIterationNumber) === false) {
            die('Benchmark unable to generate ' . $iIterationNumber . ' Dummy entities');
        }

        return microtime(true) - $iGenTime;
    }

    protected function loadDummies($iIterationNumber)
    {
        $iGenTime = microtime(true);

        $oDummyCollection  = new DummyCollection();
        $oDummyCollection->load(array(), $iIterationNumber);
        return (bool) ($oDummyCollection->count() === $iIterationNumber);
    }

}