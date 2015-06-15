<?php
namespace Library\Core\Tests\Scope;

use \Library\Core\Test as Test;

use Library\Core\Scope\EntitiesScope;

use bundles\blog\Entities\Post;
use bundles\lifestream\Entities\FeedItem;
use bundles\user\Entities\User;


/**
 * Scope\Enitities component unit tests
 * 
 * @author Nicolas Bonnici <nicolasbonnici@gmail.com>
 */
class EntityScopeTest extends Test
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
        self::$oScopeEntitiesInstance = new EntitiesScope();
        $this->assertTrue(self::$oScopeEntitiesInstance instanceof EntitiesScope);
    }

    public function testAdd()
    {
        $oPost = new Post();
        $oFeedItem = new FeedItem();
        $oUser = new User();

        /**
         * @todo also test constraints parameter
         */

        $this->assertTrue(self::$oScopeEntitiesInstance->add($oPost) instanceof EntitiesScope);
        $this->assertTrue(self::$oScopeEntitiesInstance->add($oFeedItem) instanceof EntitiesScope);
        $this->assertTrue(self::$oScopeEntitiesInstance->add($oUser) instanceof EntitiesScope);
    }

    public function testGetScope()
    {
        $this->assertTrue(is_array(self::$oScopeEntitiesInstance->getScope()));
        $this->assertTrue(array_key_exists('Post', self::$oScopeEntitiesInstance->getScope()));
        $this->assertTrue(array_key_exists('FeedItem', self::$oScopeEntitiesInstance->getScope()));
        $this->assertTrue(array_key_exists('User', self::$oScopeEntitiesInstance->getScope()));
    }

}