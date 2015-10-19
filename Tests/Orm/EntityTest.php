<?php
namespace Library\Core\Tests\Orm;

use Library\Core\Orm\Entity;
use Library\Core\Test;
use Library\Core\Tests\Dummy\Entities\Dummy;

/**
 * ORM Entity component unit tests
 * 
 * @author Nicolas Bonnici <nicolasbonnici@gmail.com>
 */
class EntityTest extends Test
{
    protected static $oEntityInstance;

    protected static $aTestData = array(
        'test_string' => 'Some test string',
        'test_int'    => 199,
        'test_float'  => 666.99235,
        'test_null'   => null,
        'lastupdate'   => 1434675542,
        'created'   => 1434675542
    );

    /**
     * Member to store test entity primary key value
     * @var integer
     */
    protected static $iCreatedDummyId;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
    }

    protected function setUp()
    {
    }

    protected function tearDown()
    {
    }

    public function testConstructor()
    {
        self::$oEntityInstance = new Dummy();
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

    public function testToString()
    {
        $this->assertEquals(self::$oEntityInstance->getChildClass(), self::$oEntityInstance->__toString());
    }

    public function testAdd()
    {
        self::$oEntityInstance->test_string  = self::$aTestData['test_string'];
        self::$oEntityInstance->test_int     = self::$aTestData['test_int'];
        self::$oEntityInstance->test_float   = self::$aTestData['test_float'];
        self::$oEntityInstance->test_null    = self::$aTestData['test_null'];
        self::$oEntityInstance->lastupdate   = self::$aTestData['lastupdate'];
        self::$oEntityInstance->created      = self::$aTestData['created'];
        $this->assertTrue(self::$oEntityInstance->add());
        self::$iCreatedDummyId = self::$oEntityInstance->getId();
        $this->assertTrue(self::$iCreatedDummyId > 0);
    }

    public function testGetId()
    {
        $this->assertTrue(self::$oEntityInstance->getId() > 0);
    }

    public function testConstructorWithString()
    {
        self::$oEntityInstance = new Dummy((string) self::$iCreatedDummyId);
        $this->assertTrue(self::$oEntityInstance->isLoaded() === true);
        $this->assertEquals(self::$iCreatedDummyId, self::$oEntityInstance->getId());
    }

    public function testConstructorWithInteger()
    {
        self::$oEntityInstance = new Dummy((int) self::$iCreatedDummyId);
        $this->assertTrue(self::$oEntityInstance->isLoaded() === true);
        $this->assertEquals(self::$iCreatedDummyId, self::$oEntityInstance->getId());
    }

    public function testConstructorWithArray()
    {
        self::$oEntityInstance = new Dummy(array(Dummy::PRIMARY_KEY => self::$iCreatedDummyId));
        $this->assertTrue(self::$oEntityInstance->isLoaded() === true);
        $this->assertEquals(self::$iCreatedDummyId, self::$oEntityInstance->getId());
    }

    public function testUpdate()
    {
        self::$oEntityInstance = new Dummy(self::$iCreatedDummyId);
        self::$oEntityInstance->test_string  = 'Other string value';
        $this->assertTrue(self::$oEntityInstance->update());
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