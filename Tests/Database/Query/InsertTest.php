<?php
namespace Library\Core\Tests\Database\Query;

use Library\Core\Database\Query\Operators;
use Library\Core\Database\Query\Query;
use Library\Core\Database\Query\Insert;
use \Library\Core\Test as Test;


/**
 * Insert component unit tests
 *
 * @todo test de merde inmaintenable.............
 * 
 * @author Nicolas Bonnici <nicolasbonnici@gmail.com>
 */
class InsertTest extends Test
{
    protected static $oInsertInstance;

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
        self::$oInsertInstance = new Insert();
        $this->assertTrue(self::$oInsertInstance instanceof Insert);
        $this->assertEquals(
            Query::QUERY_TYPE_INSERT,
            self::$oInsertInstance->getQueryType()
        );

    }

    public function testSetParameters()
    {
        $this->assertTrue(self::$oInsertInstance->setParameters($this->aParameters) instanceof Insert);
    }

    public function testGetParameters()
    {
        $this->assertEquals(self::$oInsertInstance->getParameters(), array(
            '`prop1`' => '"value1"',
            '`prop2`' => 2,
            '`prop3`' => '"lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum "',

        ));
    }

    public function testAddParameter()
    {
        $this->assertTrue(self::$oInsertInstance->addParameter('prop4', 'value for prop 4') instanceof Insert);
    }

    public function testSetFrom()
    {
        $this->assertTrue(self::$oInsertInstance->setFrom('table_name') instanceof Insert);
    }

    public function testGetFrom()
    {
        $this->assertEquals(self::$oInsertInstance->getFrom() , 'table_name');
    }

    public function testBuild()
    {
        $this->assertEquals(
            'INSERT  INTO  table_name (`prop1`, `prop2`, `prop3`, `prop4`) VALUES("value1", 2, "lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum ", "value for prop 4")',
            self::$oInsertInstance->build()
        );
    }

    public function testAddWhereCondition()
    {
        $this->assertTrue(self::$oInsertInstance->addWhereCondition(Operators::equal('otherField1')) instanceof Insert);
        $this->assertEquals(
            'INSERT  INTO  table_name (`prop1`, `prop2`, `prop3`, `prop4`) VALUES("value1", 2, "lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum ", "value for prop 4") WHERE `otherField1` = :otherField1',
            self::$oInsertInstance->build()
        );
    }
    
}