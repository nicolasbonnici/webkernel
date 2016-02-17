<?php
namespace Library\Core\Tests;

use Library\Core\Database\Pdo;
use Library\Core\Entity\Generator;
use Library\Core\Test;
use Library\Core\Tests\Dummy\Entities\Collection\DummyCollection;
use Library\Core\Tests\Dummy\Entities\Dummy;


/**
 * This class allow to perform Benchmark on framework components
 *
 * Class Benchmark
 * @package Library\Core
 */
class BenchmarkTest extends Test
{
    protected $aBenchmark = array();

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        if(self::loadUser(true) === false) {
            die('Unable to load Test User');
        }
    }


    public function testEntityComponent()
    {
        # First generate 1000 Dummy
        $this->aBenchmark['create_100'] = $this->generateDummies(100);
        # First generate 10 000 Dummy
//        $this->aBenchmark['create_1000'] = $this->generateDummies(1000);

//        # First generate 100 000 Dummy
//        $this->aBenchmark['create_100000'] = $this->generateDummies(100000);

        # Load a hundred entities
        $this->aBenchmark['load_100'] = $this->loadDummies(100);
        # Load a thousand entities
//        $this->aBenchmark['load_1000'] = $this->loadDummies(1000);

        # Render benchmark results
        $this->renderResults();
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

        # Create Dummy instance with a generated root user for Acl layer
        $oDummy = new Dummy(null, 'FR_fr');

        #Generate 1000 entities then benchmark loading
        $oGenerator = new Generator(self::$oUser);
        if ($oGenerator->process($oDummy, $iIterationNumber) === false) {
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