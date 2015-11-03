<?php
namespace Library\Core\Tests;


use app\Entities\User;
use bundles\auth\Models\AuthModel;
use Library\Core\Acl;
use Library\Core\Database\Pdo;
use Library\Core\Database\Query\Insert;
use Library\Core\Test;
use Library\Core\Tests\Dummy\Entities\Dummy;

class AclTest extends Test {

    const TEST_MAIL = 'user@domain.tld';
    const TEST_PASS = 'testpassword';

    /**
     * @var Acl
     */
    private $oAclInstance;

    /**
     * @var User
     */
    private $oUser;

    protected function setUp()
    {
        $this->generateUser();
        $this->oAclInstance = new Acl($this->oUser);
    }

    public function testConstructor()
    {
        $this->assertInstanceOf(
            'Library\Core\Acl',
            $this->oAclInstance,
            'Unable to instantiate Acl in Acl Test'
        );
    }

    /**
     * Assert that a user registered users who belong to no group always has false on Acl
     */

    public function testAclWithUserWithoutGroupIsNotLoaded()
    {
        $this->assertFalse(
            $this->oAclInstance->isLoaded(),
            'Acl loaded for a user with no group'
        );
    }

    public function testAclWithRootUser()
    {

        $oInsert = new Insert();
        $oInsert->setFrom('userGroup')
            ->addParameter('user_iduser', $this->oUser->getId())
            ->addParameter('group_idgroup', 9);

        $this->assertTrue(
            $oStatement  = Pdo::dbQuery(
                $oInsert->build(),
                array(
                    'user_iduser' => $this->oUser->getId(),
                    'group_idgroup' => 9

                )
            ) !== false,
            'Unable to register user to Root group on Acl unit test'
        );

        # Refresh Acl at this level
        $this->oAclInstance = new Acl($this->oUser);

        $this->assertTrue(
            $this->oAclInstance->hasReadAccess(get_class(new Dummy())),
            'Unable for a Root User to read on Dummy Entity'
        );
        $this->assertTrue(
            $this->oAclInstance->hasUpdateAccess(get_class(new Dummy())),
            'Unable for a Root User to update on Dummy Entity'
        );
        $this->assertTrue(
            $this->oAclInstance->hasDeleteAccess(get_class(new Dummy())),
            'Unable for a Root User to delete on Dummy Entity'
        );
        $this->assertTrue(
            $this->oAclInstance->hasListAccess(get_class(new Dummy())),
            'Unable for a Root User to list on Dummy Entity'
        );
    }

    protected function generateUser()
    {
        $oAuthModelInstance = new AuthModel();
        $aValidData = array(
            'firstname' => 'User',
            'lastname'  => 'Detest',
            'email'     => self::TEST_MAIL,
            'password1' => self::TEST_PASS,
            'password2' => self::TEST_PASS
        );
        $this->assertTrue($oAuthModelInstance->register($aValidData));
        $this->assertFalse($oAuthModelInstance->activate(self::TEST_MAIL, 'sd7fdf7sd3543sd5g74357d35g5ds7f2454'));
        $oUser = new User();
        $oUser->loadByParameters(array(
            'mail' => self::TEST_MAIL
        ));
        $this->assertTrue($oAuthModelInstance->activate(self::TEST_MAIL, $oUser->token), 'Unable to activate user');

        $this->oUser =  $oUser;
    }


    public static function tearDownAfterClass()
    {
        self::cleanCreatedUsers(self::TEST_MAIL);
        parent::tearDownAfterClass();
    }
}