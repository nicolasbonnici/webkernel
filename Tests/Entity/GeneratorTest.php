<?php
namespace Library\Core\Tests\Entity;

use Library\Core\Database\Pdo;
use \Library\Core\Test;
use Library\Core\Entity\Generator;
use Library\Core\Tests\Dummy\Entities\Dummy;


/**
 * Generator component unit tests
 * 
 * @author Nicolas Bonnici <nicolasbonnici@gmail.com>
 */
class GeneratorTest extends Test
{
    /**
     * @var Generator
     */
    private $oEntityGeneratorInstance;

    protected function setUp()
    {
        $this->oEntityGeneratorInstance = new Generator();
    }

    public function testConstructor()
    {
        $this->assertTrue(
            $this->oEntityGeneratorInstance instanceof Generator
        );
    }

    public function testGenerateDummyEntities()
    {
        $this->assertTrue(
            $this->oEntityGeneratorInstance->process(new Dummy(), 100),
            'Unable to generate a thousand Dummy entities.'
        );

        $aDummyEntities = $this->oEntityGeneratorInstance->getGeneratedEntities();

        $this->assertTrue(
            is_array($aDummyEntities)
        );

        $this->assertEquals(
            100,
            count($aDummyEntities)
        );
    }

    /**
     * This method is called after the last test of this test class is run.
     *
     * @since Method available since Release 3.4.0
     */
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
}