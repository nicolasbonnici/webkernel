<?php
namespace Library\Core\Tests\Html\Elements;

use Library\Core\Html\Elements\FormElements\Select;
use \Library\Core\Test as Test;

/**
 * Form element autocomplete component unit tests
 * 
 * @author Nicolas Bonnici <nicolasbonnici@gmail.com>
 */
class AutocompleteTest extends Test
{

protected static $oAutocompleteInstance;

    const INPUT_TAG    = 'Autocomplete';
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
    	self::$oAutocompleteInstance = new Textarea(array());
        $this->assertTrue(self::$oAutocompleteInstance instanceof Autocomplete);
    }

    public function testToString()
    {
        $this->assertEquals(self::$oAutocompleteInstance->__toString(), self::$oAutocompleteInstance->render());
    }

    public function testSetValue()
    {
        $this->assertTrue(self::$oAutocompleteInstance->setValue(self::TEST_STRING_VALUE) instanceof Autocomplete);
    }

    public function testGetValue()
    {
        $this->assertEquals(self::$oAutocompleteInstance->getValue(), self::TEST_STRING_VALUE);
    }

    public function testSetAttributes()
    {
        $this->assertTrue(self::$oAutocompleteInstance->setAttributes($this->aTestDataArray) instanceof Autocomplete);
    }

    public function testSetAttribute()
    {
        // Also test if the setAttribute() overload properly the setAttributes()
        $this->assertTrue(
            self::$oAutocompleteInstance->setAttribute(self::TEST_STRING_KEY, self::TEST_STRING_VALUE) instanceof Autocomplete
        );
    }

    public function testGetAttribute()
    {
        // Assert that the generic accessors work properly
        foreach ($this->aTestDataArray as $sKey=>$mValue) {
            $this->assertEquals(self::$oAutocompleteInstance->getAttribute($sKey), $mValue);
        }
        $this->assertEquals(self::$oAutocompleteInstance->getAttribute(self::TEST_STRING_KEY), self::TEST_STRING_VALUE);
    }

    public function testGetAttributes()
    {
        $this->assertEquals(
            self::$oAutocompleteInstance->getAttributes(),
            array_merge($this->aTestDataArray, array(self::TEST_STRING_KEY=>self::TEST_STRING_VALUE))
        );
    }

    public function testRender()
    {
        $this->assertTrue(is_string(self::$oAutocompleteInstance->render()));
        $this->assertNotEmpty(strstr(self::$oAutocompleteInstance->render(), self::INPUT_TAG));
    }

}