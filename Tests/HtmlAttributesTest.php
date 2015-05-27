<?php
namespace Library\Core\Tests;

use Library\Core\HtmlAttributes;
use \Library\Core\Test as Test;

/**
 * Html component unit tests
 * 
 * @author Nicolas Bonnici <nicolasbonnici@gmail.com>
 */
class HtmlAttributesTest extends Test
{

    protected static $oHtmlAttributesInstance;

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
    	self::$oHtmlAttributesInstance = new HtmlAttributes();
        $this->assertTrue(self::$oHtmlAttributesInstance instanceof HtmlAttributes);
    }

    public function testRender()
    {
        $this->assertTrue(is_string(self::$oHtmlAttributesInstance->render(array())));
        $this->assertEquals(self::$oHtmlAttributesInstance->render($this->aTestDataArray), $this->sExpected);
    }

}