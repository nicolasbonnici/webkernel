<?php

namespace Library\Core\App;

use app\Entities\Config;
use Library\Core\FileSystem\File;
use Library\Core\Json\Json;

/**
 * Configuration component
 * Class Config
 * @package Core\App
 *
 */
class Configuration {

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

    /**
     * Configuration constructor with optional bundle name
     * @param string $sBundleName   Bundle name (optional)
     */
    public function __construct($sBundleName)
    {
        $this->build($sBundleName);
    }

    /**
     * Build all available configurations from project or bundle scope and database
     * @param string $sBundleName
     */
    private function build($sBundleName)
    {
        // Load global project configuration then load bundle's json configuration if available
        $this->loadFromIniFile(CONF_PATH)
            ->loadFromJsonFile(BUNDLES_PATH . $sBundleName . self::DEFAULT_BUNDLE_CONFIGURATION_RELATIVE_PATH);
    }

    /**
     * @todo load from config table (no need to build entities with them just request an array and pass it to instance setter)
     */
    protected function loadFromDatabase()
    {

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
            return $this->setConfiguration(parse_ini_file(PROJECT_CONF_PATH, true));
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
     * Store or update database configuration
     *
     * @param string $sKey
     * @param mixed $mValue
     * @param string $sBundle
     *
     * @return bool
     */
    public function store($sKey, $mValue, $sBundle = null)
    {
        if (empty($sKey) === false && empty($mValue) === false) {
            $oConfiguration = new Config();
            $oConfiguration->name       = $sKey;
            $oConfiguration->value      = $mValue;
            $oConfiguration->bundle     = $sBundle;
            $oConfiguration->created    = time();
            $oConfiguration->lastupdate = time();

            // Store on instance
            $this->aConfiguration[$sKey] = $mValue;

            return $oConfiguration->add();
        }
        return false;
    }

    /**
     * Delete configuration from database with his key
     * @param $sConfigurationKey
     * @return bool
     */
    public function delete($sConfigurationKey)
    {
        $oConfiguration = new Config();
        $oConfiguration->loadByParameters(
            array(
                'name' => $sConfigurationKey
            )
        );

        if ($oConfiguration->isLoaded() === true) {
            return $oConfiguration->delete();
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

}

class ConfigurationException extends \Exception {}