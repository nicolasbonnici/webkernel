<?php
namespace Library\Core\Tests\Entity;

use \Library\Core\Test;
use Library\Core\Entity\Generator;
use Library\Core\Tests\Dummy\Entities\Dummy;


/**
 * Generator component unit tests
 * 
 * @author Nicolas Bonnici <nicolasbonnici@gmail.com>
 */
class GeneratorTest extends Test
{
    /**
     * @var Generator
     */
    private $oEntityGeneratorInstance;

    protected function setUp()
    {
        $this->oEntityGeneratorInstance = new Generator();
    }

    public function testConstructor()
    {
        $this->assertTrue(
            $this->oEntityGeneratorInstance instanceof Generator
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