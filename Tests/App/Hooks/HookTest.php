<?php
namespace Library\Core\Tests\App\Hooks;


use Library\Core\App\Hooks\Hook;
use Library\Core\Test;
use Library\Core\Tests\Dummy\Widgets\vendorname\WidgetName\WidgetNameWidget;

class HookTest extends Test
{

    /**
     * @var Hook
     */
    private $oHookInstance;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
    }

    public function setUp()
    {
        $this->oHookInstance = new Hook();
    }

    public function tearDown()
    {
    }

    public function testConstructor()
    {
        $this->assertTrue($this->oHookInstance instanceof Hook);
    }

    public function testAddHook()
    {
        $this->assertTrue(
            $this->oHookInstance->add('foo') instanceof Hook
        );

        $this->assertEquals(
            array(),
            $this->oHookInstance->getHookWidgets('foo')
        );
    }

    public function testAddWidgetToHook()
    {
        $this->assertTrue(
            $this->oHookInstance->add('foo') instanceof Hook
        );

        $oDummyWidget = new WidgetNameWidget();
        $this->assertTrue(
            $this->oHookInstance->registerWidget('foo', $oDummyWidget) instanceof Hook
        );

        $this->assertEquals(
            array($oDummyWidget),
            $this->oHookInstance->getHookWidgets('foo')
        );
    }

    public function testAddWidgetToNonExistentHookRegisterItAnyway()
    {
        $oDummyWidget = new WidgetNameWidget();
        $this->assertTrue(
            $this->oHookInstance->registerWidget('nonExistentHook', $oDummyWidget) instanceof Hook
        );

        $this->assertEquals(
            array($oDummyWidget),
            $this->oHookInstance->getHookWidgets('nonExistentHook')
        );
    }

    public function testGetHooks()
    {
        $this->assertTrue(
            $this->oHookInstance->add('foo') instanceof Hook
        );
        $this->assertTrue(
            $this->oHookInstance->add('bar') instanceof Hook
        );
        $this->assertTrue(
            $this->oHookInstance->add('fugazzi') instanceof Hook
        );

        $this->assertEquals(
            array('foo', 'bar', 'fugazzi'),
            $this->oHookInstance->getHooksName()
        );
    }

}