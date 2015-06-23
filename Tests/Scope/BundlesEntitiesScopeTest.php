<?php
namespace Library\Core\Tests\Scope;

use \Library\Core\Test as Test;

use Library\Core\Scope\BundlesEntitiesScope;

use bundles\blog\Entities\Post;
use bundles\lifestream\Entities\FeedItem;
use bundles\user\Entities\User;


/**
 * Scope\Enitities component unit tests
 * 
 * @author Nicolas Bonnici <nicolasbonnici@gmail.com>
 */
class BundlesEntitiesScopeTest extends Test
{
    protected static $oScopeBundlesEntitiesInstance;
    
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
        self::$oScopeBundlesEntitiesInstance = new BundlesEntitiesScope();
        $this->assertTrue(self::$oScopeBundlesEntitiesInstance instanceof BundlesEntitiesScope);
    }

    public function testAdd()
    {
        self::$oScopeBundlesEntitiesInstance = new BundlesEntitiesScope();

        $oPost = new Post();
        $oFeedItem = new FeedItem();
        $oUser = new User();

        /**
         * @todo also test constraints parameter
         */

        $this->assertTrue(self::$oScopeBundlesEntitiesInstance->add($oPost, $oPost->getEntityName()) instanceof BundlesEntitiesScope);
        $this->assertTrue(self::$oScopeBundlesEntitiesInstance->add($oFeedItem, $oFeedItem->getEntityName()) instanceof BundlesEntitiesScope);
        $this->assertTrue(self::$oScopeBundlesEntitiesInstance->add($oUser, $oUser->getEntityName()) instanceof BundlesEntitiesScope);
    }

    public function testGetScope()
    {
        $this->testAdd();

        $aScope = self::$oScopeBundlesEntitiesInstance->getScope();
        $this->assertTrue(is_array($aScope));
        $this->assertArrayHasKey('Post', $aScope);
        $this->assertArrayHasKey('FeedItem', $aScope);
        $this->assertArrayHasKey('User', $aScope);
    }

}