<?php
namespace Library\Core;

/**
 * Test component
 *
 * @author Nicolas Bonnici <nicolasbonnici@gmail.com>
 */
class Test extends \PHPUnit_Framework_TestCase
{
    public function __construct($sDomainName = 'domain.com', $sEnv = 'dev')
    {
        $_SERVER['SERVER_NAME'] = $sDomainName;

        // Overwrite global project constants
        if (defined('FRAMEWORK_STARTED') === false) {
        	define('FRAMEWORK_STARTED', microtime(true));
        }
        if (defined('CONF_PATH') === false) {
        	define('CONF_PATH', __DIR__ . '/../../app/config/');
        }
        if (defined('ROOT_PATH') === false) {
        	define('ROOT_PATH', __DIR__ . '/../../');
        }
        if (defined('CACHE_HOST') === false) {
        	define('CACHE_HOST', '127.0.0.1');
        }
        if (defined('CACHE_PORT') === false) {
        	define('CACHE_PORT', '11211');
        }

        // Register autoload and load config for given staging environment
        include_once __DIR__ . '/Bootstrap.php';
        \Library\Core\Bootstrap::initAutoloader();
        \Library\Core\Bootstrap::initConfig();
    }

    /**
     * Display class name before run all testcase methods
     */
    public static function setUpBeforeClass()
    {
        echo "\n" . get_called_class() . "\n";
    }
    
    /**
     * Switch method accessibility
     * 
     * @param string $sClassName
     * @param string $sMethodName
     * @return ReflectionMethod
     */
    protected function setMethodAccesible($sClassName, $sMethodName)
    {
        $oReflectionClass = new \ReflectionClass($sClassName);
        $oMethod = $oReflectionClass->getMethod($sMethodName);
        $oMethod->setAccessible(true);
        return $oMethod;
    }
    
}

class TestException extends \Exception
{
}
