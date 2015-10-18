<?php
namespace Library\Core\Tests\Html;

use Library\Core\Html\Node;
use \Library\Core\Test as Test;
use Library\Core\Tests\Dummy\Html\ElementMock;
use Library\Core\Tests\Dummy\Widgets\vendorname\WidgetName\WidgetNameWidget;


/**
 * Html Node component unit tests
 * 
 * @author Nicolas Bonnici <nicolasbonnici@gmail.com>
 */
class NodeTest extends Test
{

    /**
     * @var Node
     */
    protected $oHtmlNodeInstance;


    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
    }

    public function setUp()
    {
        $this->oHtmlNodeInstance = new Node();
    }

    public function tearDown()
    {
    }

    public function testConstructor()
    {
        $this->assertTrue($this->oHtmlNodeInstance instanceof Node);
    }

    public function testAddElement()
    {
        $oElementMock = new ElementMock();
        $this->assertTrue(
            $this->oHtmlNodeInstance->addElement($oElementMock) instanceof Node
        );

        $this->assertEquals(
            "<tag></tag>",
            $this->oHtmlNodeInstance->render()
        );
    }

    public function testAddElements()
    {
        $oElementMock = new ElementMock();
        for ($i = 0; $i < 10; $i++) {
            $this->assertTrue(
                $this->oHtmlNodeInstance->addElement($oElementMock) instanceof Node
            );
        }

        $this->assertEquals(
            "<tag></tag><tag></tag><tag></tag><tag></tag><tag></tag><tag></tag><tag></tag><tag></tag><tag></tag><tag></tag>",
            $this->oHtmlNodeInstance->render()
        );
    }
}