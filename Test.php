<?php
namespace Library\Core;

/**
 * Test component
 *
 * @author Nicolas Bonnici <nicolasbonnici@gmail.com>
 */
class Test extends \PHPUnit_Framework_TestCase
{
    public function __construct()
    {
        if (is_array($_SESSION) === false) {
            $_SESSION = array();
        }
        $_SERVER['SERVER_NAME'] = 'nbonnici.dev';

        // Register autoload and load config for given staging environment
        include_once __DIR__ . '/Bootstrap.php';
        \Library\Core\Bootstrap::getInstance();
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
