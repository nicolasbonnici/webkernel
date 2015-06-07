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

}