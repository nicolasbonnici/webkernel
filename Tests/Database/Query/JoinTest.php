<?php
namespace Library\Core\Tests\Database\Query;

use Library\Core\Tests\Test;
use Library\Core\Database\Query\Join;


/**
 * Join component unit tests
 * 
 * @author Nicolas Bonnici <nicolasbonnici@gmail.com>
 */
class JoinTest extends Test
{
    protected static $oJoinInstance;

    protected $aColumns = array(
    	'prop1',
        'prop2',
        'prop3'
    );

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
        self::$oJoinInstance = new Join();
        $this->assertTrue(self::$oJoinInstance instanceof Join);

    }

    public function testAddJoin()
    {
        $this->assertTrue(self::$oJoinInstance->addJoin('LEFT JOIN FROM other_table ot ON ot.id = t.id') instanceof Join);
    }

    public function testAddJoins()
    {
        $this->assertTrue(self::$oJoinInstance->addJoins(array('LEFT JOIN FROM other_table ot ON ot.id = t.id', 'LEFT JOIN FROM other_table ot ON ot.id = t.id')) instanceof Join);
    }

    public function testGetJoins()
    {
        $this->assertEquals(self::$oJoinInstance->getJoins(), array('LEFT JOIN FROM other_table ot ON ot.id = t.id', 'LEFT JOIN FROM other_table ot ON ot.id = t.id', 'LEFT JOIN FROM other_table ot ON ot.id = t.id'));
    }

}