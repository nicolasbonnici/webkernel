<?php
namespace Core;

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
     * @var EntityCollection
     */
    private $oEntityCollection;

    /**
     * Path to the current instance entity deploy script
     * @var string
     */
    private $sDeployScriptPath;

    /**
     * Path to the current instanciated entity alter script
     * @var string
     */
    private $sAlterScriptPath;

    /**
     * Instance constructor
     * @param unknown $sEntityName
     * @throws FixturesException
     */
    public function __construct($sEntityName)
    {
        if (! File::exists(ENTITIES_DEPLOY_PATH . $sEntityName . '.sql')) {
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
                $this->oEntityCollection = new $sEntityNameCollection;
            } catch (\Core\Orm\EntityException $oEntityException) {
                throw new FixturesException($oEntityException->getMessage());
            } catch (\Core\Orm\EntityCollectionException $oEntityCollectionException) {
                throw new FixturesException($oEntityCollectionException->getMessage());
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

    /**
     * Alter and update an already deployed entity
     */
    protected function update()
    {

    	$this->sAlterScriptPath = ENTITIES_UPDATE_PATH . $sEntityName . '.sql';

    	if (! $this->isDeployed()) {
    		throw new FixturesException('Unable to update an entity not deployed on database');
    	} elseif (! File::exists($this->sAlterScriptPath)) {
		  throw new FixturesException('Unable to find the update script for entity: ' . $sEntityname);
    	} else {
    		// Loader et run le script d'alter
    	}

    }

    protected function isDeployed()
    {

    }

}

class FixturesException extends \Exception
{
}
