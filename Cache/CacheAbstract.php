<?php
namespace Library\Core\Cache;
use Library\Core\Pattern\Singleton;

/**
 * Cache abstract layer
 *
 * @author niko <nicolasbonnici@gmail.com>
 */
abstract class CacheAbstract extends Singleton implements CacheInterface
{

    /**
     * Connection to cache engine flag
     * @var boolean
     */
    protected static $bIsConnected = false;

    protected function __construct()
    {
        $this->connect();
    }

    /**
     * Generate cache key depending on given parameters
     * Variable types "ressource" and "NULL" and "Unknown type" are not handled
     * If key string is more than 250 characters long, MD5 hash is retrieved
     *
     * @return string
     */
    public static function getKey()
    {
        $sKey = '';
        foreach (func_get_args() as $mArgument) {
            switch (gettype($mArgument)) {
                case 'integer':
                case 'double':
                case 'string':
                    $sKey .= $mArgument . '-';
                    break;
                case 'boolean':
                    $sKey .= (string) $mArgument . '-';
                    break;
                case 'array':
                    $sKey .= call_user_func_array(array(
                        'self',
                        'getKey'
                    ), $mArgument) . '-';
                    break;
                case 'object':
                    $sKey .= serialize($mArgument) . '-';
                    break;
            }
        }
        
        if (strlen($sKey) > 250) {
            $sKey = md5($sKey);
        }
        
        return $sKey;
    }

    /**
     * @return bool
     */
    public static function isConnected()
    {
        return self::$bIsConnected;
    }
}
