<?php
namespace Core\Tests\App\Widgets;

use Library\Core\App\Widgets\WidgetAbstract;
use \Library\Core\Test as Test;

use Library\Core\Tests\Dummy\Widgets\vendorname\WidgetName\WidgetNameWidget;

/**
 * Widget component unit tests
 *
 * @author Nicolas Bonnici <nicolasbonnici@gmail.com>
 */
class WidgetTest extends Test
{
    /**
     * @var WidgetNameWidget
     */
    private $oDummyWidgetInstance;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
    }

    public function setUp()
    {
        $this->oDummyWidgetInstance = new WidgetNameWidget();
    }

    public function tearDown()
    {
    }

    public function testConstructor()
    {
        $this->assertTrue($this->oDummyWidgetInstance instanceof WidgetNameWidget);

        $this->assertEquals(
            'vendorname',
            $this->oDummyWidgetInstance->getVendorName()
        );
        $this->assertEquals(
            'WidgetName',
            $this->oDummyWidgetInstance->getName()
        );
    }

    public function testRender()
    {
        $this->assertEquals(
            "<div>Hello world!</div>",
            $this->oDummyWidgetInstance->render()
        );
    }

    public function testAddParameter()
    {
        $this->oDummyWidgetInstance->addParameter('test', 'foo');
        $this->assertEquals(
            array('test' => 'foo'),
            $this->oDummyWidgetInstance->getParameters()
        );
    }

    public function testDefaultRenderMode()
    {
        $this->assertEquals(
            WidgetAbstract::DEFAULT_RENDER_MODE,
            $this->oDummyWidgetInstance->getRenderMode()
        );
    }

    public function testSetRenderModeWithInvalidParameter()
    {
        $this->assertFalse(
            $this->oDummyWidgetInstance->setRenderMode('NotAllowedMode')
        );
    }

    public function testSetRenderModeWithValidParameter()
    {
        $this->assertTrue(
            $this->oDummyWidgetInstance->setRenderMode(WidgetAbstract::RENDER_MODE_EDITON)
        );

        $this->assertEquals(
            WidgetAbstract::RENDER_MODE_EDITON,
            $this->oDummyWidgetInstance->getRenderMode()
        );
    }

    public function testAllAccessors()
    {
        $this->assertNotEmpty(
            $this->oDummyWidgetInstance->getName()
        );
        $this->assertNotEmpty(
            $this->oDummyWidgetInstance->getVendorName()
        );
        $this->assertNotEmpty(
            $this->oDummyWidgetInstance->getPath()
        );
        $this->assertNotEmpty(
            $this->oDummyWidgetInstance->getDisplayName()
        );
        $this->assertNotEmpty(
            $this->oDummyWidgetInstance->getDescription()
        );
        $this->assertNotEmpty(
            $this->oDummyWidgetInstance->getVersion()
        );
    }

    public function testSetParameters()
    {
        $aTest = array(
            'foo' => 666,
            'key_ok' => 'ok',
            'to' => 'zisfool',
        );

        $this->oDummyWidgetInstance->addParameters($aTest);

        $this->assertEquals(
            $aTest,
            $this->oDummyWidgetInstance->getParameters()
        );
    }
}