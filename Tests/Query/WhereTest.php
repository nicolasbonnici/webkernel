<?php
namespace Library\Core\Tests\Query;

use Library\Core\Query\Where;
use \Library\Core\Test as Test;


/**
 * Where component unit tests
 * 
 * @author Nicolas Bonnici <nicolasbonnici@gmail.com>
 */
class WhereTest extends Test
{
    protected static $oWhereInstance;

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
        self::$oWhereInstance = new Where();
        $this->assertTrue(self::$oWhereInstance instanceof Where);

    }

    public function testAddWhere()
    {
        $this->assertTrue(self::$oWhereInstance->addWhere('prop1 != 1') instanceof Where);
        $this->assertTrue(self::$oWhereInstance->addWhere('prop2 != 9') instanceof Where);
    }

    public function testGetWhere()
    {
        $this->assertEquals(self::$oWhereInstance->getWhere(), array('prop1 != 1', 'prop2 != 9'));
    }
}