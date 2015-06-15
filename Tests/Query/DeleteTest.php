<?php
namespace Library\Core\Tests\Delete;

use Library\Core\Query\Operators;
use Library\Core\Query\Query;
use Library\Core\Query\Delete;
use \Library\Core\Test as Test;


/**
 * Delete component unit tests
 *
 * @author Nicolas Bonnici <nicolasbonnici@gmail.com>
 */
class DeleteTest extends Test
{
    protected static $oDeleteInstance;

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
        self::$oDeleteInstance = new Delete();
        $this->assertTrue(self::$oDeleteInstance instanceof Delete);
        $this->assertEquals(self::$oDeleteInstance->getQueryType(), Query::QUERY_TYPE_DELETE);

    }

    public function testSetFrom()
    {
        $this->assertTrue(self::$oDeleteInstance->setFrom('table_name') instanceof Delete);
    }

    public function testGetFrom()
    {
        $this->assertEquals(self::$oDeleteInstance->getFrom() , 'table_name');
    }

    public function testBuild()
    {
        $this->assertEquals(
            self::$oDeleteInstance->build(),
            'DELETE FROM table_name'
        );
    }

    public function testAddWhereCondition()
    {
        $this->assertTrue(self::$oDeleteInstance->addWhereCondition(Operators::equal('otherField1')) instanceof Delete);
        $this->assertEquals(
            self::$oDeleteInstance->build(),
            'DELETE FROM table_name WHERE `otherField1` = :?'
        );
    }
    
}