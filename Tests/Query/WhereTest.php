<?php
namespace Core\Tests\Query;

use Core\Query\Operators;
use Core\Query\Where;
use \Core\Test as Test;


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

    public function testAddWhereCondition()
    {
        $this->assertTrue(self::$oWhereInstance->addWhereCondition(Operators::equal('prop1')) instanceof Where);
        $this->assertTrue(self::$oWhereInstance->addWhereCondition(Operators::different('prop2'), Where::QUERY_WHERE_CONNECTOR_AND) instanceof Where);
        $this->assertTrue(self::$oWhereInstance->addWhereCondition(Operators::equal('prop3'), Where::QUERY_WHERE_CONNECTOR_AND) instanceof Where);
        $this->assertTrue(self::$oWhereInstance->addWhereCondition(Operators::bigger('prop4'), Where::QUERY_WHERE_CONNECTOR_AND) instanceof Where);
        $this->assertTrue(self::$oWhereInstance->addWhereCondition(Operators::smaller('prop5'), Where::QUERY_WHERE_CONNECTOR_AND) instanceof Where);
        $this->assertTrue(self::$oWhereInstance->addWhereCondition(Operators::biggerOrEqual('prop6'), Where::QUERY_WHERE_CONNECTOR_AND) instanceof Where);
        $this->assertTrue(self::$oWhereInstance->addWhereCondition(Operators::smallerOrEqual('prop7'), Where::QUERY_WHERE_CONNECTOR_AND) instanceof Where);
        $this->assertTrue(self::$oWhereInstance->addWhereCondition(Operators::like('prop8'), Where::QUERY_WHERE_CONNECTOR_OR) instanceof Where);
        $this->assertTrue(self::$oWhereInstance->addWhereCondition(Operators::in('prop8', 8)) instanceof Where);
    }

    public function testBuildWhere()
    {
        $this->assertEquals(self::$oWhereInstance->buildWhere(), 'WHERE `prop1` = :? AND `prop2` != :? AND `prop3` = :? AND `prop4` > :? AND `prop5` < :? AND `prop6` >= :? AND `prop7` <= :? OR `prop8` LIKE %prop8%`prop8` IN(?,?,?,?,?,?,?,?)');
    }
}