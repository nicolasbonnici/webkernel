<?php
namespace Library\Core;

/**
 *  Data Fixtures managment class
 *  Import/Export data fixtures in and out your SGBD using pdo
 *
 * @uses Json
 * @author Nicolas Bonnici <nicolasbonnici@gmail.com>
 */
class Fixtures
{

    /**
     * Current instance entity
     * @var Entity
     */
    private $oEntity;

    /**
     * Current instance entity collection
     * @var EntitiesCollection
     */
    private $oEntitiesCollection;

    /**
     * Path to the current instance entity deploy script
     * @var string
     */
    private $sDeployScriptPath;

    /**
     * Instance constructor
     * @param unknown $sEntityName
     * @throws FixturesException
     */
    public function __construct($sEntityName)
    {
        if (! Files::exists(ENTITIES_DEPLOY_PATH . $sEntityName . '.sql')) {
            throw new FixturesException('No schema found to properly deploy this Entity');
        } else {
            $this->sDeployScriptPath = ENTITIES_DEPLOY_PATH . $sEntityName . '.sql';

            if (! $this->isDeployed()) {
                if (! $this->deploy()) {
                    throw new FixturesException('Unable to deploy entity on database');
                }
            }

            try {
                $this->oEntity = new $sEntityName;
                $this->oEntitiesCollection = new $sEntityNameCollection;
            } catch (\Library\Core\EntityException $oEntityException) {
                throw new FixturesException($oEntityException->getMessage());
            } catch (\Library\Core\EntitiesCollectionException $oEntitiesCollectionException) {
                throw new FixturesException($oEntitiesCollectionException->getMessage());
            }

        }

    }

    /**
     * Import data fixtures
     */
    protected function import()
    {

    }

    /**
     * Export data fixtures
     */
    protected function export()
    {

    }

    /**
     * Deploy a new entity on SGBD
     */
    protected function deploy()
    {
        // @todo
        /**
        * "
        SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
        SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
        SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

        DUMP DE LA STRUCTURE


        */
    }

    protected function isDeployed()
    {

    }

}

class FixturesException extends \Exception
{
}
