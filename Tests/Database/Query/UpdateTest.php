<?php
namespace Library\Core\Tests\Database\Query;

use Library\Core\Database\Query\Operators;
use Library\Core\Database\Query\Query;
use Library\Core\Database\Query\Update;
use \Library\Core\Test as Test;


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
        $this->assertEquals(self::$oUpdateInstance->getQueryType(), Query::QUERY_TYPE_UPDATE);

    }

    public function testSetParameters()
    {
        $this->assertTrue(self::$oUpdateInstance->setParameters($this->aParameters) instanceof Update);
    }

    public function testGetParameters()
    {
        $this->assertEquals(self::$oUpdateInstance->getParameters(), array(
            '`prop1`' => '"value1"',
            '`prop2`' => 2,
            '`prop3`' => '"lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum "',

        ));
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
            'UPDATE table_name (`prop1`, `prop2`, `prop3`, `prop4`) VALUES("value1", 2, "lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum ", "value for prop 4")',
            self::$oUpdateInstance->build()
        );
    }

    public function testAddWhereCondition()
    {
        $this->assertTrue(self::$oUpdateInstance->addWhereCondition(Operators::equal('otherField1')) instanceof Update);
        $this->assertEquals(
            'UPDATE table_name (`prop1`, `prop2`, `prop3`, `prop4`) VALUES("value1", 2, "lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum ", "value for prop 4") WHERE `otherField1` = :otherField1',
            self::$oUpdateInstance->build()
        );
    }
    
}