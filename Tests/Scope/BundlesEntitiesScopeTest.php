<?php
namespace Library\Core\Tests\Scope;

use \Library\Core\Test as Test;

use Library\Core\Scope\BundlesEntitiesScope;



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
                    $this->assertInstanceOf('Library\Core\Entity\Entity', $oEntity);
                }
            }
        }
    }

    public function testGetFilter()
    {
        $this->assertEquals(null, self::$oScopeBundlesEntitiesInstance->getFilter());
    }

    public function testSetLabel()
    {
        $this->assertInstanceOf(
            'Library\Core\Scope\BundlesEntitiesScope',
            self::$oScopeBundlesEntitiesInstance->setFilter(BundlesEntitiesScope::FILTER_ENTITY_SEARCH));
        $this->assertEquals(
            BundlesEntitiesScope::FILTER_ENTITY_SEARCH,
            self::$oScopeBundlesEntitiesInstance->getFilter()
        );
    }

    public function testLabelWithNotAllowedFilter()
    {
        // setFilter method must return null with an invalid filter
        $this->assertEquals(null, self::$oScopeBundlesEntitiesInstance->setFilter('lbdfdjfdf4d5f4ds5f4d5'));

        // Assert that setting a not allowed filter doesn't alter the previously setted filter
        $this->assertEquals(
            BundlesEntitiesScope::FILTER_ENTITY_SEARCH,
            self::$oScopeBundlesEntitiesInstance->getFilter()
        );
    }

}