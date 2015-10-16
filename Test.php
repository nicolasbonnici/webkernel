<?php
namespace Library\Core;

/**
 * Test component
 *
 * @author Nicolas Bonnici <nicolasbonnici@gmail.com>
 */
class Test extends \PHPUnit_Framework_TestCase
{

    const ACCESSORS_GETTER = 'getter';
    const ACCESSORS_SETTER = 'setter';

    public function __construct()
    {
        $_SERVER['SERVER_NAME'] = 'nbonnici.dev';
        $_SERVER['REQUEST_URI'] = '/';
        if (isset($_SESSION) === false || is_array($_SESSION) === false) {
            global $_SESSION;
            $_SESSION = array();
        }
        if (defined('TEST') === false) {
            define('TEST', true);
        }


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
     * @return \ReflectionMethod
     */
    protected function setMethodAccessible($sClassName, $sMethodName)
    {
        $oReflectionClass = new \ReflectionClass($sClassName);
        $oMethod = $oReflectionClass->getMethod($sMethodName);
        $oMethod->setAccessible(true);
        return $oMethod;
    }

    /**
     * Retrieve class accessors
     *
     * @param mixed string|object $mClass
     * @return array
     */
    protected function getAccessors($mClass)
    {
        $oReflectedEmailNotification = new \ReflectionClass($mClass);

        $aAccessors = array();
        $aAccessors[self::ACCESSORS_GETTER] = array();
        $aAccessors[self::ACCESSORS_SETTER] = array();
        $aMethods = $oReflectedEmailNotification->getMethods();
        foreach ($aMethods as $iIndex => $oMethod) {
            if (substr($oMethod->name, 0, strlen('get')) === 'get') {
                $aAccessors[self::ACCESSORS_GETTER][] = $oMethod->name;
            }

            if (substr($oMethod->name, 0, strlen('set')) === 'set') {
                $aAccessors[self::ACCESSORS_SETTER][] = $oMethod->name;
            }
        }
        return $aAccessors;
    }
    
}

class TestException extends \Exception
{
}
