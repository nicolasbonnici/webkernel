<?php
namespace Library\Core\Tests\Select;

use \Library\Core\Test as Test;

use Library\Core\Orm\EntityMapper;
use Library\Core\Tests\Dummy\Entities\Collection\Dummy2Collection;
use Library\Core\Tests\Dummy\Entities\Collection\Dummy3Collection;
use Library\Core\Tests\Dummy\Entities\Dummy;
use Library\Core\Tests\Dummy\Entities\Dummy2;
use Library\Core\Tests\Dummy\Entities\Dummy3;
use Library\Core\Tests\Dummy\Entities\Dummy4;

/**
 * ORM EntityMapper component unit tests
 * 
 * @author Nicolas Bonnici <nicolasbonnici@gmail.com>
 */
class EntityMapperTest extends Test
{
    /**
     * @var EntityMapper
     */
    private $oEntityMapperInstance;

    private $iDummyId = 0;

    protected function setUp()
    {
        $this->oEntityMapperInstance = $this->getEntityMapper();
    }

    /**
     * OneToOne mapping test
     */

    public function testStoreMappedOneToOneEntity()
    {

        $oDummy4 = new Dummy4();
        $oDummy4->foo = 'Test string value';
        $this->assertTrue(
            $this->oEntityMapperInstance->store($oDummy4),
            'Unable to store a one to one mapping'
        );
    }

    public function testLoadMappedOneToOneEntity()
    {
        $oMappedDummy4 = $this->oEntityMapperInstance->loadMapped(new Dummy4());
        $this->assertTrue(
            $oMappedDummy4 instanceof Dummy4,
            'Unable to load a one to one mapping'
        );

        $this->assertTrue(
            $oMappedDummy4->isLoaded()
        );

        $this->assertEquals(
            $oMappedDummy4->foo,
            'Test string value'
        );
    }


    /**
     * OneToMany mapping test
     */

    public function testStoreThenLoadMappedOneToManyEntities()
    {
        for ($i = 0; $i < 10; $i++) {
            $oDummy2 = new Dummy2();
            $oDummy2->string = 'Test entity mapper';
            $this->assertTrue(
                $this->oEntityMapperInstance->store($oDummy2),
                'Unable to store a one to many Entities mapping'
            );
        }
    }

    public function testStoreMappedOneToManyEntity()
    {
        $oMappedDummy2Collection = $this->oEntityMapperInstance->loadMapped(new Dummy2());
        $this->assertTrue(
            $oMappedDummy2Collection instanceof Dummy2Collection,
            'Unable to load a one to many mapping'
        );

        $this->assertTrue(
            $oMappedDummy2Collection->count() > 0
        );

        $this->assertEquals(
            10,
            $oMappedDummy2Collection->count()
        );
    }

    /**
     * ManyToMany mapping test
     */

    public function testStoreMappedManyToManyEntity()
    {
        for ($i = 0; $i < 100; $i++) {
            $oDummy3 = new Dummy3();
            $oDummy3->string = 'Test entity mapper';
            $this->assertTrue(
                $this->oEntityMapperInstance->store($oDummy3),
                'Unable to store a one to many Entities mapping'
            );
        }
    }

    public function testLoadMappedManyToManyEntity()
    {
        $oMappedDummy3Collection = $this->oEntityMapperInstance->loadMapped(new Dummy3());
        $this->assertTrue(
            $oMappedDummy3Collection instanceof Dummy3Collection,
            'Unable to load a one to many mapping'
        );

        $this->assertTrue(
            $oMappedDummy3Collection->count() > 0
        );

    }


    /**
     * Create a EntityMapper instance
     *
     * @return EntityMapper|null
     * @throws \Library\Core\Orm\EntityException
     */
    private function getEntityMapper()
    {
        $aDummyEntityData = array(
            'test_string' => 'Test entity mapper',
            'test_int'    => 33,
            'test_float'  => 666.99235,
            'test_null'   => null,
            'lastupdate'  => time(),
            'created'     => time(),
        );

        $oDummy = new Dummy();
        $oDummy->loadByParameters(
            array(
                $oDummy->getPrimaryKeyName() => 1
            )
        );
        if ($oDummy->isLoaded() === false) {
            $oDummy->test_string      = $aDummyEntityData['test_string'];
            $oDummy->test_int         = $aDummyEntityData['test_int'];
            $oDummy->test_float       = $aDummyEntityData['test_float'];
            $oDummy->test_null        = $aDummyEntityData['test_null'];
            $oDummy->lastupdate       = $aDummyEntityData['lastupdate'];
            $oDummy->created          = $aDummyEntityData['created'];

            $oDummy->add();

        }

        $oEntityMapper = new EntityMapper();
        $oEntityMapper->setSourceEntity($oDummy);
        return $oEntityMapper;
    }

}