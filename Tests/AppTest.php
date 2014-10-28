<?php
namespace Library\Core\Tests;

use \Library\Core\Test;
use \Library\Core\App;

/**
 * App component unit tests
 * @author Nicolas Bonnici <nicolasbonnici@gmail.com>
 */
class AppTest extends Test
{
    protected static $oUserInstance;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::$oUserInstance = new \bundles\user\Entities\User(1);
    }

    public function setUp()
    {
    }

    public function tearDown()
    {
    }

    public function testConstructor()
    {
        $this->assertTrue(self::$oUserInstance->isLoaded());
        $oApp = App::getInstance();
        $this->assertTrue($oApp instanceof App);
    }
    
    public function testBuildEntities()
    {
        $aEntities = App::buildEntities();
        $this->assertTrue(is_array($aEntities));
    }

    public function testRegisterLoadedClassClass()
    {
        // Try register a class
        // Assert that the class is registered
    }
}