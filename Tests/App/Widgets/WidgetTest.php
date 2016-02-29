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
        $this->assertEquals(
            '<div class="ui-widget"><div class="ui-widget-data"><div class="ui-widget-header"></div><div class="ui-widget-content"></div><div class="ui-widget-footer"></div></div></div>',
            $this->oWidgetInstance->render(),
            'Invalid rendered widget markup'
        );
    }


    public function testRenderWithUpdatedMakup()
    {
        # Overwrite widget header class
        $this->oWidgetInstance->build()->getWidgetHeader()->setAttribute('class', 'test-123-ok');

        $this->assertEquals(
            '<div class="ui-widget"><div class="ui-widget-data"><div class="ui-widget-header test-123-ok"></div><div class="ui-widget-content"></div><div class="ui-widget-footer"></div></div></div>',
            $this->oWidgetInstance->render(),
            'Invalid rendered widget markup'
        );
    }

    public function testRenderWithLoadableParameter()
    {
        $this->oWidgetInstance->setLoadable(true)
                ->setUrl('/foo/ok');

        $this->assertEquals(
            '<div class="ui-widget"><div class="ui-widget-data ui-loadable" data-url="/foo/ok"></div></div>',
            $this->oWidgetInstance->render(),
            'Invalid rendered widget markup'
        );
    }

    public function testRenderWithScrollLoadableParameter()
    {
        $this->oWidgetInstance->setScrollLoadable(true)
                ->setUrl('/foo/ok');

        $this->assertEquals(
            '<div class="ui-widget"><div class="ui-widget-data ui-scroll-loadable" data-url="/foo/ok"></div></div>',
            $this->oWidgetInstance->render(),
            'Invalid rendered widget markup'
        );
    }

    public function testRenderWithLoadableScrollLoadableParameter()
    {
        $this->oWidgetInstance->setLoadable(true)->setScrollLoadable(true)
                ->setUrl('/foo/ok');

        $this->assertEquals(
            '<div class="ui-widget"><div class="ui-widget-data ui-loadable ui-scroll-loadable" data-url="/foo/ok"></div></div>',
            $this->oWidgetInstance->render(),
            'Invalid rendered widget markup'
        );
    }

    public function testToStringMethod()
    {
        $this->oWidgetInstance->setLoadable(true)
            ->setUrl('/foo/ok');

        $this->assertEquals(
            '<div class="ui-widget"><div class="ui-widget-data ui-loadable" data-url="/foo/ok"></div></div>',
            $this->oWidgetInstance,
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