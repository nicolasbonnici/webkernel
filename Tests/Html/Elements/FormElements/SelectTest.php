<?php
namespace Library\Core\Tests\Html\Elements;

use Library\Core\Html\Elements\FormElements\Select;
use \Library\Core\Test as Test;

/**
 * Form element InputNumber component unit tests
 * 
 * @author Nicolas Bonnici <nicolasbonnici@gmail.com>
 */
class SelectTest extends Test
{

protected static $oSelectInstance;

    const INPUT_TAG    = 'select';
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
    	self::$oSelectInstance = new Textarea(array());
        $this->assertTrue(self::$oSelectInstance instanceof Select);
    }

    public function testToString()
    {
        $this->assertEquals(self::$oSelectInstance->__toString(), self::$oSelectInstance->render());
    }

    public function testSetValue()
    {
        $this->assertTrue(self::$oSelectInstance->setValue(self::TEST_STRING_VALUE) instanceof Select);
    }

    public function testGetValue()
    {
        $this->assertEquals(self::$oSelectInstance->getValue(), self::TEST_STRING_VALUE);
    }

    public function testSetAttributes()
    {
        $this->assertTrue(self::$oSelectInstance->setAttributes($this->aTestDataArray) instanceof Select);
    }

    public function testSetAttribute()
    {
        // Also test if the setAttribute() overload properly the setAttributes()
        $this->assertTrue(
            self::$oSelectInstance->setAttribute(self::TEST_STRING_KEY, self::TEST_STRING_VALUE) instanceof Select
        );
    }

    public function testGetAttribute()
    {
        // Assert that the generic accessors work properly
        foreach ($this->aTestDataArray as $sKey=>$mValue) {
            $this->assertEquals(self::$oSelectInstance->getAttribute($sKey), $mValue);
        }
        $this->assertEquals(self::$oSelectInstance->getAttribute(self::TEST_STRING_KEY), self::TEST_STRING_VALUE);
    }

    public function testGetAttributes()
    {
        $this->assertEquals(
            self::$oSelectInstance->getAttributes(),
            array_merge($this->aTestDataArray, array(self::TEST_STRING_KEY=>self::TEST_STRING_VALUE))
        );
    }

    public function testRender()
    {
        $this->assertTrue(is_string(self::$oSelectInstance->render()));
        $this->assertNotEmpty(strstr(self::$oSelectInstance->render(), self::INPUT_TAG));
    }

}