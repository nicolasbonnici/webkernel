<?php
namespace Library\Core\Tests\Database\Query;

use Library\Core\Tests\Test;

use Library\Core\Database\Query\Operators;
use Library\Core\Database\Query\Where;


/**
 * Where component unit tests
 * 
 * @author Nicolas Bonnici <nicolasbonnici@gmail.com>
 */
class WhereTest extends Test
{
    /**
     * @var Where
     */
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

    public function testAddWhereCondition()
    {
        $this->assertTrue(self::$oWhereInstance->addWhereCondition(Operators::equal('prop1')) instanceof Where);
        $this->assertTrue(self::$oWhereInstance->addWhereCondition(Operators::different('prop2'), Where::QUERY_WHERE_CONNECTOR_AND) instanceof Where);
        $this->assertTrue(self::$oWhereInstance->addWhereCondition(Operators::equal('prop3'), Where::QUERY_WHERE_CONNECTOR_AND) instanceof Where);
        $this->assertTrue(self::$oWhereInstance->addWhereCondition(Operators::bigger('prop4'), Where::QUERY_WHERE_CONNECTOR_AND) instanceof Where);
        $this->assertTrue(self::$oWhereInstance->addWhereCondition(Operators::smaller('prop5'), Where::QUERY_WHERE_CONNECTOR_AND) instanceof Where);
        $this->assertTrue(self::$oWhereInstance->addWhereCondition(Operators::biggerOrEqual('prop6'), Where::QUERY_WHERE_CONNECTOR_AND) instanceof Where);
        $this->assertTrue(self::$oWhereInstance->addWhereCondition(Operators::smallerOrEqual('prop7'), Where::QUERY_WHERE_CONNECTOR_AND) instanceof Where);
        $this->assertTrue(self::$oWhereInstance->addWhereCondition(Operators::like('prop8', 'value'), Where::QUERY_WHERE_CONNECTOR_OR) instanceof Where);
        $this->assertTrue(self::$oWhereInstance->addWhereCondition(Operators::in('prop8', array(1,2,3,4,5,6,7,8,9)), Where::QUERY_WHERE_CONNECTOR_AND) instanceof Where);
        $this->assertTrue(self::$oWhereInstance->addWhereCondition(Operators::smallerOrEqual('prop9'), Where::QUERY_WHERE_CONNECTOR_AND) instanceof Where);
    }

    public function testBuildWhere()
    {
        $this->assertEquals(
            'WHERE `prop1` = :prop1 AND `prop2` != :prop2 AND `prop3` = :prop3 AND `prop4` > :prop4 AND `prop5` < :prop5 AND `prop6` >= :prop6 AND `prop7` <= :prop7 OR `prop8` LIKE "%value%" AND `prop8` IN(?,?,?,?,?,?,?,?,?) AND `prop9` <= :prop9',
            self::$oWhereInstance->buildWhere()
        );
    }
}