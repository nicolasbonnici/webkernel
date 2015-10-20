<?php
namespace Library\Core\Tests\Database\Query;

use Library\Core\Database\Query\Operators;
use Library\Core\Database\Query\QueryAbstract;
use Library\Core\Database\Query\Delete;
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
        $this->assertEquals(self::$oDeleteInstance->getQueryType(), QueryAbstract::QUERY_TYPE_DELETE);

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
            'DELETE FROM table_name',
            self::$oDeleteInstance->build()
        );
    }

    public function testAddWhereCondition()
    {
        $this->assertTrue(self::$oDeleteInstance->addWhereCondition(Operators::equal('otherField1')) instanceof Delete);
        $this->assertEquals(
            'DELETE FROM table_name WHERE `otherField1` = :otherField1',
            self::$oDeleteInstance->build()
        );
    }
    
}