<?php
namespace Core\Tests\App\Bundles;

use Library\Core\Tests\Test;
use Library\Core\App\Bundles\Bundle;
use Library\Core\App\Bundles\Template;

/**
 * Widget component unit tests
 *
 * @author Nicolas Bonnici <nicolasbonnici@gmail.com>
 */
class BundleTest extends Test
{

    const TEST_BUNDLE = 'sample';

    /**
     * @var Bundle
     */
    private $oSampleBundleInstance;


    public function setUp()
    {
        self::loadUser(true);
        $this->oSampleBundleInstance = new Bundle(self::TEST_BUNDLE, self::$oUser);
    }

    public function testConstructor()
    {
        $this->assertTrue(
            $this->oSampleBundleInstance instanceof Bundle,
            'Unable to find "' . self::TEST_BUNDLE . '" bundle, this bundle is required for test purposes.'
        );

        $this->assertTrue(
            $this->oSampleBundleInstance->isLoaded()
        );
    }

    public function testGetBundleName()
    {
        $this->assertEquals(
            self::TEST_BUNDLE,
            $this->oSampleBundleInstance->getName()
        );
    }

    public function testGetBundleDisplayName()
    {
        $this->assertEquals(
            Bundle::TRANSLATION_KEY_BUNDLE_NAME,
            $this->oSampleBundleInstance->getDisplayName()
        );
    }

    public function testGetBundleDescription()
    {
        $this->assertEquals(
            Bundle::TRANSLATION_KEY_BUNDLE_DESCRIPTION,
            $this->oSampleBundleInstance->getDescription()
        );
    }

    public function testGetBundleAuthor()
    {
        $this->assertEquals(
            null,
            $this->oSampleBundleInstance->getAuthor()
        );
    }

    public function testGetBundleSupportInfo()
    {
        $this->assertEquals(
            null,
            $this->oSampleBundleInstance->getSupportInformation()
        );
    }

    public function testGetBundleVendorName()
    {
        $this->assertEquals(
            null,
            $this->oSampleBundleInstance->getVendorName()
        );
    }

    public function testGetBundleRepository()
    {
        $this->assertEquals(
            null,
            $this->oSampleBundleInstance->getRepository()
        );
    }

    public function testGetBundleUrl()
    {
        $this->assertEquals(
            null,
            $this->oSampleBundleInstance->getProjectUrl()
        );
    }

    public function testGetTemplatePath()
    {
        $this->assertNotEmpty(
            $this->oSampleBundleInstance->getTemplatePath()
        );
    }

    public function  testGetBundleTemplate()
    {
        $this->assertTrue(
            $this->oSampleBundleInstance->getTemplate() instanceof Template
        );
    }

}