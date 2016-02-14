<?php
namespace Library\Core\Tests\Entity;

use Library\Core\Database\Pdo;
use \Library\Core\Test as Test;

use Library\Core\Entity\Mapper;
use Library\Core\Tests\Dummy\Entities\Collection\Dummy2Collection;
use Library\Core\Tests\Dummy\Entities\Collection\Dummy3Collection;
use Library\Core\Tests\Dummy\Entities\Dummy;
use Library\Core\Tests\Dummy\Entities\Dummy2;
use Library\Core\Tests\Dummy\Entities\Dummy3;
use Library\Core\Tests\Dummy\Entities\Dummy4;

/**
 * ORM Mapper component unit tests
 * 
 * @author Nicolas Bonnici <nicolasbonnici@gmail.com>
 */
class MapperTest extends Test
{
    /**
     * @var Mapper
     */
    private $oEntityMapperInstance;

    protected function setUp()
    {
        self::loadUser(true);
        $this->oEntityMapperInstance = $this->getEntityMapper();
    }

    /**
     * OneToOne mapping test
     */

    public function testStoreMappedOneToOneEntity()
    {
        $oDummy4 = new Dummy4();
        $oDummy4->foo = 'Test string value';

        $oDummy4->setUser(self::$oUser);

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
            'Mapper test mapped dummy4',
            $oMappedDummy4->foo
        );
    }

    public function testDeleteOneToOneEntity()
    {
        $oDummy4 = new Dummy4();
        $oDummy4->foo = 'Test string value';

        $oDummy4->setUser(self::$oUser);

        $this->oEntityMapperInstance->store($oDummy4);
        $this->assertTrue(
            $this->oEntityMapperInstance->delete($oDummy4),
            'Unable to delete a one to one mapping'
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

            $oDummy2->setUser(self::$oUser);

            $this->assertTrue(
                $this->oEntityMapperInstance->store($oDummy2),
                'Unable to store a one to many Entities mapping'
            );
        }

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

    public function testDeleteOneToManyEntity()
    {
        $oDummy2 = new Dummy2();
        $oDummy2->string = 'Test entity mapper';

        $oDummy2->setUser(self::$oUser);

        $this->oEntityMapperInstance->store($oDummy2);

        $this->assertTrue(
            $this->oEntityMapperInstance->delete($oDummy2),
            'Unable to delete a one to many Entities mapping'
        );
    }

    /**
     * ManyToMany mapping test
     */

    public function testStoreThenLoadMappedManyToManyEntity()
    {
        for ($i = 0; $i < 100; $i++) {
            $oDummy3 = new Dummy3();
            $oDummy3->int = 33;

            $oDummy3->setUser(self::$oUser);

            $this->assertTrue(
                $this->oEntityMapperInstance->store($oDummy3),
                'Unable to store a one to many Entities mapping'
            );
        }

        $oMappedDummy3Collection = $this->oEntityMapperInstance->loadMapped(new Dummy3());
        $this->assertTrue(
            $oMappedDummy3Collection instanceof Dummy3Collection,
            'Unable to load a one to many mapping'
        );

        $this->assertTrue(
            $oMappedDummy3Collection->count() > 0
        );

    }

    public function testDeleteManyToManyEntity()
    {
        $oDummy3 = new Dummy3();
        $oDummy3->int = 66;

        $oDummy3->setUser(self::$oUser);

        $this->oEntityMapperInstance->store($oDummy3);
        $this->assertTrue(
            $this->oEntityMapperInstance->delete($oDummy3),
            'Unable to store a one to many Entities mapping'
        );
    }

    /**
     * Create a Mapper instance
     *
     * @return Mapper|null
     * @throws \Library\Core\Entity\EntityException
     */
    private function getEntityMapper()
    {

        $aDummyEntityData = array(
            'test_string' => 'Test entity mapper',
            'test_int'    => 33,
            'test_float'  => '666.99235',
            'test_null'   => null,
            'lastupdate'  => time(),
            'created'     => time(),
        );

        $oDummy = new Dummy();
        $oDummy->loadByParameters($aDummyEntityData);
        if ($oDummy->isLoaded() === false) {

            $oMappedDummy4 = new Dummy4();
            $oMappedDummy4->foo = 'Mapper test mapped dummy4';

            $oMappedDummy4->setUser(self::$oUser);

            $oMappedDummy4->create();

            $oDummy->test_string      = $aDummyEntityData['test_string'];
            $oDummy->test_int         = $aDummyEntityData['test_int'];
            $oDummy->test_float       = $aDummyEntityData['test_float'];
            $oDummy->test_null        = $aDummyEntityData['test_null'];
            $oDummy->lastupdate       = $aDummyEntityData['lastupdate'];
            $oDummy->created          = $aDummyEntityData['created'];
            $oDummy->dummy4_iddummy4  = $oMappedDummy4->getId();

            $oDummy->setUser(self::$oUser);

            $oDummy->create();

        }

        $oEntityMapper = new Mapper();
        $oEntityMapper->setSourceEntity($oDummy);
        return $oEntityMapper;
    }

    public function testDeleteMappedEntities()
    {
        $this->assertTrue(
            $this->oEntityMapperInstance->deleteMapped(),
            'Unable to delete all mapped entities in MapperTest'
        );
    }

    /**
     * This method is called after the last test of this test class is run.
     *
     * @since Method available since Release 3.4.0
     */
    public static function tearDownAfterClass()
    {
        # Truncate tables
        $aLog = array();
        $sQueries = 'SET FOREIGN_KEY_CHECKS=0;
            TRUNCATE TABLE `dummy`;
            TRUNCATE TABLE `dummy1`;
            TRUNCATE TABLE `dummy2`;
            TRUNCATE TABLE `dummy3`;
            TRUNCATE TABLE `dummy4`;
            TRUNCATE TABLE `dummyDummy3`;
            SET FOREIGN_KEY_CHECKS=1;';
        foreach (explode(';', $sQueries) as $sQuery) {
            $aLog[] = $oStatement = Pdo::dbQuery($sQuery);
        }
        return (bool) (in_array(false, $aLog) === false);
    }


}