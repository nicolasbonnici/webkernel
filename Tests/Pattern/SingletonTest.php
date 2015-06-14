<?php
namespace Core\Tests\Query;

use \Core\Test as Test;


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

    public function setUp()
    {
    }

    public function tearDown()
    {
    }

    public function testGetInstance()
    {
        self::$oSingletonInstance = new Singleton();
        $this->assertTrue(self::$oSingletonInstance instanceof Singleton);

    }

}