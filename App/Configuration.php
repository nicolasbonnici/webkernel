<?php

namespace Core\App;

use Core\File;

/**
 * Configuration component
 * Class Config
 * @package Core\App
 */

class Configuration {


    /**
     * App global configuration parsed from the config.ini file
     *
     * @var array
     */
    protected $aConfig = array();

    public function __construct(array $aConfigFilePath = array())
    {
        $this->load();
    }

    /**
     * Parse global config from a ini file
     * @see app/config/
     *
     * @todo mettre en cache et merger les deux methodes Projet et bundle
     *
     * @throws AppException
     */
    private function load()
    {
        // Project global configuration
        if (File::exists(PROJECT_CONF_PATH) === false) {
            throw new ConfigException('Unable to load core configuration: ' . PROJECT_CONF_PATH);
        } else {
            $this->aConfig = parse_ini_file(PROJECT_CONF_PATH, true);
        }

        // Bundle configuration
        if (File::exists(BUNDLES_PATH . self::$oRouterInstance->getBundle() . '/Config/bundle.json')) {
            $sBundleConfig = File::getContent(BUNDLES_PATH . self::$oRouterInstance->getBundle() . '/Config/bundle.json');
            if (! empty($sBundleConfig)) {
                $this->aConfig = array_merge($this->aConfig, new Json($sBundleConfig));
            }
        }

    }

    /**
     * Accessors
     */
    public function getConfig()
    {
        return $this->aConfig;
    }

}

class ConfigException extends \Exception {}