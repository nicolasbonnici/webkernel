<?php
namespace Library\Core\Tests\Select;

use \Library\Core\Test as Test;

use Library\Core\Orm\EntityMapper;
use Library\Core\Tests\Dummy\Entities\DummyEntity;


/**
 * ORM EntityMapper component unit tests
 * 
 * @author Nicolas Bonnici <nicolasbonnici@gmail.com>
 */
class EntityMapperTest extends Test
{
    protected $oEntityMapperInstance;

    protected function setUp()
    {
    }

    public function testConstructor()
    {
        $aDummyEntityData = array(
            'test_string' => 'Test entity mapper',
            'test_int'    => 33,
            'test_float'  => 666.99235,
            'test_null'   => null,
            'lastupdate'  => time(),
            'created'     => time(),
        );

        $oDummyEntity = new DummyEntity();
        $oDummyEntity->test_string  = $aDummyEntityData['test_string'];
        $oDummyEntity->test_int     = $aDummyEntityData['test_int'];
        $oDummyEntity->test_float   = $aDummyEntityData['test_float'];
        $oDummyEntity->test_null    = $aDummyEntityData['test_null'];
        $oDummyEntity->lastupdate   = $aDummyEntityData['lastupdate'];
        $oDummyEntity->created      = $aDummyEntityData['created'];
        $oDummyEntity->add();

        $this->oEntityMapperInstance = new EntityMapper($oDummyEntity);
        $this->assertTrue($this->oEntityMapperInstance instanceof EntityMapper);
    }

    public function testLoad()
    {

    }

    public function testLoadMapping()
    {

    }

    public function testLoadMappedEntityWithNoRelation()
    {

    }

    public function testLoadMappedEntityWithMappedEntity()
    {

    }

    public function testLoadMappedEntitiesWithNoRelation()
    {

    }

    public function testLoadMappedEntitiesWithMappedEntity()
    {

    }

    public function testStoreOneToOneMappedEntity()
    {

    }

    public function testStoreOneToManyMappedEntity()
    {

    }
}