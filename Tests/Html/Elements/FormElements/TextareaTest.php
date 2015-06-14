<?php
namespace Core\Tests\Html\Elements;

use Core\Html\Elements\FormElements\Textarea;
use \Core\Test as Test;

/**
 * Form element InputNumber component unit tests
 * 
 * @author Nicolas Bonnici <nicolasbonnici@gmail.com>
 */
class TextareaTest extends Test
{

    protected static $oTextareaInstance;

    const INPUT_TAG    = 'textarea';
    const TEST_STRING_KEY   = 'test';
    const TEST_STRING_VALUE = 'test-value';

    protected $aTestDataArray = array(
        'id'     => 'form-dom-node-id',
        'method' => 'post',
        'action' => '/some/url/',
        'value' => '',
        'type'  => '',
        'multiple' => null,
        'class' => array('some-class', 'otherone', 'andsoon'),
        'data'  => array('key' => 'value', 'otherKey' => 'otherValue')
    );

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
    	self::$oTextareaInstance = new Textarea(array());
        $this->assertTrue(self::$oTextareaInstance instanceof Textarea);
    }

    public function testToString()
    {
        $this->assertEquals(self::$oTextareaInstance->__toString(), self::$oTextareaInstance->render());
    }

    public function testSetValue()
    {
        $this->assertTrue(self::$oTextareaInstance->setValue(self::TEST_STRING_VALUE) instanceof Textarea);
    }

    public function testGetValue()
    {
        $this->assertEquals(self::$oTextareaInstance->getValue(), self::TEST_STRING_VALUE);
    }

    public function testSetAttributes()
    {
        $this->assertTrue(self::$oTextareaInstance->setAttributes($this->aTestDataArray) instanceof Textarea);
    }

    public function testSetAttribute()
    {
        // Also test if the setAttribute() overload properly the setAttributes()
        $this->assertTrue(
            self::$oTextareaInstance->setAttribute(self::TEST_STRING_KEY, self::TEST_STRING_VALUE) instanceof Textarea
        );
    }

    public function testGetAttribute()
    {
        // Assert that the generic accessors work properly
        foreach ($this->aTestDataArray as $sKey=>$mValue) {
            $this->assertEquals(self::$oTextareaInstance->getAttribute($sKey), $mValue);
        }
        $this->assertEquals(self::$oTextareaInstance->getAttribute(self::TEST_STRING_KEY), self::TEST_STRING_VALUE);
    }

    public function testGetAttributes()
    {
        $this->assertEquals(
            self::$oTextareaInstance->getAttributes(),
            array_merge($this->aTestDataArray, array(self::TEST_STRING_KEY=>self::TEST_STRING_VALUE))
        );
    }

    public function testRender()
    {
        $this->assertTrue(is_string(self::$oTextareaInstance->render()));
        $this->assertNotEmpty(strstr(self::$oTextareaInstance->render(), self::INPUT_TAG));
    }

}