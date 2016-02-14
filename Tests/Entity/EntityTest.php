<?php
namespace Library\Core\Tests\Entity;

use app\Entities\User;
use bundles\auth\Models\AuthModel;
use Library\Core\Database\Query\Insert;
use Library\Core\Entity\Entity;
use Library\Core\Test;
use Library\Core\Tests\Dummy\Entities\Dummy;
use Library\Core\Tests\Dummy\Entities\Dummy4;

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
        self::loadUser(true);
        $this->oDummyEntity = new Dummy(null, 'FR_fr');
        $this->oDummyEntity->setUser(self::$oUser);
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

        $this->assertTrue($this->oDummyEntity->create());

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

        # Set User for ACL check
        $this->oDummyEntity->setUser(self::$oUser);

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

    public function testStoreMappedThenLoadMapped()
    {

        $this->oDummyEntity->test_string        = $this->aTestData['test_string'];
        $this->oDummyEntity->test_int           = $this->aTestData['test_int'];
        $this->oDummyEntity->test_float         = $this->aTestData['test_float'];
        $this->oDummyEntity->test_null          = $this->aTestData['test_null'];
        $this->oDummyEntity->lastupdate         = $this->aTestData['lastupdate'];
        $this->oDummyEntity->created            = $this->aTestData['created'];

        $this->assertTrue($this->oDummyEntity->create());

        # Create a Dummy4
        $oDummy4 = new Dummy4();
        $oDummy4->foo = 'Test string value';

        $oDummy4->setUser(self::$oUser);
        $this->assertTrue(
            $this->oDummyEntity->storeMapped($oDummy4),
            'Unable to store mapped Entity directly from Entity Instance'
        );

        $oDummy4 = $this->oDummyEntity->loadMapped($oDummy4);

        $this->assertInstanceOf(
            get_class(new Dummy4()),
            $oDummy4,
            'Unable to load mapped Entity in EntityTest.'
        );

        $this->assertEquals(
            'Test string value',
            $oDummy4->foo,
            'Incorrect value found on mapped Dummy4 in EntityTest.'
        );
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

    public function testComputeForeignKeyName()
    {
        $this->assertEquals(
            'dummy_iddummy',
            $this->oDummyEntity->computeForeignKeyName(),
            'Error on computed Entity foreign key name'
        );
    }

    public function testSetThenGetTranslation()
    {

        $this->oDummyEntity = new Dummy((int) self::$iCreatedDummyId, 'FR_fr');

        $aTr = array(
            array(
                'key' => 'test_string',
                'value' => 'Foo translated!'
            ),
            array(
                'key' => 'test_int',
                'value' => 'Foo 1 translated!'
            ),
            array(
                'key' => 'test_float',
                'value' => 'Foo 2 translated!'
            )
        );

        $this->oDummyEntity->setUser(self::$oUser);

        $aTrs = array();
        foreach ($aTr as $aTranslation) {
            $aTrs[$aTranslation['key']] = $aTranslation['value'];
            $this->assertTrue(
                $this->oDummyEntity->setTranslation($aTranslation['key'], $aTranslation['value']),
                'Unable to add a "' . $aTranslation['key'] . '" with value "' . $aTranslation['value'] . '" translation'
            );
        }

        # Refresh current instance to retrieve setted translations
        $this->oDummyEntity = new Dummy((int) self::$iCreatedDummyId, 'FR_fr');
        foreach ($this->oDummyEntity->getTranslatedAttributes() as $sKey) {
            $this->assertEquals(
                $aTrs[$sKey],
                $this->oDummyEntity->$sKey,
                'Wrong or empty translation found for field ' . $sKey
            );
        }
    }

}