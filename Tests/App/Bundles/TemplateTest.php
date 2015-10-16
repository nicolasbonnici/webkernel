<?php
namespace Core\Tests\App\Bundles;

use Library\Core\App\Bundles\Bundle;
use Library\Core\App\Bundles\Template;
use \Library\Core\Test as Test;

/**
 * Widget component unit tests
 *
 * @author Nicolas Bonnici <nicolasbonnici@gmail.com>
 */
class TemplateTest extends Test
{

    const TEST_BUNDLE = 'sample';

    /**
     * @var Template
     */
    private $oTemplateInstance;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
    }

    public function setUp()
    {
        $this->oTemplateInstance = new Template(new Bundle(self::TEST_BUNDLE));
    }

    public function tearDown()
    {
    }

    public function testConstructor()
    {
        $this->assertTrue(
            $this->oTemplateInstance instanceof Template,
            'Unable to load default template for bundle "' . self::TEST_BUNDLE . '", this bundle is required for test purposes.'
        );
    }

    /**
     * Test all accessors must have a setted value a this level
     */
    public function testInstanceGettersReturnNotEmptyValue()
    {
        $aAccessors = $this->getAccessors($this->oTemplateInstance);
        foreach ($aAccessors[self::ACCESSORS_GETTER] as $sAccessorMethod) {
            $this->assertNotEmpty(
                $this->oTemplateInstance->$sAccessorMethod(),
                sprintf('Template Accessor "%s" return empty value', $sAccessorMethod)
            );
        }

    }

}