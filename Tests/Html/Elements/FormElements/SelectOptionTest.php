<?php
namespace Library\Core\Tests\Html\Elements;

use Library\Core\Tests\Test;
use Library\Core\Html\Elements\FormElements\SelectOption;

/**
 * Form element InputNumber component unit tests
 * 
 * @author Nicolas Bonnici <nicolasbonnici@gmail.com>
 */
class SelectOptionTest extends Test
{

protected static $oSelectOptionInstance;

    const INPUT_TAG    = 'option';
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
    	self::$oSelectOptionInstance = new SelectOption(array());
        $this->assertTrue(self::$oSelectOptionInstance instanceof SelectOption);
    }

    public function testToString()
    {
        $this->assertEquals(self::$oSelectOptionInstance->__toString(), self::$oSelectOptionInstance->render());
    }

    public function testSetValue()
    {
        $this->assertTrue(self::$oSelectOptionInstance->setValue(self::TEST_STRING_VALUE) instanceof SelectOption);
    }

    public function testGetValue()
    {
        $this->assertEquals(self::$oSelectOptionInstance->getValue(), self::TEST_STRING_VALUE);
    }

    public function testSetAttributes()
    {
        $this->assertTrue(self::$oSelectOptionInstance->setAttributes($this->aTestDataArray) instanceof SelectOption);
    }

    public function testSetAttribute()
    {
        // Also test if the setAttribute() overload properly the setAttributes()
        $this->assertTrue(
            self::$oSelectOptionInstance->setAttribute(self::TEST_STRING_KEY, self::TEST_STRING_VALUE) instanceof SelectOption
        );
    }

    public function testGetAttribute()
    {
        // Assert that the generic accessors work properly
        foreach ($this->aTestDataArray as $sKey=>$mValue) {
            $this->assertEquals(self::$oSelectOptionInstance->getAttribute($sKey), $mValue);
        }
        $this->assertEquals(self::$oSelectOptionInstance->getAttribute(self::TEST_STRING_KEY), self::TEST_STRING_VALUE);
    }

    public function testGetAttributes()
    {
        $this->assertEquals(
            self::$oSelectOptionInstance->getAttributes(),
            array_merge($this->aTestDataArray, array(self::TEST_STRING_KEY=>self::TEST_STRING_VALUE))
        );
    }

    public function testRender()
    {
        $this->assertTrue(is_string(self::$oSelectOptionInstance->render()));
        $this->assertNotEmpty(strstr(self::$oSelectOptionInstance->render(), self::INPUT_TAG));
    }

}