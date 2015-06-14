<?php
namespace Core\Tests\Html;

use Core\Html\Element;
use \Core\Test as Test;

use Core\Tests\Mocks\HtmlElementMock;

/**
 * Html Element component unit tests
 * 
 * @author Nicolas Bonnici <nicolasbonnici@gmail.com>
 */
class ElementTest extends Test
{

    protected static $oHtmlElementInstance;

    const TEST_STRING_KEY   = 'test';
    const TEST_STRING_VALUE = 'test-value';

    protected $aTestDataArray = array(
        'id'     => 'form-dom-node-id',
        'method' => 'post',
        'action' => '/some/url/',
        'multiple' => null,
        'class' => array('some-class', 'otherone', 'andsoon'),
        'data'  => array('key' => 'value', 'otherKey' => 'otherValue')
    );

    protected $sExpected = ' id="form-dom-node-id" method="post" action="/some/url/" multiple class="some-class otherone andsoon" data-key="value" data-otherKey="otherValue"';


    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
    }

    public function setUp()
    {
    }

    public function tearDown()
    {
    }

    public function testConstructor()
    {
        self::$oHtmlElementInstance = new HtmlElementMock();
        $this->assertTrue(self::$oHtmlElementInstance instanceof HtmlElementMock);
    }


    public function testSetAttributes()
    {
        $this->assertTrue(self::$oHtmlElementInstance->setAttributes($this->aTestDataArray) instanceof Element);
    }

    public function testSetAttribute()
    {
        // Also test if the setAttribute() overload properly the setAttributes()
        $this->assertTrue(
            self::$oHtmlElementInstance->setAttribute(self::TEST_STRING_KEY, self::TEST_STRING_VALUE) instanceof Element
        );
    }

    public function testGetAttribute()
    {
        // Assert that the generic accessors work properly
        foreach ($this->aTestDataArray as $sKey=>$mValue) {
            $this->assertEquals(self::$oHtmlElementInstance->getAttribute($sKey), $mValue);
        }
        $this->assertEquals(self::$oHtmlElementInstance->getAttribute(self::TEST_STRING_KEY), self::TEST_STRING_VALUE);
    }

    public function testGetAttributes()
    {
        $this->assertEquals(
            self::$oHtmlElementInstance->getAttributes(),
            array_merge($this->aTestDataArray, array(self::TEST_STRING_KEY=>self::TEST_STRING_VALUE))
        );
    }
}