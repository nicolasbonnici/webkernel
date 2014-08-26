<?php
namespace Library\Core;

/**
 *
 *
 * @author niko <nicolasbonnici@gmail.com>
 */
class Config
{
    /**
     * Configuration entity instance
     * @var \app\Entities\Config
     */
    private $oConfig;

    /**
     * @todo
     * @var \app\Collection\ConfigCollection
     */
    private $oConfigCollection;

    /**
     *Instance constructor
     */
    public function __construct($sConfKeyName = null)
    {
        if (! is_string($sConfKeyName)) {
            throw new ConfigException('The setting parameter key name must be casted as a string, ' . var_dump($sConfKeyName) . ' given.');
        }
    }

}

class ConfigException extends \Exception
{

}
