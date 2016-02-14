<?php
namespace Library\Core;
use app\Entities\Group;
use app\Entities\User;
use bundles\auth\Models\AuthModel;
use Library\Core\Database\Pdo;
use Library\Core\Database\Query\Delete;
use Library\Core\Database\Query\Insert;
use Library\Core\Database\Query\Operators;
use Library\Core\Database\Query\Select;
use Library\Core\Database\Query\Where;

/**
 * Test component
 *
 * @author Nicolas Bonnici <nicolasbonnici@gmail.com>
 */
class Test extends \PHPUnit_Framework_TestCase
{


    const ACCESSORS_GETTER = 'getter';
    const ACCESSORS_SETTER = 'setter';

    /**
     * Test user credentials
     */
    const TEST_MAIL = 'user@domain.tld';
    const TEST_PASS = 'testpassword';

    /**
     * @var User
     */
    public static $oUser;

    public function __construct()
    {
        $_SERVER['SERVER_NAME'] = 'nbonnici.dev';
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'fr,en_EN';
        $_SERVER['REQUEST_URI'] = '/';
        if (isset($_SESSION) === false || is_array($_SESSION) === false) {
            global $_SESSION;
            $_SESSION = array();
        }
        if (defined('TEST') === false) {
            define('TEST', true);
        }


        // Register autoload and load config for given staging environment
        include_once __DIR__ . '/Bootstrap.php';
        \Library\Core\Bootstrap::getInstance();
    }

    /**
     * Display class name before run all testcase methods
     */
    public static function setUpBeforeClass()
    {
        echo "\n" . get_called_class() . "\n";
    }
    
    /**
     * Switch method accessibility
     * 
     * @param string $sClassName
     * @param string $sMethodName
     * @return \ReflectionMethod
     */
    protected function setMethodAccessible($sClassName, $sMethodName)
    {
        $oReflectionClass = new \ReflectionClass($sClassName);
        $oMethod = $oReflectionClass->getMethod($sMethodName);
        $oMethod->setAccessible(true);
        return $oMethod;
    }

    /**
     * Retrieve class accessors
     *
     * @param mixed string|object $mClass
     * @return array
     */
    protected function getAccessors($mClass)
    {
        $oReflectedEmailNotification = new \ReflectionClass($mClass);

        $aAccessors = array();
        $aAccessors[self::ACCESSORS_GETTER] = array();
        $aAccessors[self::ACCESSORS_SETTER] = array();
        $aMethods = $oReflectedEmailNotification->getMethods();
        foreach ($aMethods as $iIndex => $oMethod) {
            if (substr($oMethod->name, 0, strlen('get')) === 'get') {
                $aAccessors[self::ACCESSORS_GETTER][] = $oMethod->name;
            }

            if (substr($oMethod->name, 0, strlen('set')) === 'set') {
                $aAccessors[self::ACCESSORS_SETTER][] = $oMethod->name;
            }
        }
        return $aAccessors;
    }

    protected static function loadUser($bAddToRootGroup = false)
    {
        try {
            self::$oUser = new User(null, 'FR_fr');
            self::$oUser->loadByParameters(array(
                'mail' => self::TEST_MAIL
            ));

            if (self::$oUser->isLoaded() === false) {
                $oAuthModelInstance = new AuthModel();
                $aValidData = array(
                    'firstname' => 'User',
                    'lastname'  => 'Detest',
                    'email'     => self::TEST_MAIL,
                    'password1' => self::TEST_PASS,
                    'password2' => self::TEST_PASS
                );
                if ($oAuthModelInstance->register($aValidData) === true) {

                    self::$oUser->loadByParameters(array(
                        'mail' => self::TEST_MAIL
                    ));

                    if ($oAuthModelInstance->activate(self::TEST_MAIL, self::$oUser->token) === false) {
                        die('Unable to generated a test user');
                    }
                }
            }

            if ($bAddToRootGroup === true) {
                if (self::setUserRoot() === false) {
                    die('Unable to add test User in Root group');
                }
            }

            return (bool) (self::$oUser->isLoaded() === true);
        } catch(\Exception $oException) {

            die(var_dump($oException->getMessage()));

            return false;
        }
    }

    private static function setUserRoot()
    {
        try {
            $oRootGroup = new Group();
            $oRootGroup->loadByParameters(array('name' => 'root'));

            if ($oRootGroup->isLoaded() === false) {
                die('No "root" group were found');
            }

            $oUserMappedGroup = self::$oUser->loadMapped(new Group(), array('name' => 'root'));
            if (is_null($oUserMappedGroup) === false) {
                return true;
            } else {
                $oInsert = new Insert();
                $oInsert->setFrom('userGroup', true)
                    ->addParameter(self::$oUser->computeForeignKeyName(), self::$oUser->getId())
                    ->addParameter($oRootGroup->computeForeignKeyName(), $oRootGroup->getId());

                $oStatement = Pdo::dbQuery(
                    $oInsert->build(),
                    array(
                        self::$oUser->computeForeignKeyName() => self::$oUser->getId(),
                        $oRootGroup->computeForeignKeyName() => $oRootGroup->getId()
                    )
                );

                return (bool) ($oStatement !== false);
            }
        } catch(\Exception $oException) {

            die(var_dump($oException->getMessage()));

            return false;
        }
    }

    protected static function deleteUserGroups()
    {
        try {
            $oDelete = new Delete();
            $oDelete->setFrom('userGroup')
                ->addWhereCondition(Operators::equal(self::$oUser->computeForeignKeyName()));

            $oStatement = Pdo::dbQuery(
                $oDelete->build(),
                array(
                    self::$oUser->computeForeignKeyName() => self::$oUser->getId()
                )
            );

            return (bool) ($oStatement !== false);
        } catch (\Exception $oException) {
            die(var_dump($oException->getMessage()));
            return false;
        }
    }

    protected static function cleanCreatedUsers($sUserEmailPattern)
    {
        // Clean created user from the register method unit test
        $oUser = new User();
        $oUser->loadByParameters(array(
            'mail' => $sUserEmailPattern
        ));

        if ($oUser->isLoaded() === true) {

            $oStatement = Pdo::dbQuery('
            SET FOREIGN_KEY_CHECKS=0;
            DELETE FROM `feedItem` WHERE `feed_idfeed` IN (SELECT `idfeed` FROM `feed` WHERE `user_iduser` = ?);
            DELETE FROM `feed` WHERE `user_iduser` = ?;
            DELETE FROM `userGroup` WHERE `user_iduser` = ?;
            SET FOREIGN_KEY_CHECKS=1;', array($oUser->getId(), $oUser->getId(), $oUser->getId()));
            if ($oStatement === false) {
                die('Unable to delete mapped user records');
            }

            $oUser->delete();
        } else {
            echo 'Error: Unable to retrieve and delete the user generated by the register method unit test';
        }
    }

}

class TestException extends \Exception
{
}
