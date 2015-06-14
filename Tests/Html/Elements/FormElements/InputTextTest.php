<?php
namespace Core\Tests\Html\Elements;

use Core\Html\Elements\FormElements\InputText;
use \Core\Test as Test;

/**
 * Form element InputText component unit tests
 * 
 * @author Nicolas Bonnici <nicolasbonnici@gmail.com>
 */
class InputTextTest extends Test
{

    protected static $oInputTextInstance;

    const INPUT_TAG    = 'input';
    const INPUT_TYPE   = 'text';
    const INPUT_DEFAULT_VALUE = '';
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
    	self::$oInputTextInstance = new InputText(array());
        $this->assertTrue(self::$oInputTextInstance instanceof InputText);
        $this->assertEquals(self::$oInputTextInstance->getAttribute('type'), self::INPUT_TYPE);
        $this->assertEquals(self::$oInputTextInstance->getAttribute('value'), self::INPUT_DEFAULT_VALUE);
    }

    public function testToString()
    {
        $this->assertEquals(self::$oInputTextInstance->__toString(), self::$oInputTextInstance->render());
    }

    public function testSetValue()
    {
        $this->assertTrue(self::$oInputTextInstance->setValue(self::TEST_STRING_VALUE) instanceof InputText);
    }

    public function testGetValue()
    {
        $this->assertEquals(self::$oInputTextInstance->getValue(), self::TEST_STRING_VALUE);
    }

    public function testSetAttributes()
    {
        $this->assertTrue(self::$oInputTextInstance->setAttributes($this->aTestDataArray) instanceof InputText);
    }

    public function testSetAttribute()
    {
        // Also test if the setAttribute() overload properly the setAttributes()
        $this->assertTrue(
            self::$oInputTextInstance->setAttribute(self::TEST_STRING_KEY, self::TEST_STRING_VALUE) instanceof InputText
        );
    }

    public function testGetAttribute()
    {
        // Assert that the generic accessors work properly
        foreach ($this->aTestDataArray as $sKey=>$mValue) {
            $this->assertEquals(self::$oInputTextInstance->getAttribute($sKey), $mValue);
        }
        $this->assertEquals(self::$oInputTextInstance->getAttribute(self::TEST_STRING_KEY), self::TEST_STRING_VALUE);
    }

    public function testGetAttributes()
    {
        $this->assertEquals(
            self::$oInputTextInstance->getAttributes(),
            array_merge($this->aTestDataArray, array(self::TEST_STRING_KEY=>self::TEST_STRING_VALUE))
        );
    }

    public function testRender()
    {
        $this->assertTrue(is_string(self::$oInputTextInstance->render()));
        $this->assertNotEmpty(strstr(self::$oInputTextInstance->render(), self::INPUT_TAG));
    }

}