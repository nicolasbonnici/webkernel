<?php
namespace Core\Tests\App\Widgets;

use Library\Core\App\Widgets\Widget;
use Library\Core\Tests\Test;

/**
 * Widget component unit tests
 *
 * @author Nicolas Bonnici <nicolasbonnici@gmail.com>
 */
class WidgetTest extends Test
{
    /**
     * @var Widget
     */
    private $oWidgetInstance;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
    }

    public function setUp()
    {
        $this->oWidgetInstance = new Widget();
    }

    public function tearDown()
    {
    }

    public function testRender()
    {
        $this->assertNotFalse(
            strstr($this->oWidgetInstance->render(), '<div class="ui-widget"'),
            'Invalid rendered widget markup'
        );
    }


    public function testRenderWithUpdatedMarkup()
    {
        # Overwrite widget header class
        $this->oWidgetInstance->build()->getHeader()->setAttribute('class', 'test-123-ok');

        $this->assertEquals(
            '<div class="ui-widget" data-snap-ignore="true"><div class="ui-widget-data"><div class="ui-widget-header test-123-ok"></div><div class="ui-widget-content"></div><div class="ui-widget-footer"></div></div></div>',
            $this->oWidgetInstance->render(),
            'Invalid rendered widget markup'
        );
    }

    public function testRenderWithLoadableParameter()
    {
        $this->oWidgetInstance->setLoadable(true)
                ->setUrl('/foo/ok');

        $this->assertNotFalse(
            strstr($this->oWidgetInstance->render(), Widget::LOADABLE_CLASS),
            'Invalid rendered widget markup for loadable widget'
        );
    }

    public function testRenderWithScrollLoadableParameter()
    {
        $this->oWidgetInstance->setScrollLoadable(true)
                ->setUrl('/foo/ok');

        $this->assertNotFalse(
            strstr($this->oWidgetInstance->render(), Widget::SCROLL_LOADABLE_CLASS),
            'Invalid rendered widget markup for scroll loadable with loadable widget'
        );
    }

    public function testRenderWithLoadableAndScrollLoadableParameter()
    {
        $this->oWidgetInstance->setLoadable(true)->setScrollLoadable(true)
                ->setUrl('/foo/ok');

        $this->assertNotFalse(
            strstr($this->oWidgetInstance->render(), Widget::LOADABLE_CLASS),
            'Invalid rendered widget markup for loadable with scroll loadable widget'
        );
        $this->assertNotFalse(
            strstr($this->oWidgetInstance->render(), Widget::SCROLL_LOADABLE_CLASS),
            'Invalid rendered widget markup for scroll loadable with loadable widget'
        );
    }

    public function testWithToolbar()
    {
        $this->oWidgetInstance->setToolbar(true);

        $this->assertNotFalse(
            strstr($this->oWidgetInstance->render(), Widget::WIDGET_TOOLBAR_CLASS),
            'Invalid rendered widget markup for scroll loadable with loadable widget'
        );
    }

    public function testWithToolbarAndLoadableStructure()
    {
        $this->oWidgetInstance->setLoadable(true)
            ->setUrl('/foo/ok')
            ->setToolbar(true)->build();

        $this->assertNotFalse(
            strstr($this->oWidgetInstance->render(), Widget::WIDGET_TOOLBAR_CLASS),
            'Invalid rendered widget markup for loadable widget with toolbar'
        );

        $this->assertNotFalse(
            strstr($this->oWidgetInstance->render(), Widget::LOADABLE_CLASS),
            'Invalid rendered widget markup for loadable widget with toolbar'
        );
    }

    public function testToStringMethod()
    {
        $this->oWidgetInstance->setLoadable(true)
            ->setUrl('/foo/ok');

        $this->assertEquals(
            $this->oWidgetInstance->render(),
            $this->oWidgetInstance->__toString(),
            'Invalid rendered widget markup'
        );
    }

    public function testAddParameter()
    {
        $this->oWidgetInstance->addParameter('test', 'foo');
        $this->assertEquals(
            array('test' => 'foo'),
            $this->oWidgetInstance->getParameters()
        );
    }

    public function testSetParameters()
    {
        $aTest = array(
            'foo' => 666,
            'key_ok' => 'ok',
            'to' => 'zisfool',
        );

        $this->oWidgetInstance->addParameters($aTest);

        $this->assertEquals(
            $aTest,
            $this->oWidgetInstance->getParameters()
        );
    }
}