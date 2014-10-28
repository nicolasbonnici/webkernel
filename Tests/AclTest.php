<?php
namespace Library\Core\Tests;

use \Library\Core\Test as Test;
use \Library\Core\Acl as Acl;

/**
 * Acl component unit test
 * @author Nicolas Bonnici <nicolasbonnici@gmail.com>
 */
class AclTest extends Test
{
    protected static $oAclInstance;
    protected static $oUserInstance;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::$oUserInstance = new \bundles\user\Entities\User(1);
        self::$oAclInstance = new AclExtentedClassTest(self::$oUserInstance);
    }

    public function setUp()
    {
    }

    public function tearDown()
    {
    }

    public function testConstructor()
    {
        $this->assertTrue(self::$oUserInstance->isLoaded());
        $this->assertTrue(self::$oAclInstance instanceof \Library\Core\Acl);
    }

    public function testPermissionLoaded()
    {
        $oPermissions = self::$oAclInstance->getPermissions();
        $this->assertTrue($oPermissions->count() > 0);
    }

    public function testInvalidEntityPermission($sInvalidRessourceName = 'BULLSHITENTITY')
    {
        $oTestedMethod = $this->setMethodAccesible('\Library\Core\Acl', 'hasCreateAccess');
        $this->assertFalse($oTestedMethod->invokeArgs(self::$oAclInstance, array($sInvalidRessourceName)));
    }

    public function testValidEntityPermission($sValidRessourceName = 'todo')
    {
        $oTestedMethod = $this->setMethodAccesible('\Library\Core\Acl', 'hasCreateAccess');
        $this->assertTrue($oTestedMethod->invokeArgs(self::$oAclInstance, array($sValidRessourceName)));
    }

}

/**
 * Abstract Acl component test extended mock class
 *
 */
class AclExtentedClassTest extends Acl
{
    /* Accessors for testing purposes */
    public function getPermissions()
    {
        return $this->oPermissions;
    }
    public function getGroups()
    {
        return $this->oGroups;
    }
    public function getRessources()
    {
        return $this->oRessources;
    }
}