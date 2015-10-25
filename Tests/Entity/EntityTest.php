<?php
namespace Library\Core\Tests\Entity;

use Library\Core\Entity\Entity;
use Library\Core\Test;
use Library\Core\Tests\Dummy\Entities\Dummy;

/**
 * ORM Entity component unit tests
 * 
 * @author Nicolas Bonnici <nicolasbonnici@gmail.com>
 */
class EntityTest extends Test
{
    /**
     * @var Entity
     */
    protected $oDummyEntity;

    protected $aTestData = array(
        'test_string' => 'Some test string',
        'test_int'    => 199,
        'test_float'  => 666.99235,
        'test_null'   => null,
        'lastupdate'   => 1434675542,
        'created'   => 1434675542
    );

    /**
     * Member to store test entity primary key value
     * @var integer
     */
    protected static $iCreatedDummyId;

    protected function setUp()
    {
        $this->oDummyEntity = new Dummy();
    }

    public function testConstructor()
    {
        $this->assertTrue($this->oDummyEntity instanceof Entity);
    }

    // Test configuration
    public function testIsDeletable()
    {
        $this->assertEquals(true, $this->oDummyEntity->isDeletable());
    }
    public function testIsCacheable()
    {
        $this->assertEquals(true, $this->oDummyEntity->isCacheable());
    }
    public function testIsSearchable()
    {
        $this->assertEquals(true, $this->oDummyEntity->isSearchable());
    }
    public function testIsHistorized()
    {
        $this->assertEquals(false, $this->oDummyEntity->isHistorized());
    }

    public function testToString()
    {
        $this->assertEquals($this->oDummyEntity->getChildClass(), $this->oDummyEntity->__toString());
    }

    public function testIsLoaded()
    {
        $this->assertEquals($this->oDummyEntity->isLoaded(), false);
    }

    public function testAddThenRetrieveEntityId()
    {
        $this->oDummyEntity->test_string        = $this->aTestData['test_string'];
        $this->oDummyEntity->test_int           = $this->aTestData['test_int'];
        $this->oDummyEntity->test_float         = $this->aTestData['test_float'];
        $this->oDummyEntity->test_null          = $this->aTestData['test_null'];
        $this->oDummyEntity->lastupdate         = $this->aTestData['lastupdate'];
        $this->oDummyEntity->created            = $this->aTestData['created'];
        $this->oDummyEntity->dummy4_iddummy4    = 1;

        $this->assertTrue($this->oDummyEntity->add());
        self::$iCreatedDummyId = $this->oDummyEntity->getId();
        $this->assertTrue(self::$iCreatedDummyId > 0);

        $this->assertTrue($this->oDummyEntity->getId() > 0);

        $this->assertEquals(
            true,
            $this->oDummyEntity->isLoaded()
        );

        $this->assertEquals(
            self::$iCreatedDummyId,
            $this->oDummyEntity->getId()
        );
    }

    public function testConstructorWithString()
    {
        $this->oDummyEntity = new Dummy((string) self::$iCreatedDummyId);
        $this->assertTrue($this->oDummyEntity->isLoaded() === true);
        $this->assertEquals(self::$iCreatedDummyId, $this->oDummyEntity->getId());
    }

    public function testConstructorWithInteger()
    {
        $this->oDummyEntity = new Dummy((int) self::$iCreatedDummyId);
        $this->assertTrue($this->oDummyEntity->isLoaded() === true);
        $this->assertEquals(self::$iCreatedDummyId, $this->oDummyEntity->getId());
    }

    public function testConstructorWithArray()
    {
        $this->oDummyEntity = new Dummy(array(Dummy::PRIMARY_KEY => self::$iCreatedDummyId));
        $this->assertTrue($this->oDummyEntity->isLoaded() === true);
        $this->assertEquals(self::$iCreatedDummyId, $this->oDummyEntity->getId());
    }

    public function testUpdate()
    {
        $this->oDummyEntity = new Dummy(self::$iCreatedDummyId);
        $this->oDummyEntity->test_string  = 'Other string value';
        $this->assertTrue($this->oDummyEntity->update());
    }

    public function testReset()
    {
        $this->oDummyEntity->test_string = 'some test string';
        $this->oDummyEntity->test_int = 3;
        $this->oDummyEntity->created = 111111;
        # also test on non existing attributes
        $this->oDummyEntity->toto = 'tata';

        $this->assertTrue(
            $this->oDummyEntity->reset()
        );

        # Assert that non existent attribute was correctly unsetted
        $this->assertFalse(
            isset($this->oDummyEntity->toto)
        );

        # After that the Entity instance was not loaded anymore
        $this->assertFalse(
            $this->oDummyEntity->isLoaded()
        );

    }

    public function testComputeEntityClassName()
    {
        $this->assertEquals(
            'Library\Core\Tests\Dummy\Entities\Collection\DummyCollection',
            $this->oDummyEntity->computeCollectionClassName()
        );
    }

    public function testIsInCache()
    {
        $oDummy = new Dummy(self::$iCreatedDummyId);
        $this->assertTrue($oDummy->isInCache());
    }

    public function testChildClass()
    {
        $this->assertEquals(
            get_class($this->oDummyEntity),
            $this->oDummyEntity->getChildClass()
        );
    }

    public function testGetCached()
    {
        $oDummy = new Dummy(self::$iCreatedDummyId);

        # Assert that the cached object data is identical to the source Entity
        $aCached = $this->oDummyEntity->getCached($oDummy->getId());

        foreach ($oDummy->getAttributes() as $sAttribute) {
            $this->assertEquals(
                $oDummy->$sAttribute,
                $aCached[$sAttribute]
            );
        }
    }

    public function testGetPrimaryKeyName()
    {
        $this->assertEquals(
            'iddummy',
            $this->oDummyEntity->getPrimaryKeyName()
        );
    }

    public function testGetTableName()
    {
        $this->assertEquals(
            'dummy',
            $this->oDummyEntity->getTableName()
        );
    }

}