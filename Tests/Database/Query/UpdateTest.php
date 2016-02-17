<?php
namespace Library\Core\Tests\Database\Query;

use Library\Core\Tests\Test;
use Library\Core\Database\Query\Operators;
use Library\Core\Database\Query\QueryAbstract;
use Library\Core\Database\Query\Update;


/**
 * Update component unit tests
 *
 * @todo test de merde inmaintenable.............
 * 
 * @author Nicolas Bonnici <nicolasbonnici@gmail.com>
 */
class UpdateTest extends Test
{
    protected static $oUpdateInstance;

    protected $aParameters = array(
        'prop1' => 'value1',
        'prop2' => 2,
        'prop3' => 'lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum '
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
        self::$oUpdateInstance = new Update();
        $this->assertTrue(self::$oUpdateInstance instanceof Update);
        $this->assertEquals(QueryAbstract::QUERY_TYPE_UPDATE, self::$oUpdateInstance->getQueryType());

    }

    public function testSetParameters()
    {
        $this->assertTrue(self::$oUpdateInstance->setParameters($this->aParameters) instanceof Update);
    }

    public function testGetParameters()
    {
        $this->assertEquals(
            array(
                'prop1' => '"value1"',
                'prop2' => 2,
                'prop3' => '"lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum "',
            ),
            self::$oUpdateInstance->getParameters()
        );
    }

    public function testAddParameter()
    {
        $this->assertTrue(self::$oUpdateInstance->addParameter('prop4', 'value for prop 4') instanceof Update);
    }

    public function testSetFrom()
    {
        $this->assertTrue(self::$oUpdateInstance->setFrom('table_name') instanceof Update);
    }

    public function testGetFrom()
    {
        $this->assertEquals(self::$oUpdateInstance->getFrom() , 'table_name');
    }

    public function testBuild()
    {
        $this->assertEquals(
            'UPDATE table_name SET `prop1` = ?, `prop2` = ?, `prop3` = ?, `prop4` = ?',
            self::$oUpdateInstance->build()
        );
    }

    public function testAddWhereCondition()
    {
        $this->assertTrue(self::$oUpdateInstance->addWhereCondition(Operators::equal('otherField1', false)) instanceof Update);
        $this->assertEquals(
            'UPDATE table_name SET `prop1` = ?, `prop2` = ?, `prop3` = ?, `prop4` = ? WHERE `otherField1` = ?',
            self::$oUpdateInstance->build()
        );
    }
    
}