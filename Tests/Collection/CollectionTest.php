<?php
namespace Library\Core\Tests\Collection;

use Library\Core\Collection\Collection;
use Library\Core\Tests\Test;

class CollectionTest extends Test
{

    protected $aTestDataArray = array(
        'prop1' => 1,
        'prop2' => 2,
        'prop3' => 3
    );

    /**
     * @var Collection
     */
    private $oCollectionInstance;

    protected function setUp()
    {
        $this->oCollectionInstance = new Collection();
    }

    public function testConstructor()
    {
        $this->assertInstanceOf(
            get_class(new Collection()),
            $this->oCollectionInstance,
            'Unable to instantiate Collection in CollectionTest Test'
        );
    }

    public function testAddThenGet()
    {
        $this->assertTrue(
            $this->oCollectionInstance->add('test value', 999),
            'Unable to add item to the Collection instance'
        );
        $this->assertEquals(
            'test value',
            $this->oCollectionInstance->get(999),
            'Unable to retrieve added collection item'
        );
    }

    public function testAddSeveralItemsThenCountCollection()
    {
        $this->assertTrue(
            $this->oCollectionInstance->addItems($this->aTestDataArray),
            'Unable to add several items to collection'
        );

        $this->assertEquals(
            $this->aTestDataArray['prop1'],
            $this->oCollectionInstance->get('prop1'),
            'Unable to retrieve element collection added with addItems() method'
        );

        $this->assertEquals(
            $this->aTestDataArray['prop2'],
            $this->oCollectionInstance->get('prop2'),
            'Unable to retrieve element collection added with addItems() method'
        );

        $this->assertEquals(
            $this->aTestDataArray['prop3'],
            $this->oCollectionInstance->get('prop3'),
            'Unable to retrieve element collection added with addItems() method'
        );

        $this->assertEquals(
            count($this->aTestDataArray),
            $this->oCollectionInstance->count(),
            'Wrong count after addItems() was called'
        );

    }

    public function testMergeCollections()
    {
        $oCollection = new Collection();
        $this->assertTrue(
            $this->oCollectionInstance->addItems($this->aTestDataArray),
            'Unable to add several items to collection'
        );
        $this->assertTrue(
            $oCollection->addItems($this->aTestDataArray),
            'Unable to add several items to collection'
        );

        $this->assertTrue(
            $this->oCollectionInstance->merge($oCollection)
        );

        $this->assertEquals(
            $this->aTestDataArray['prop1'],
            $this->oCollectionInstance->get('prop1'),
            'Unable to retrieve element collection after merge() was called'
        );

        $this->assertEquals(
            $this->aTestDataArray['prop2'],
            $this->oCollectionInstance->get('prop2'),
            'Unable to retrieve element collection after merge() was called'
        );

        $this->assertEquals(
            $this->aTestDataArray['prop3'],
            $this->oCollectionInstance->get('prop3'),
            'Unable to retrieve element collection after merge() was called'
        );

        $this->assertEquals(
            count($this->aTestDataArray),
            $this->oCollectionInstance->count(),
            'Wrong count after merge() was called'
        );

    }

    public function testMergeCollectionsWithoutIndexes()
    {
        $oCollection = new Collection();
        $this->assertTrue(
            $this->oCollectionInstance->addItems($this->aTestDataArray),
            'Unable to add several items to collection'
        );
        $this->assertTrue(
            $oCollection->addItems($this->aTestDataArray),
            'Unable to add several items to collection'
        );

        $this->assertTrue(
            $this->oCollectionInstance->merge($oCollection, true)
        );

        $this->assertEquals(
            $this->aTestDataArray['prop1'],
            $this->oCollectionInstance->get('prop1'),
            'Unable to retrieve element collection after merge() was called'
        );

        $this->assertEquals(
            $this->aTestDataArray['prop2'],
            $this->oCollectionInstance->get('prop2'),
            'Unable to retrieve element collection after merge() was called'
        );

        $this->assertEquals(
            $this->aTestDataArray['prop3'],
            $this->oCollectionInstance->get('prop3'),
            'Unable to retrieve element collection after merge() was called'
        );

        $this->assertEquals(
            count($this->aTestDataArray) * 2,
            $this->oCollectionInstance->count(),
            'Wrong count after merge() was called'
        );

    }

    public function testDeleteThenHasItemAndCountMethodsOnEmptyCollection()
    {
        $this->assertTrue(
            $this->oCollectionInstance->add('test value', 999),
            'Unable to add item to the Collection instance'
        );

        $this->assertTrue(
            $this->oCollectionInstance->delete(999),
            'Unable to delete Collection item'
        );

        $this->assertFalse(
            $this->oCollectionInstance->hasItem(),
            'Invalid result for hasItem() method after the last item was deleted'
        );

        $this->assertEquals(
            0,
            $this->oCollectionInstance->count(),
            'Wrong result for Collection::count()'
        );
    }

    public function testHasItemAndCountMethodsWithOneItemInCollection()
    {
        $this->assertTrue(
            $this->oCollectionInstance->add('test value', 999),
            'Unable to add item to the Collection instance'
        );

        $this->assertTrue(
            $this->oCollectionInstance->hasItem(),
            'Wrong result for hasItem'
        );

        $this->assertEquals(
            1,
            $this->oCollectionInstance->count(),
            'Wrong result for Collection::count()'
        );

        $this->oCollectionInstance->valid();
    }

    public function testSortThenIterateWithNextMethodThenRewind()
    {
        $this->assertTrue(
            $this->oCollectionInstance->addItems($this->aTestDataArray),
            'Unable to add several items to collection'
        );

        $this->assertTrue(
            $this->oCollectionInstance->sort(),
            'Unable to sort Collection'
        );

        $this->assertEquals(
            $this->aTestDataArray['prop1'],
            $this->oCollectionInstance->current()
        );

        $this->oCollectionInstance->next();
        $this->assertEquals(
            $this->aTestDataArray['prop2'],
            $this->oCollectionInstance->current()
        );

        $this->oCollectionInstance->next();
        $this->assertEquals(
            $this->aTestDataArray['prop3'],
            $this->oCollectionInstance->current()
        );

        $this->oCollectionInstance->rewind();

        $this->assertEquals(
            $this->aTestDataArray['prop1'],
            $this->oCollectionInstance->current()
        );
    }

    public function testSortByValueThenIterateWithNextMethodThenRewind()
    {
        $this->assertTrue(
            $this->oCollectionInstance->addItems($this->aTestDataArray),
            'Unable to add several items to collection'
        );

        $this->assertTrue(
            $this->oCollectionInstance->ksort(),
            'Unable to ksort Collection'
        );

        $this->assertEquals(
            $this->aTestDataArray['prop1'],
            $this->oCollectionInstance->current()
        );

        $this->oCollectionInstance->next();
        $this->assertEquals(
            $this->aTestDataArray['prop2'],
            $this->oCollectionInstance->current()
        );

        $this->oCollectionInstance->next();
        $this->assertEquals(
            $this->aTestDataArray['prop3'],
            $this->oCollectionInstance->current()
        );

        $this->oCollectionInstance->rewind();

        $this->assertEquals(
            $this->aTestDataArray['prop1'],
            $this->oCollectionInstance->current()
        );
    }

    public function testReverseSort()
    {
        $this->assertTrue(
            $this->oCollectionInstance->addItems($this->aTestDataArray),
            'Unable to add several items to collection'
        );

        $this->assertTrue(
            $this->oCollectionInstance->sort(true),
            'Unable to reverse sort Collection'
        );

        $this->assertEquals(
            $this->aTestDataArray['prop3'],
            $this->oCollectionInstance->current()
        );

    }

    public function testResetThenAssertCollectionNotHasItemAndIsEmpty()
    {
        $this->assertTrue(
            $this->oCollectionInstance->addItems($this->aTestDataArray),
            'Unable to add several items to collection'
        );

        $this->assertTrue(
            $this->oCollectionInstance->reset(),
            'Unable to reset Collection'
        );


        $this->assertFalse(
            $this->oCollectionInstance->hasItem(),
            'Invalid result for hasItem() method after the last item was deleted'
        );

        $this->assertEquals(
            0,
            $this->oCollectionInstance->count(),
            'Wrong result for Collection::count()'
        );

    }
}