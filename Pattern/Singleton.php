<?php
namespace Library\Core\Pattern;

/**
 * Singleton design pattern implementation class
 */
abstract class Singleton
{

    /**
     * List of available instances
     *
     * @var array
     */
    protected static $aInstances = array();

    /**
     * Constructor
     */
    protected function __construct()
    {

    }

    /**
     * Retrieve single instance of class
     *
     * @return self
     */
    public static function getInstance()
    {
        $sClass = get_called_class();
        
        if (! self::isInstanceRegistered()) {
            self::$aInstances[$sClass] = new $sClass();
        }
        
        return self::$aInstances[$sClass];
    }

    /**
     * Check whether class instance has already been instanciated or not
     *
     * @return boolean TRUE if instance of class exists, otherwise FALSE
     */
    public static function isInstanceRegistered()
    {
        return isset(self::$aInstances[get_called_class()]);
    }

    /**
     * to prevent loop hole in PHP so that the class cannot be cloned
     */
    final private function __clone()
    {}
}
