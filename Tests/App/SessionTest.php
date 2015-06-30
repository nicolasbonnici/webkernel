<?php
namespace Core\Tests\App;

use Library\Core\App\Session;
use \Library\Core\Test as Test;

/**
 * Session component unit tests
 *
 * @author Nicolas Bonnici <nicolasbonnici@gmail.com>
 */
class SessionTest extends Test
{
    protected static $oSessionInstance;

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

    public function testConstructor()
    {
        self::$oSessionInstance = Session::getInstance();
        $this->assertEquals($_SESSION, self::$oSessionInstance->get());
    }

    public function testAddAndGet()
    {
        $this->assertTrue(self::$oSessionInstance->add('test1', 'value1'));
        $this->assertEquals($_SESSION['test1'], self::$oSessionInstance->get('test1'));
    }

    public function testSet()
    {
        $this->assertTrue(self::$oSessionInstance->set($_SESSION));
    }

    public function testDelete()
    {
        $this->assertTrue(self::$oSessionInstance->delete('test1'));
        $this->assertEquals($_SESSION, self::$oSessionInstance->get());
    }

}