<?php
namespace Library\Core\Tests\Scope;

use \Library\Core\Test as Test;

use Library\Core\Scope\EntitiesScope;

use bundles\blog\Entities\Post;
use app\Entities\FeedItem;
use app\Entities\User;


/**
 * Scope\Enitities component unit tests
 * 
 * @author Nicolas Bonnici <nicolasbonnici@gmail.com>
 */
class EntitiesScopeTest extends Test
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
        self::$oScopeEntitiesInstance = new EntitiesScope();

        $oPost = new Post();
        $oFeedItem = new FeedItem();
        $oUser = new User();

        /**
         * @todo also test constraints parameter
         */

        $this->assertTrue(self::$oScopeEntitiesInstance->add($oPost, $oPost->getEntityName()) instanceof EntitiesScope);
        $this->assertTrue(self::$oScopeEntitiesInstance->add($oFeedItem, $oFeedItem->getEntityName()) instanceof EntitiesScope);
        $this->assertTrue(self::$oScopeEntitiesInstance->add($oUser, $oUser->getEntityName()) instanceof EntitiesScope);
    }

    public function testGetScope()
    {
        $this->testAdd();

        $aScope = self::$oScopeEntitiesInstance->getScope();
        $this->assertTrue(is_array($aScope));
        $this->assertArrayHasKey('Post', $aScope);
        $this->assertArrayHasKey('FeedItem', $aScope);
        $this->assertArrayHasKey('User', $aScope);
    }

}