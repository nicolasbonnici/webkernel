<?php
namespace Library\Core\Entity\Dataset;

use Library\Core\Bootstrap;
use Library\Core\Entity\Entity;
use Library\Core\Entity\EntityCollection;
use Library\Core\Exception\CoreException;
use Library\Core\FileSystem\File;
use Library\Core\Json\Json;


/**
 * Manage Entities records imports and exports
 *
 * Class Dataset
 * @package Library\Core\Dataset
 */
class Dataset
{

    /**
     * Source Entity instance
     *
     * @var Entity
     */
    protected $oEntity;

    /**
     * Export created files log
     *
     * @var array
     */
    protected $aExports;

    /**
     * @var EntityCollection
     */
    protected $oEntityCollection;

    public function __construct(Entity $oEntity)
    {
        $this->oEntity = $oEntity;
        # Also build a collection instance
        $sEntityCollectionClassName = $oEntity->computeCollectionClassName();
        $this->oEntityCollection = new $sEntityCollectionClassName();
    }

    /**
     * Import dataset
     *
     * @param Json $oJson
     * @return bool
     * @throws DatasetException
     */
    public function import(Json $oJson)
    {
        $aLog = array();
        $aDataset = $oJson->getAsArray();
        foreach ($aDataset as $sEntityClassName => $aRecords) {
            # check the dataset Entity type
            if ($this->oEntity->getChildClass() !== $sEntityClassName) {
                throw new DatasetException(
                    DatasetException::getError(DatasetException::ERROR_ENTITY_TYPE_MISMATCH),
                    DatasetException::ERROR_ENTITY_TYPE_MISMATCH
                );
            }

            if (count($aRecords) > 0) {
                # Sort by primary keys to first insert the lower value
                sort($aRecords);
                foreach ($aRecords as $iEntityId => $aDataset) {
                    $aLog[$iEntityId] = $this->importEntity($aDataset);
                }
            }
        }

        return (bool) (in_array(false, $aLog) === false);
    }

    /**
     * Export dataset
     *
     * @return bool
     */
    public function export()
    {
        $this->oEntityCollection->load();
        $aRecords = $this->oEntityCollection->getAsArray();

        # Store instance class name
        $aExport = array(
            $this->oEntity->getChildClass() => $aRecords
        );
        $oJsonExport = new Json($aExport);
        return $this->createExportFile($oJsonExport);
    }

    /**
     * Import and create a new Entity Record from a given two dimensional array
     *
     * @param array $aDataset
     * @return bool
     * @throws \Library\Core\Entity\EntityException
     */
    protected function importEntity(array $aDataset)
    {
        # Clone source entity instance
        $oEntity = clone $this->oEntity;
        foreach ($aDataset as $sKey => $mValue) {
            $oEntity->$sKey = $mValue;
        }
        return $oEntity->add();
    }

    /**
     * Create the exported json file
     *
     * @param Json $oJson
     * @return bool
     */
    protected function createExportFile(Json $oJson)
    {
        $sExportFilePath = $this->computeExportPath() . $this->oEntity->getEntityName() . '_' . time() . '.json';
        if (File::create($sExportFilePath) === false) {
            return false;
        }

        # Log exported dataset
        $this->aExports[] = $sExportFilePath;

        return File::write($sExportFilePath, $oJson->__toString());
    }

    /**
     * The compute the export path
     *
     * @return string
     */
    protected function computeExportPath()
    {
        return Bootstrap::getPath(Bootstrap::PATH_APP) . 'exports' . DIRECTORY_SEPARATOR;
    }

    /**
     * Get exported dataset files log
     *
     * @return array
     */
    public function getExports()
    {
        return $this->aExports;
    }
}

class DatasetException extends CoreException
{
    const ERROR_ENTITY_TYPE_MISMATCH = 2;

    public static $aErrors = array(
        self::ERROR_ENTITY_TYPE_MISMATCH => 'Entity type mismatch.'
    );
}