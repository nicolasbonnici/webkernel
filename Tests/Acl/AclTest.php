<?php
namespace Library\Core\Tests\Acl;


use Library\Core\Tests\Test;
use Library\Core\Acl;
use Library\Core\Entity\Crud;
use Library\Core\Tests\Dummy\Entities\Dummy;

class AclTest extends Test {

    const TEST_MAIL = 'user@domain.tld';
    const TEST_PASS = 'testpassword';

    /**
     * @var Dummy
     */
    private $oDummyInstance;


    protected function setUp()
    {
        $this->oDummyInstance = $this->getDummy();
    }

    public function testAclWithRootUser()
    {

        foreach (Crud::$aActionScope as $sAction) {
            $this->assertTrue(
                $this->oDummyInstance->hasAccess($sAction),
                'Unauthorized action ' . $sAction . ' on Dummy by a Root User'
            );
        }

    }

    public function testAclWithNoGroupUser()
    {
        # Delete all User <=> Group mappings
        self::deleteUserGroups();
        self::loadUser();

        $this->oDummyInstance->setUser(self::$oUser);
        foreach (Crud::$aActionScope as $sAction) {
            $this->assertFalse(
                $this->oDummyInstance->hasAccess($sAction),
                'Authorized action ' . $sAction . ' for User with no group!'
            );
        }
    }

    protected function getDummy()
    {
        $oDummy = new Dummy(null, 'FR_fr');

        if (self::loadUser(true) === false) {
            die('Unable to load test User');
        }
        $oDummy->setUser(self::$oUser);
        return $oDummy;
    }


    public static function tearDownAfterClass()
    {
        //self::cleanCreatedUsers(self::TEST_MAIL);
        parent::tearDownAfterClass();
    }
}