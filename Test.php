<?php
namespace Core;

use Core\App\Configuration;

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

        if (defined('ROOT_PATH') === false) {
            define('ROOT_PATH', substr(__DIR__, 0, strlen(__DIR__) - strlen('Library/Core')));
        }

        require_once ROOT_PATH . '/Library/Core/App/Bootstrap.php';
        App\Bootstrap::getInstance();

        die('Bootstrapey!');
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
