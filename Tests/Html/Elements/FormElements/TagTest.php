<?php
namespace Library\Core\Tests\Html\Elements;

use Library\Core\Html\Elements\FormElements\Select;
use \Library\Core\Test as Test;

/**
 * Form element autocomplete component unit tests
 * 
 * @author Nicolas Bonnici <nicolasbonnici@gmail.com>
 */
class TagTest extends Test
{

protected static $oTagInstance;

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
    	self::$oTagInstance = new Textarea(array());
        $this->assertTrue(self::$oTagInstance instanceof Tag);
    }

    public function testToString()
    {
        $this->assertEquals(self::$oTagInstance->__toString(), self::$oTagInstance->render());
    }

    public function testSetValue()
    {
        $this->assertTrue(self::$oTagInstance->setValue(self::TEST_STRING_VALUE) instanceof Tag);
    }

    public function testGetValue()
    {
        $this->assertEquals(self::$oTagInstance->getValue(), self::TEST_STRING_VALUE);
    }

    public function testSetAttributes()
    {
        $this->assertTrue(self::$oTagInstance->setAttributes($this->aTestDataArray) instanceof Tag);
    }

    public function testSetAttribute()
    {
        // Also test if the setAttribute() overload properly the setAttributes()
        $this->assertTrue(
            self::$oTagInstance->setAttribute(self::TEST_STRING_KEY, self::TEST_STRING_VALUE) instanceof Tag
        );
    }

    public function testGetAttribute()
    {
        // Assert that the generic accessors work properly
        foreach ($this->aTestDataArray as $sKey=>$mValue) {
            $this->assertEquals(self::$oTagInstance->getAttribute($sKey), $mValue);
        }
        $this->assertEquals(self::$oTagInstance->getAttribute(self::TEST_STRING_KEY), self::TEST_STRING_VALUE);
    }

    public function testGetAttributes()
    {
        $this->assertEquals(
            self::$oTagInstance->getAttributes(),
            array_merge($this->aTestDataArray, array(self::TEST_STRING_KEY=>self::TEST_STRING_VALUE))
        );
    }

    public function testRender()
    {
        $this->assertTrue(is_string(self::$oTagInstance->render()));
        $this->assertNotEmpty(strstr(self::$oTagInstance->render(), self::INPUT_TAG));
    }

}