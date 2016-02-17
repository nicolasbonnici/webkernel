<?php
namespace Library\Core\Tests\Entity;

use Library\Core\Tests\Test;
use Library\Core\Database\Pdo;
use Library\Core\Entity\Importer;
use Library\Core\Tests\Dummy\Entities\Dummy;

/**
 * Entity Importer component unit tests
 * 
 * @author Nicolas Bonnici <nicolasbonnici@gmail.com>
 */
class ImporterTest extends Test
{
    /**
     * @var Importer
     */
    private $oEntityImporter;

    protected function setUp()
    {
        $oDummy = new Dummy();
        $this->oEntityImporter = new Importer($oDummy->getChildClass());
    }

    public function testConstructor()
    {
        $this->assertInstanceOf(
            'Library\Core\Entity\Importer',
            $this->oEntityImporter
        );
    }

    public function testIsLoaded()
    {
        $this->assertTrue(
            $this->oEntityImporter->isLoaded(),
            'Unable to load Entities Importer component'
        );
    }

    public function testProcess()
    {
        $this->assertTrue(
            $this->oEntityImporter->process(),
            'Unable to import Dummy Entity on database.'
        );

        # Assert that the table exists
        $oStatement = Pdo::dbQuery('SHOW COLUMNS FROM `dummy`');
        $this->assertTrue(
            $oStatement !== false
        );

        # Assert the table contain at least one field
        $aColumns = $oStatement->fetchAll();
        $this->assertNotEmpty(
            $aColumns
        );
    }

}