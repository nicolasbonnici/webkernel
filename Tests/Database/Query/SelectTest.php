<?php
namespace Library\Core\Tests\Database\Query;

use Library\Core\Tests\Test;
use Library\Core\Database\Query\QueryAbstract;
use Library\Core\Database\Query\Select;


/**
 * Select component unit tests
 * 
 * @author Nicolas Bonnici <nicolasbonnici@gmail.com>
 */
class SelectTest extends Test
{

    /**
     * @var Select
     */
    protected static $oSelectInstance;

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
        self::$oSelectInstance = new Select();
        $this->assertTrue(self::$oSelectInstance instanceof Select);
        $this->assertEquals(self::$oSelectInstance->getQueryType(), QueryAbstract::QUERY_TYPE_SELECT);

    }

    public function testAddColumns()
    {
        $this->assertTrue(self::$oSelectInstance->addColumns($this->aColumns) instanceof Select);
    }

    public function testGetColumns()
    {
        $this->assertEquals(self::$oSelectInstance->getColumns(), $this->aColumns);
    }

    public function testAddColumn()
    {
        $this->assertTrue(self::$oSelectInstance->addColumn('prop4') instanceof Select);
        $this->aColumns = array_merge($this->aColumns, array('prop4'));
        $this->assertEquals(self::$oSelectInstance->getColumns(), $this->aColumns);

    }

    public function testSetFromEscaped()
    {
        $this->assertTrue(self::$oSelectInstance->setFrom('table_name', true) instanceof Select);
        $this->assertEquals(self::$oSelectInstance->getFrom() , '`table_name`');
    }

    public function testSetFrom()
    {
        $this->assertTrue(self::$oSelectInstance->setFrom('table_name AS t') instanceof Select);
    }

    public function testGetFrom()
    {
        $this->assertEquals(self::$oSelectInstance->getFrom() , 'table_name AS t');
    }

    public function testSetOrderBy()
    {
        $this->assertTrue(self::$oSelectInstance->setOrderBy(array('prop1', 'prop2', 'prop3')) instanceof Select);

    }

    public function getOrderBy()
    {
        $this->assertEquals(self::$oSelectInstance->getOrderBy() , array('prop1', 'prop2', 'prop3'));
    }

    public function testSetOrder()
    {
        $this->assertTrue(self::$oSelectInstance->setDefaultOrder(Select::QUERY_ORDER_ASC) instanceof Select);
        $this->assertTrue(self::$oSelectInstance->setDefaultOrder(Select::QUERY_ORDER_DESC) instanceof Select);
        self::$oSelectInstance->setDefaultOrder('not in scope');
        $this->assertTrue(self::$oSelectInstance->setDefaultOrder(Select::QUERY_ORDER_DESC) instanceof Select);
    }

    public function testSetGroupBy()
    {
        $this->assertTrue(self::$oSelectInstance->setGroupBy(array('prop1', 'prop2', 'prop3')) instanceof Select);

    }

    public function testGetGroupBy()
    {
        $this->assertEquals(self::$oSelectInstance->getGroupBy() , array('prop1', 'prop2', 'prop3'));
    }

    public function testSetLimit()
    {
        $this->assertTrue(self::$oSelectInstance->setLimit(array(1099, 25)) instanceof Select);
    }

    public function testGetLimit()
    {
        $this->assertEquals(self::$oSelectInstance->getLimit() , array(1099, 25));
    }


    public function testBuild()
    {
        $this->assertEquals(
            'SELECT `prop1`, `prop2`, `prop3`, `prop4` FROM table_name AS t GROUP BY `prop1`, `prop2`, `prop3` ORDER BY `prop1`, `prop2`, `prop3` DESC LIMIT 1099, 25',
            self::$oSelectInstance->build()
        );
    }
}