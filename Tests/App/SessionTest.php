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
        $_SESSION['testKey'] = 'testValue';
        self::$oSessionInstance = Session::getInstance();
        $this->assertTrue(self::$oSessionInstance->setSession($_SESSION) instanceof Session);
        $this->assertEquals($_SESSION, self::$oSessionInstance->getSession());
    }

}