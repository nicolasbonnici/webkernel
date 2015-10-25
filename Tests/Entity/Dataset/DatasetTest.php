<?php
namespace Library\Core\Tests\Entity\Dataset;

use Library\Core\Bootstrap;
use Library\Core\Database\Pdo;
use Library\Core\Entity\Dataset\Dataset;
use Library\Core\Entity\Generator;
use Library\Core\FileSystem\File;
use Library\Core\Json\Json;
use Library\Core\Test;
use Library\Core\Tests\Dummy\Entities\Dummy;

class DatasetTest extends Test
{
    /**
     * @var Dataset
     */
    private $oDataset;

    protected function setUp()
    {
        $this->oDataset = new Dataset(new Dummy());
    }

    /**
     * Display class name before run all testcase methods
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        # Add some dummies data
        $oEntityGenerator = new Generator();
        $oEntityGenerator->process(new Dummy(), 100);

    }


    public function testConstructor()
    {
        $this->assertInstanceOf(
            'Library\Core\Entity\Dataset\Dataset',
            $this->oDataset
        );
    }

    public function testExportImport()
    {
        # Count total row first
        $iInitialDummiesCount = Pdo::dbQuery('SELECT COUNT(1) FROM `dummy`')->fetchColumn();

        if ($iInitialDummiesCount === 0) {
            $oGenerator = new Generator();
            $oGenerator->process(new Dummy(), 100);
            $iInitialDummiesCount = Pdo::dbQuery('SELECT COUNT(1) FROM `dummy`')->fetchColumn();
        }

        $this->assertTrue(
            $this->oDataset->export(),
            'Unable to export a Dataset'
        );

        # Load exported dataset
        $aExportedDatasets = $this->oDataset->getExports();

        $this->assertNotEmpty(
            $aExportedDatasets,
            'No exported dataset was found.'
        );

        $sLastExportFilePath = array_pop($aExportedDatasets);

        $oJsonExported = new Json(File::getContent($sLastExportFilePath));
        $aDataset = $oJsonExported->getAsArray();
        $oDummy = new Dummy();

        # Assert that the exported dataset count the same row number as previously requested on database
        $this->assertEquals(
            $iInitialDummiesCount,
            count($aDataset[$oDummy->getChildClass()])
        );

        # First truncate table
        # Truncate tables
        $aLog = array();
        $sQueries = 'SET FOREIGN_KEY_CHECKS=0;
            TRUNCATE TABLE `dummy`;
            SET FOREIGN_KEY_CHECKS=1;';
        foreach (explode(';', $sQueries) as $sQuery) {
            $aLog[] = $oStatement = Pdo::dbQuery($sQuery);
        }
        if (in_array(false, $aLog) === false) {
            # Import exported table rows
            $this->assertTrue(
                $this->oDataset->import(new Json(File::getContent($sLastExportFilePath))),
                'Unable to import a dataset'
            );
        } else {
            $this->assertTrue(
                $oStatement,
                'Unable to truncate Dummy table before test Dataset import method.'
            );
        }

        # Delete export
        $this->assertTrue(
            File::delete($sLastExportFilePath),
            'Unable to delete last Dataset export'
        );
    }

}