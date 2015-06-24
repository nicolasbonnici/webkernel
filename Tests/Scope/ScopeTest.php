<?php
namespace Library\Core\Tests\Scope;

use \Library\Core\Test as Test;


/**
 * Scope abstract  component unit tests
 * 
 * @author Nicolas Bonnici <nicolasbonnici@gmail.com>
 */
class ScopeTest extends DummyScope
{
    protected static $oScopeEntitiesInstance;
    
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
        self::$oScopeInstance = new Entities();
        $this->assertTrue(self::$oScopeInstance instanceof Entities);
    }

    public function testAdd()
    {
        $this->assertTrue(self::$oScopeInstance->add('test1') instanceof Entities);
        $this->assertTrue(self::$oScopeInstance->add('test2') instanceof Entities);
        $this->assertTrue(self::$oScopeInstance->add('test3') instanceof Entities);
    }

    public function testGetScope()
    {
        $this->assertTrue(is_array(self::$oScopeInstance->getScope()));
        $this->assertTrue(in_array('test1', self::$oScopeInstance->getScope()));
        $this->assertTrue(in_array('test2', self::$oScopeInstance->getScope()));
        $this->assertTrue(in_array('test3', self::$oScopeInstance->getScope()));

    }

    public function testAddItems()
    {
        $aTest = array(
            'test4',
            'test5',
            'test6'
        );
        $this->assertTrue(self::$oScopeInstance->addItems($aTest) instanceof Entities);

        $this->assertTrue(in_array('test4', self::$oScopeInstance->getScope()));
        $this->assertTrue(in_array('test5', self::$oScopeInstance->getScope()));
        $this->assertTrue(in_array('test6', self::$oScopeInstance->getScope()));
        $this->assertEquals(self::$oScopeInstance->getScope(), array(
            'test1',
            'test2',
            'test3',
            'test4',
            'test5',
            'test6'
        ));
    }

    public function testGetWithNoParameter()
    {
        $this->assertEquals(self::$oScopeInstance->get(), array(
            'test1',
            'test2',
            'test3',
            'test4',
            'test5',
            'test6'
        ));
    }

    public function testAddWithItemWithKey()
    {
        $this->assertTrue(self::$oScopeInstance->addItem('testValue', 'testKey') instanceof Entities);
        $this->assertTrue(array_key_exists('testValue', self::$oScopeInstance->getScope()));
    }

    public function testAddWithoutItemWithKey()
    {
        $this->assertTrue(self::$oScopeInstance->addItem('XXXXXX') instanceof Entities);
        $this->assertTrue(self::$oScopeInstance->addItem('YYYYYY') instanceof Entities);
        $this->assertTrue(self::$oScopeInstance->addItem('ZZZZZZ') instanceof Entities);
        $this->assertTrue(in_array('XXXXXX', array_values(self::$oScopeInstance->getScope())));
        $this->assertTrue(in_array('YYYYYY', array_values(self::$oScopeInstance->getScope())));
        $this->assertTrue(in_array('ZZZZZZ', array_values(self::$oScopeInstance->getScope())));
        $this->assertTrue(in_array(0, array_keys(self::$oScopeInstance->getScope())));
        $this->assertTrue(in_array(1, array_keys(self::$oScopeInstance->getScope())));
        $this->assertTrue(in_array(2, array_keys(self::$oScopeInstance->getScope())));
    }

    public function testGetWithParameter()
    {
        $this->assertEquals(self::$oScopeInstance->addItem('testKey'), 'testValue');
    }

    public function testDelete()
    {
        $this->assertTrue(self::$oScopeInstance->delete('testKey'));
        $this->assertTrue(self::$oScopeInstance->get('testKey'));

    }

}