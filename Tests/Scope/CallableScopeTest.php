<?php
namespace Library\Core\Tests\Scope;

use Library\Core\Bootstrap;
use \Library\Core\Test as Test;

use Library\Core\Scope\CallableScope;

use bundles\blog\Entities\Post;
use bundles\lifestream\Entities\FeedItem;
use bundles\user\Entities\User;


/**
 * Scope\Enitities component unit tests
 * 
 * @author Nicolas Bonnici <nicolasbonnici@gmail.com>
 */
class CallableScopeTest extends Test
{
    protected static $oCallableScopeInstance;
    
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
        self::$oCallableScopeInstance = new CallableScope();
        $this->assertTrue(self::$oCallableScopeInstance instanceof CallableScope);
    }

    public function testAddItems()
    {
        self::$oCallableScopeInstance = new CallableScope();
        $callable1 = function() {
            echo 'Hello function!';
        };

        $this->assertTrue(self::$oCallableScopeInstance->add($callable1) instanceof CallableScope);
    }

    public function testGetScope()
    {
        self::$oCallableScopeInstance = new CallableScope();
        $aScope = self::$oCallableScopeInstance->getScope();
        $this->assertTrue(is_array($aScope));
        foreach ($aScope as $callable) {
            $this->assertTrue(is_callable($callable));
        }

    }

    /**
     * Dummy function for test purpose
     * @return bool
     */
    public static function dummyFunction()
    {
        return true;
    }

}