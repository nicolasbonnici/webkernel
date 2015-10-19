<?php
namespace Library\Core\Tests\Orm;

use Library\Core\Bootstrap;
use \Library\Core\Test as Test;
use Library\Core\Orm\EntityParser;


/**
 * ORM EntityParser component unit tests
 * 
 * @author Nicolas Bonnici <nicolasbonnici@gmail.com>
 */
class EntityParserTest extends Test
{
    protected static $oEntityParserInstance;

    /**
     * Member to store test entity primary key value
     * @var integer
     */
    protected static $iCreatedDummyId;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
    }

    protected function setUp()
    {
    }

    protected function tearDown()
    {
    }

    public function testConstructor()
    {
        self::$oEntityParserInstance = new EntityParser(Bootstrap::getPath(Bootstrap::PATH_APP) . 'Entities/');
        $this->assertTrue(self::$oEntityParserInstance instanceof EntityParser);
    }

    public function testGetPath()
    {
        $this->assertEquals(
            Bootstrap::getPath(Bootstrap::PATH_APP) . 'Entities/',
            self::$oEntityParserInstance->getPath()
        );
    }

    public function testGetEntities()
    {
        $this->assertTrue(is_array(self::$oEntityParserInstance->getEntities()));
    }
}