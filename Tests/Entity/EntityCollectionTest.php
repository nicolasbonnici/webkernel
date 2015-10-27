<?php
namespace Library\Core\Tests\Entity;

use Library\Core\Database\Pdo;
use Library\Core\Entity\EntityCollection;
use Library\Core\Entity\Generator;
use Library\Core\Test;
use Library\Core\Tests\Dummy\Entities\Collection\DummyCollection;
use Library\Core\Tests\Dummy\Entities\Dummy;

/**
 * ORM EntityCollection component unit tests
 * 
 * @author Nicolas Bonnici <nicolasbonnici@gmail.com>
 */
class EntityCollectionTest extends Test
{
    /**
     * @var EntityCollection
     */
    protected $oDummyEntityCollection;

    protected function setUp()
    {
        $this->oDummyEntityCollection = new DummyCollection();
    }

    public function testConstructor()
    {
        $this->assertTrue($this->oDummyEntityCollection instanceof EntityCollection);
    }

    public function testLoad()
    {
        # Count total row first
        $iInitialDummiesCount = Pdo::dbQuery('SELECT COUNT(1) FROM `dummy`')->fetchColumn();

        if ($iInitialDummiesCount === 0) {
            $oGenerator = new Generator();
            $iInitialDummiesCount = 100;
            $oGenerator->process(new Dummy(), $iInitialDummiesCount);
        }

        $this->oDummyEntityCollection->load();

        $this->assertEquals(
            $iInitialDummiesCount,
            $this->oDummyEntityCollection->count()
        );
    }

    public function testConstructorWithArray()
    {
        $aIds = array();
        $this->oDummyEntityCollection->load();

        $iOriginalCount = $this->oDummyEntityCollection->count();

        foreach ($this->oDummyEntityCollection as $oDummy) {
            $aIds[] = $oDummy->getId();
        }

        $this->oDummyEntityCollection = new DummyCollection($aIds);

        $this->assertEquals(
            $iOriginalCount,
            $this->oDummyEntityCollection->count()
        );
    }

    public function loadWithOrderAndLimit()
    {
        $aOrders = array(
            'created' => 'DESC'
        );
        $iLimit = 10;

        $this->oDummyEntityCollection->load($aOrders, $iLimit);

        $this->assertEquals(
            $iLimit,
            $this->oDummyEntityCollection->count()
        );

    }

    public function testLoadWithParameters()
    {
        $aOrders = array(
            'created'
        );
        $iLimit = 18;

        $aParameters = array(
            'iddummy' => array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20)
        );

        $this->oDummyEntityCollection->loadByParameters($aParameters, $aOrders, $iLimit);

        $this->assertEquals(
            $iLimit,
            $this->oDummyEntityCollection->count()
        );
    }

    public function testLoadByQuery()
    {
        $aParameters = array(
            'iddummy' => array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20)
        );

        $aOrders = array(
            'created'
        );
        $iLimit = 18;


        $this->oDummyEntityCollection->loadByQuery(
            'SELECT * FROM `dummy` WHERE `iddummy` IN(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?) ORDER BY `created` DESC LIMIT 18',
            array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20)
        );

        $oDummyEntityCollection = new DummyCollection();
        $oDummyEntityCollection->loadByParameters($aParameters, $aOrders, $iLimit);

        $this->assertEquals(
            $oDummyEntityCollection,
            $this->oDummyEntityCollection
        );
    }

    public function testSearch()
    {
        $aOrders = array(
            'created'
        );
        $iLimit = 18;

        $aParameters = array(
            'iddummy' => array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20)
        );

        $this->oDummyEntityCollection->loadByParameters($aParameters, $aOrders, $iLimit);

        $oFound = $this->oDummyEntityCollection->search('iddummy', 5);
        $this->assertInstanceOf(
            get_class(new Dummy()),
            $oFound
        );

        $this->assertTrue(
            $oFound->isLoaded()
        );
    }

    public function testFilter()
    {
        $aOrders = array(
            'created'
        );
        $iLimit = 18;

        $aParameters = array(
            'iddummy' => array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20)
        );

        $this->oDummyEntityCollection->loadByParameters($aParameters, $aOrders, $iLimit);

        $this->oDummyEntityCollection->filter(array('iddummy' => 5));

        $this->assertEquals(
            1,
            $this->oDummyEntityCollection->count()
        );
    }

    public function testComputeEntityClassName()
    {
        $this->assertEquals(
            get_class(new Dummy()),
            $this->oDummyEntityCollection->computeEntityClassName()
        );
    }

    public function testGetChildClass()
    {
        $this->assertEquals(
            get_class(new Dummy()),
            $this->oDummyEntityCollection->getChildClass()
        );
    }

}