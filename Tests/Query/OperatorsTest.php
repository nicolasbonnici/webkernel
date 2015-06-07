<?php
namespace Library\Core\Tests\Select;

use Library\Core\Query\Operators;
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

    public function testEqualAs()
    {
        $this->assertEquals(self::$oOperatorsInstance->equalAs('prop'), ' = prop');
    }

    public function testDifferentThan()
    {
        $this->assertEquals(self::$oOperatorsInstance->differentThan('prop'), ' != prop');
    }

    public function testBiggerThan()
    {
        $this->assertEquals(self::$oOperatorsInstance->biggerThan('prop'), ' > prop');
    }


    public function testBiggerOrEqualThan()
    {
        $this->assertEquals(self::$oOperatorsInstance->biggerOrEqualThan('prop'), ' >= prop');
    }

    public function testSmallerThan()
    {
        $this->assertEquals(self::$oOperatorsInstance->smallerThan('prop'), ' < prop');
    }


    public function testSmallerOrEqualThan()
    {
        $this->assertEquals(self::$oOperatorsInstance->smallerOrEqualThan('prop'), ' <= prop');
    }

    public function testLike()
    {
        $sPreparatedLikeParameter = self::$oOperatorsInstance->like('prop');
        $this->assertEquals($sPreparatedLikeParameter, ' ' . Operators::OPERATOR_LIKE . ' %prop%');
        $sPreparatedLikeParameter = self::$oOperatorsInstance->like('prop', true, false);
        $this->assertEquals($sPreparatedLikeParameter, ' ' . Operators::OPERATOR_LIKE . ' %prop');
        $sPreparatedLikeParameter = self::$oOperatorsInstance->like('prop', false, true);
        $this->assertEquals($sPreparatedLikeParameter, ' ' . Operators::OPERATOR_LIKE . ' prop%');
        $sPreparatedLikeParameter = self::$oOperatorsInstance->like('prop', false, false);
        $this->assertEquals($sPreparatedLikeParameter, ' ' . Operators::OPERATOR_LIKE . ' prop');
    }

}