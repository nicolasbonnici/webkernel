<?php
namespace Library\Core\Tests\Pattern;

use \Library\Core\Test as Test;
use Library\Core\Tests\Dummy\Patterns\DummySingleton;


/**
 * Singleton component unit tests
 * 
 * @author Nicolas Bonnici <nicolasbonnici@gmail.com>
 */
class SingletonTest extends Test
{
    protected static $oSingletonInstance;
    
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
    }

    protected function setUp()
    {
        self::$oSingletonInstance = DummySingleton::getInstance();
    }

    public function testTryToInstanceTwice()
    {
        $oDummySingleton = DummySingleton::getInstance();
    }

}