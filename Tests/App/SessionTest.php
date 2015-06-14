<?php
namespace Core\Tests\App;

use Core\App\Session;
use \Core\Test as Test;

session_start();

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
        self::$oSessionInstance = new Session();
        $this->assertTrue(self::$oSessionInstance->setSession($_SESSION) instanceof Session);
        $this->assertEquals($_SESSION, self::$oSessionInstance->getSession());
    }

}