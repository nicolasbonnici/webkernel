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

        // Register autoload and load config for given staging environment
        include_once __DIR__ . '/App.php';
        \Library\Core\App::initAutoloader();
        \Library\Core\App::initConfig();
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
