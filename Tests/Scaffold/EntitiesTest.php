<?php
namespace Library\Core\Tests\Scaffold;

use Library\Core\Bootstrap;
use Library\Core\Entity\Mapping\MappingAbstract;
use Library\Core\FileSystem\File;
use Library\Core\Scaffold\Entities;
use Library\Core\Test;

class EntitiesTest extends Test
{

    /**
     * @var Entities
     */
    private $oEntitiesScaffolder;

    public function setUp()
    {
        $this->oEntitiesScaffolder = new Entities('dummy');
    }

    public function testProcess()
    {
        $this->assertTrue(
            $this->oEntitiesScaffolder->process(),
            'Unable to scaffold the Dummy Entity...'
        );

        # Check for the generated file
        $sScaffoldedEntityPath = Bootstrap::getRootPath() . 'app/Entities/Dummy.php';
        $this->assertTrue(
            File::exists($sScaffoldedEntityPath),
            'Unable to find scaffolded Entity class.'
        );

        # Try to use generated class
        $oScaffoldedEntity = new \app\Entities\Dummy();
        $this->assertInstanceOf(
            'app\Entities\Dummy',
            $oScaffoldedEntity
        );

    }

    public function testProcessWithParameters()
    {
        $this->assertInstanceOf(
            get_class($this->oEntitiesScaffolder),
            $this->oEntitiesScaffolder->setCacheDuration(100)
        );
        $this->assertInstanceOf(
            get_class($this->oEntitiesScaffolder),
            $this->oEntitiesScaffolder->setIsCacheable(true)
        );
        $this->assertInstanceOf(
            get_class($this->oEntitiesScaffolder),
            $this->oEntitiesScaffolder->setIsDeletable(true)
        );
        $this->assertInstanceOf(
            get_class($this->oEntitiesScaffolder),
            $this->oEntitiesScaffolder->setIsHistorized(true)
        );
        $this->assertInstanceOf(
            get_class($this->oEntitiesScaffolder),
            $this->oEntitiesScaffolder->setIsSearchable(true)
        );
        $this->assertInstanceOf(
            get_class($this->oEntitiesScaffolder),
            $this->oEntitiesScaffolder->setMappingConfiguration(array(
                    'Library\Core\Tests\Dummy\Entities\Dummy4' => array(
                        MappingAbstract::KEY_MAPPING_TYPE    => MappingAbstract::MAPPING_ONE_TO_ONE,
                        MappingAbstract::KEY_LOAD_BY_DEFAULT => false,
                        MappingAbstract::KEY_MAPPED_ENTITY_REFERENCE => 'dummy4_iddummy4'
                    ),
                    'Library\Core\Tests\Dummy\Entities\Dummy2' => array(
                        MappingAbstract::KEY_MAPPING_TYPE            => MappingAbstract::MAPPING_ONE_TO_MANY,
                        MappingAbstract::KEY_LOAD_BY_DEFAULT         => false,
                        MappingAbstract::KEY_SOURCE_ENTITY_REFERENCE => 'dummy_iddummy'
                    ),
                    'Library\Core\Tests\Dummy\Entities\Dummy3' => array(
                        MappingAbstract::KEY_MAPPING_TYPE 	         => MappingAbstract::MAPPING_MANY_TO_MANY,
                        MappingAbstract::KEY_LOAD_BY_DEFAULT         => false,
                        MappingAbstract::KEY_MAPPING_TABLE           => 'dummyDummy3',
                        MappingAbstract::KEY_SOURCE_ENTITY_REFERENCE => 'dummy_iddummy',
                        MappingAbstract::KEY_MAPPED_ENTITY_REFERENCE => 'dummy3_iddummy3'
                    )
                )
            )
        );

        $this->assertTrue(
            $this->oEntitiesScaffolder->process(),
            'Unable to scaffold the Dummy Entity...'
        );

        # Check for the generated file
        $sScaffoldedEntityPath = Bootstrap::getRootPath() . 'app/Entities/Dummy.php';
        $this->assertTrue(
            File::exists($sScaffoldedEntityPath),
            'Unable to find scaffolded Entity class.'
        );

        # Try to use generated class
        /** @var Entity $oScaffoldedEntity */
        $oScaffoldedEntity = new \app\Entities\Dummy();
        $this->assertInstanceOf(
            'app\Entities\Dummy',
            $oScaffoldedEntity
        );
    }

    /**
     * Clear scaffolded class
     */
    protected function tearDown()
    {
        $this->assertTrue(
            File::delete(Bootstrap::getRootPath() . 'app/Entities/Dummy.php'),
            'Unable to delete scaffolded Entities'
        );
        parent::tearDown();
    }
}