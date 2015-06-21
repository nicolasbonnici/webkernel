<?php
namespace Library\Core\Tests\Database\Query;

use Library\Core\Database\Query\Operators;
use \Library\Core\Test as Test;


/**
 * Operators component unit tests
 * 
 * @author Nicolas Bonnici <nicolasbonnici@gmail.com>
 */
class OperatorsTest extends Test
{
    protected static $oOperatorsInstance;

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
        self::$oOperatorsInstance = new Operators();
        $this->assertTrue(self::$oOperatorsInstance instanceof Operators);
    }

    public function testEqual()
    {
        $this->assertEquals(self::$oOperatorsInstance->equal('prop'), '`prop` = :?');
    }

    public function testDifferent()
    {
        $this->assertEquals(self::$oOperatorsInstance->different('prop'), '`prop` != :?');
    }

    public function testBigger()
    {
        $this->assertEquals(self::$oOperatorsInstance->bigger('prop'), '`prop` > :?');
    }


    public function testBiggerOrEqual()
    {
        $this->assertEquals(self::$oOperatorsInstance->biggerOrEqual('prop'), '`prop` >= :?');
    }

    public function testSmaller()
    {
        $this->assertEquals(self::$oOperatorsInstance->smaller('prop'), '`prop` < :?');
    }


    public function testSmallerOrEqual()
    {
        $this->assertEquals(self::$oOperatorsInstance->smallerOrEqual('prop'), '`prop` <= :?');
    }

    public function testLike()
    {
        $sPreparatedLikeParameter = self::$oOperatorsInstance->like('prop');
        $this->assertEquals($sPreparatedLikeParameter, '`prop` ' . Operators::OPERATOR_LIKE . ' %prop%');
        $sPreparatedLikeParameter = self::$oOperatorsInstance->like('prop', true, false);
        $this->assertEquals($sPreparatedLikeParameter, '`prop` ' . Operators::OPERATOR_LIKE . ' %prop');
        $sPreparatedLikeParameter = self::$oOperatorsInstance->like('prop', false, true);
        $this->assertEquals($sPreparatedLikeParameter, '`prop` ' . Operators::OPERATOR_LIKE . ' prop%');
        $sPreparatedLikeParameter = self::$oOperatorsInstance->like('prop', false, false);
        $this->assertEquals($sPreparatedLikeParameter, '`prop` ' . Operators::OPERATOR_LIKE . ' prop');
    }

}