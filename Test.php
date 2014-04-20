<?php
namespace Library\Core;

/**
 * Test component
 *
 * @author Nicolas Bonnici <nicolasbonnici@gmail.com>
 */
class Test extends \PHPUnit_Framework_TestCase
{
    public function __construct($sDomainName = 'domain.com', $sEnv = 'prod')
    {
        $_SERVER['SERVER_NAME'] = $sDomainName;
        define('ENV', $sEnv);

        // Overwrite global project constants
        define('ROOT_PATH', __DIR__ . '/../../');
        define('CONF_PATH', __DIR__ . '/../../app/config/');
        define('FRAMEWORK_STARTED', microtime(true));
        define('ROOT_PATH', __DIR__ . '/../../');
        define('CACHE_HOST', '127.0.0.1');
        define('CACHE_PORT', '11211');

        // Register autoload and load config for given staging environment
        include_once __DIR__ . '/App.php';
        \Library\Core\App::initAutoloader();
        \Library\Core\App::initConfig();
    }

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
