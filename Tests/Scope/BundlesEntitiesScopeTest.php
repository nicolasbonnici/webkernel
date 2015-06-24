<?php
namespace Library\Core\Tests\Scope;

use Library\Core\App\Bundles;
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

    public function testGetScope()
    {
        $aScope = self::$oScopeBundlesEntitiesInstance->getScope();
        $this->assertTrue(is_array($aScope));
        foreach($aScope as $sBundle => $aEntities) {
            $this->assertTrue(is_string($sBundle));
            $this->assertTrue(is_array($aEntities) || is_null($aEntities));
            if (is_array($aEntities) === true) {
                foreach ($aEntities as $oEntity) {
                    $this->assertInstanceOf('Library\Core\Orm\Entity', $oEntity);
                }
            }
        }
    }

}