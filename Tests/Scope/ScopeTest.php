<?php
namespace Library\Core\Tests\Scope;

use Library\Core\Scope\EntitiesScope;
use \Library\Core\Test as Test;
use Library\Core\Tests\Dummy\Scope\DummyScope;


/**
 * Scope abstract  component unit tests
 * 
 * @author Nicolas Bonnici <nicolasbonnici@gmail.com>
 */
class ScopeTest extends Test
{
    protected static $oScopeInstance;
    
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
        self::$oScopeInstance = new EntitiesScope();
        $this->assertTrue(self::$oScopeInstance instanceof EntitiesScope);
    }

    public function testAdd()
    {
        $this->assertTrue(self::$oScopeInstance->add('test1') instanceof EntitiesScope);
        $this->assertTrue(self::$oScopeInstance->add('test2') instanceof EntitiesScope);
        $this->assertTrue(self::$oScopeInstance->add('test3') instanceof EntitiesScope);
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
        $this->assertTrue(self::$oScopeInstance->addItems($aTest) instanceof EntitiesScope);

        $this->assertTrue(in_array('test4', self::$oScopeInstance->getScope()));
        $this->assertTrue(in_array('test5', self::$oScopeInstance->getScope()));
        $this->assertTrue(in_array('test6', self::$oScopeInstance->getScope()));
        $this->assertEquals(self::$oScopeInstance->getScope(), array(
            'test4',
            'test5',
            'test6'
        ));
    }

    public function testGetWithNoParameter()
    {
        $this->assertEquals(self::$oScopeInstance->get(), array(
            'test4',
            'test5',
            'test6'
        ));
    }

    public function testAddWithItemWithKey()
    {
        $this->assertTrue(self::$oScopeInstance->add('testValue', 'testKey') instanceof EntitiesScope);
        $this->assertTrue(array_key_exists('testKey', self::$oScopeInstance->getScope()));
    }

    public function testAddWithoutItemWithKey()
    {
        $this->assertTrue(self::$oScopeInstance->add('XXXXXX') instanceof EntitiesScope);
        $this->assertTrue(self::$oScopeInstance->add('YYYYYY') instanceof EntitiesScope);
        $this->assertTrue(self::$oScopeInstance->add('ZZZZZZ') instanceof EntitiesScope);
        $this->assertTrue(in_array('XXXXXX', array_values(self::$oScopeInstance->getScope())));
        $this->assertTrue(in_array('YYYYYY', array_values(self::$oScopeInstance->getScope())));
        $this->assertTrue(in_array('ZZZZZZ', array_values(self::$oScopeInstance->getScope())));
        $this->assertTrue(in_array(0, array_keys(self::$oScopeInstance->getScope())));
        $this->assertTrue(in_array(1, array_keys(self::$oScopeInstance->getScope())));
        $this->assertTrue(in_array(2, array_keys(self::$oScopeInstance->getScope())));
    }

    public function testGetWithParameter()
    {
        $this->assertEquals(self::$oScopeInstance->get('testKey'), 'testValue');
    }

    public function testDelete()
    {
        $this->assertTrue(self::$oScopeInstance->delete('testKey'));
        // Assert hat getter return null on Scope value
        $this->assertTrue(self::$oScopeInstance->get('testKey') === null);

    }


    public function testAddConstraints()
    {
        $this->assertInstanceOf(
            'Library\Core\Scope\EntitiesScope',
            self::$oScopeInstance->setConstraints(
                array(
                    'constraint1',
                    'constraint2',
                    'constraint3',
                    'constraint4'
                )
            )
        );

    }

    public function testGetConstraints()
    {
        $this->assertEquals(
            array(
                'constraint1',
                'constraint2',
                'constraint3',
                'constraint4'
            ),
            self::$oScopeInstance->getConstraints()
        );
    }

    public function testFilter()
    {
        $oDummyScope = new DummyScope();
        $oDummyScope->addItems(array('foo1' => 'value1', 'foo2' => 'value2', 'foo3' => 'value3'));
        $oDummyScope->setConstraints(array('foo2'));
        $this->assertArrayHasKey(
            'foo1',
            $oDummyScope->getScope()
        );
        $this->assertArrayHasKey(
            'foo3',
            $oDummyScope->getScope()
        );
        $this->assertArrayNotHasKey(
            'foo2',
            $oDummyScope->getScope()
        );
    }

}