<?php
namespace Library\Core\Tests\Select;

use \Library\Core\Test as Test;

use Library\Core\Orm\EntityMapper;
use Library\Core\Tests\Dummy\Entities\DummyEntity;
use Library\Core\Tests\Dummy\Entities\OnetomanyEntity;
use Library\Core\Tests\Dummy\Entities\OnetomanyEntityCollection;
use Library\Core\Tests\Dummy\Entities\OnetooneEntity;

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

    private static $iDummmyId;

    /**
     * Display class name before run all testcase methods
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

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

        self::$iDummmyId = $oDummyEntity->getId();
    }

    /**
     * Cleanup created entities on tear down after class
     */
    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();

        $oDummyEntity = new DummyEntity(self::$iDummmyId);
        if($oDummyEntity->delete() === false) {
            throw new \Exception('Unable top delete created DummyEntity by test ' . get_called_class());
        }
    }


    protected function setUp()
    {
        $oDummyEntity = new DummyEntity(self::$iDummmyId);
        $this->oEntityMapperInstance = new EntityMapper($oDummyEntity);
    }

    public function testConstructor()
    {
        $this->assertTrue($this->oEntityMapperInstance instanceof EntityMapper);
    }

    public function testStoreMappedOneToOneEntity()
    {
        $oOneToOneEntity = new OnetooneEntity();
        $oOneToOneEntity->int = 999;
        $oOneToOneEntity->lastupdate = time();
        $oOneToOneEntity->created = time();

        $this->assertTrue($this->oEntityMapperInstance->store($oOneToOneEntity), 'Unable to store a mapped Entity');
    }

    public function testStoreOneToManyMappedEntity()
    {
        $oOneToManyEntity = new OnetomanyEntity();
        $oOneToManyEntity->int = 7;
        $oOneToManyEntity->lastupdate = time();
        $oOneToManyEntity->created = time();

        $oOneToManyCollection = new OnetomanyEntityCollection();
        $oOneToManyCollection->add($oOneToManyEntity);

        $this->assertTrue(
            $this->oEntityMapperInstance->store($oOneToManyCollection),
            'Unable to store One to Many mapped entity'
        );
    }


}