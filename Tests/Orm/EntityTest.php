<?php
namespace Library\Core\Tests\Select;

use \Library\Core\Test as Test;
use Library\Core\Orm\Entity;
use Library\Core\Tests\Dummy\Entities\DummyEntity;


/**
 * ORM Entity component unit tests
 * 
 * @author Nicolas Bonnici <nicolasbonnici@gmail.com>
 */
class EntityTest extends Test
{
    protected static $oEntityInstance;

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
        self::$oEntityInstance = new DummyEntity();
        $this->assertTrue(self::$oEntityInstance instanceof Entity);
    }

    // Test configuration
    public function testIsDeletable()
    {
        $this->assertEquals(true, self::$oEntityInstance->isDeletable());
    }
    public function testIsCacheable()
    {
        $this->assertEquals(true, self::$oEntityInstance->isCacheable());
    }
    public function testIsSearchable()
    {
        $this->assertEquals(true, self::$oEntityInstance->isSearchable());
    }
    public function testIsHistorized()
    {
        $this->assertEquals(false, self::$oEntityInstance->isHistorized());
    }

    // @todo
/**    public function testConstructorWithInteger()
    {
        $this->assertEquals();
    }

    public function testConstructorWithString()
    {
        $this->assertEquals();
    }

    public function testIsLoaded()
    {
        $this->assertEquals(self::$oEntityInstance->isLoaded(), false);
    }
 **/
}