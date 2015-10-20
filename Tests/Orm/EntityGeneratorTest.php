<?php
namespace Library\Core\Tests\Orm;

use \Library\Core\Test;
use Library\Core\Orm\EntityGenerator;
use Library\Core\Tests\Dummy\Entities\Dummy;


/**
 * EntityGenerator component unit tests
 * 
 * @author Nicolas Bonnici <nicolasbonnici@gmail.com>
 */
class EntityGeneratorTest extends Test
{
    /**
     * @var EntityGenerator
     */
    private $oEntityGeneratorInstance;

    protected function setUp()
    {
        $this->oEntityGeneratorInstance = new EntityGenerator();
    }

    public function testConstructor()
    {
        $this->assertTrue(
            $this->oEntityGeneratorInstance instanceof EntityGenerator
        );
    }

    public function testGenerateThousandDummyEntities()
    {
        $this->assertTrue(
            $this->oEntityGeneratorInstance->process(new Dummy(), 100),
            'Unable to generate a thousand Dummy entities.'
        );

        $aDummyEntities = $this->oEntityGeneratorInstance->getGeneratedEntities();

        $this->assertTrue(
            is_array($aDummyEntities)
        );

        $this->assertEquals(
            100,
            count($aDummyEntities)
        );
    }
}