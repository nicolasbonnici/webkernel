<?php

namespace Library\Core\App;

use app\Entities\Config;
use app\Entities\Collection\ConfigCollection;
use Library\Core\Bootstrap;
use Library\Core\FileSystem\File;
use Library\Core\Json\Json;

/**
 * Configuration component
 *
 * Class Config
 * @package Core\App
 *
 */
class Configuration {

    /**
     * Separator between the bundle name and the configuration key (eg: bundleName.aKeyThatYouDontKnowTheFuckWhyItExist)
     * @var string
     */
    const CONFIGURATION_KEY_SEPARATOR = '.';

    /**
     * Default relative path to store bundles configuration (for each bundle)
     */
    const DEFAULT_BUNDLE_CONFIGURATION_RELATIVE_PATH = '/Config/bundle.json';

    /**
     * App global configuration parsed from the config.ini file
     *
     * @var array
     */
    protected $aConfiguration = array();

    protected $sBundleName;

    /**
     * Configuration constructor with optional bundle name
     * @param string $sBundleName   Bundle name (optional)
     */
    public function __construct($sBundleName = 'sample')
    {
        $this->sBundleName = $sBundleName;
        $this->build();
    }

    /**
     * Build all available configurations from project or bundle scope and database
     */
    private function build()
    {
        // Load global project configuration then load bundle's json configuration if available
        $this->loadFromIniFile(Bootstrap::getPath(Bootstrap::PATH_CONFIG))
            ->loadFromDatabase();
    }

    /**
     * Get configuration from his key
     *
     * @param string $sConfigurationKey
     * @return mixed
     */
    public function get($sConfigurationKey)
    {
        return (isset($this->aConfiguration[$sConfigurationKey])) ? $this->aConfiguration[$sConfigurationKey] : null;
    }

    /**
     * Set a configuration variable (create it if not exists)
     *
     * @param $sKey
     * @param $mValue
     * @return bool
     * @throws \Library\Core\Orm\EntityException
     */
    public function set($sKey, $mValue)
    {
        $oConfig = $this->loadConfigByKeyName($sKey);
        if (is_null($oConfig) === true) {
            return $this->store($sKey, $mValue);
        } else {
            $oConfig->value = $mValue;
            return $oConfig->update();
        }
    }


    /**
     * Delete configuration from database with his key
     * @param $sConfigurationKey
     * @return bool
     */
    public function delete($sConfigurationKey)
    {
        $oConfig = $this->loadConfigByKeyName($sConfigurationKey);
        if ($oConfig != null) {
            return $oConfig->delete();
        }
        return false;
    }

    /**
     * Get configurations
     * @return array
     */
    public function getConfiguration()
    {
        return $this->aConfiguration;
    }

    /**
     * Set one or more configurations vars
     *
     * @param array $aConfigurations
     * @return Configuration
     */
    public function setConfiguration(array $aConfigurations)
    {
        $this->aConfiguration = array_merge($this->aConfiguration, $aConfigurations);
        return $this;
    }

    /**
     * Store or update database configuration
     *
     * @param string $sKey
     * @param mixed $mValue
     *
     * @return bool
     */
    private function store($sKey, $mValue)
    {
        if (empty($sKey) === false && empty($mValue) === false) {
            $oConfiguration = new Config();
            $oConfiguration->name       = $sKey;
            $oConfiguration->value      = $mValue;
            $oConfiguration->bundle     = $this->sBundleName;
            $oConfiguration->created    = time();
            $oConfiguration->lastupdate = time();

            // Store on instance
            $this->aConfiguration[$sKey] = $mValue;

            return $oConfiguration->add();
        }
        return false;
    }


    /**
     * Load all configuration found for the given bundle at instance
     */
    protected function loadFromDatabase()
    {
        $aConfigs = array();
        $oConfigCollection = new ConfigCollection();
        $oConfigCollection->loadByParameters(
            array(
                'bundle' => $this->sBundleName
            )
        );
        if ($oConfigCollection->count() > 0) {
            foreach ($oConfigCollection as $oConfig) {
                $aConfigs[$oConfig->name] = $oConfig->value;
            }
            $this->setConfiguration($aConfigs);
        }
    }

    /**
     * Load configuration vars from a ini file
     *
     * @param string $sAbsoluteFilePath
     * @return Configuration
     */
    protected function loadFromIniFile($sAbsoluteFilePath)
    {
        if (File::exists($sAbsoluteFilePath) === true) {
            return $this->setConfiguration(parse_ini_file(Bootstrap::getPath(Bootstrap::PATH_CONFIG), true));
        }
        return $this;
    }

    /**
     * Load configuration from a json file
     *
     * @param string $sAbsoluteFilePath
     * @return Configuration
     */
    protected function loadFromJsonFile($sAbsoluteFilePath)
    {
        if (File::exists($sAbsoluteFilePath) === true) {
            $sJsonConfiguration = File::getContent($sAbsoluteFilePath);
            if (empty($sJsonConfiguration) === false) {
                $oJson = new Json($sJsonConfiguration);
                return $this->setConfiguration($oJson->getAsArray());
            }
        }
        return $this;
    }

    /**
     * Load a config by key name
     *
     * @param string $sKey
     * @return Config|null
     * @throws \Library\Core\Orm\EntityException
     */
    private function loadConfigByKeyName($sKey)
    {
        try {
            $oConfiguration = new Config();
            $oConfiguration->loadByParameters(
                array(
                    'bundle' => $this->sBundleName,
                    'name' => $sKey
                )
            );
            if ($oConfiguration->isLoaded() === true) {
                return $oConfiguration;
            }
            return null;
        } catch (\Exception $oException) {
            return null;
        }
    }

}

class ConfigurationException extends \Exception {}