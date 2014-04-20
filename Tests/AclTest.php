<?php
namespace Library\Core\Tests;


// Tested class
include_once __DIR__ . '/../Acl.php';

/**
 * Acl unit test
 * @author Nicolas Bonnici <nicolasbonnici@gmail.com>
 */

/**
 * Abstract Acl component text enteded class
 * @author niko
 *
 */
class AclEntentedClassTest extends \Library\Core\Acl
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
	public function getCRUD($sRessource)
	{
	    return $this->oGroups;
	}
}

class AclTest extends \Library\Core\Test
{
    protected static $oAclInstance;
    protected static $oUserInstance;

    public static function setUpBeforeClass()
    {
        self::$oUserInstance = new \app\Entities\User(1);
        self::$oAclInstance = new AclEntentedClassTest(self::$oUserInstance);
    }


    public function setUp()
    {
    }

    public function tearDown()
    {
        unset($_SERVER['SERVER_NAME']);
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

    public function testInvalidPermission($sInvalidRessourceName = 'BULLSHITENTITY')
    {
        $oTestedMethod = $this->setMethodAccesible('\Library\Core\Acl', 'hasCreateAccess');
        $this->assertFalse($oTestedMethod->invokeArgs(self::$oAclInstance, array($sInvalidRessourceName)));
    }

}

