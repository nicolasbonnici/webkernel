<?php
namespace Library\Core\Tests\Html\Elements;

use Library\Core\Html\Elements\FormElements\InputNumber;
use \Library\Core\Test as Test;

/**
 * Form element InputNumber component unit tests
 * 
 * @author Nicolas Bonnici <nicolasbonnici@gmail.com>
 */
class InputNumberTest extends Test
{

        protected static $oInputNumberInstance;

    const INPUT_TAG    = 'input';
    const INPUT_TYPE   = 'number';
    const INPUT_DEFAULT_VALUE = 0;
    const INPUT_TEST_VALUE = 10;
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
    	self::$oInputNumberInstance = new InputNumber(array());
        $this->assertTrue(self::$oInputNumberInstance instanceof InputNumber);
        $this->assertEquals(self::$oInputNumberInstance->getAttribute('type'), self::INPUT_TYPE);
        $this->assertEquals(self::$oInputNumberInstance->getAttribute('value'), self::INPUT_DEFAULT_VALUE);
    }

    public function testToString()
    {
        $this->assertEquals(self::$oInputNumberInstance->__toString(), self::$oInputNumberInstance->render());
    }

    public function testSetValue()
    {
        $this->assertTrue(self::$oInputNumberInstance->setValue(self::INPUT_TEST_VALUE) instanceof InputNumber);
    }

    public function testGetValue()
    {
        $this->assertEquals(self::$oInputNumberInstance->getValue(), self::INPUT_TEST_VALUE);
    }

    public function testSetAttributes()
    {
        $this->assertTrue(self::$oInputNumberInstance->setAttributes($this->aTestDataArray) instanceof InputNumber);
    }

    public function testSetAttribute()
    {
        // Also test if the setAttribute() overload properly the setAttributes()
        $this->assertTrue(
            self::$oInputNumberInstance->setAttribute(self::TEST_STRING_KEY, self::TEST_STRING_VALUE) instanceof InputNumber
        );
    }

    public function testGetAttribute()
    {
        // Assert that the generic accessors work properly
        foreach ($this->aTestDataArray as $sKey=>$mValue) {
            $this->assertEquals(self::$oInputNumberInstance->getAttribute($sKey), $mValue);
        }
        $this->assertEquals(self::$oInputNumberInstance->getAttribute(self::TEST_STRING_KEY), self::TEST_STRING_VALUE);
    }

    public function testGetAttributes()
    {
        $this->assertEquals(
            self::$oInputNumberInstance->getAttributes(),
            array_merge($this->aTestDataArray, array(self::TEST_STRING_KEY=>self::TEST_STRING_VALUE))
        );
    }

    public function testRender()
    {
        $this->assertTrue(is_string(self::$oInputNumberInstance->render()));
        $this->assertNotEmpty(strstr(self::$oInputNumberInstance->render(), self::INPUT_TAG));
    }

}