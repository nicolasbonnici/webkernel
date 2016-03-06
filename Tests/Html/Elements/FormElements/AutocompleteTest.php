<?php
namespace Library\Core\Tests\Html\Elements;

use Library\Core\Tests\Test;
use Library\Core\Html\Elements\FormElements\Autocomplete;

/**
 * Form element autocomplete component unit tests
 * 
 * @author Nicolas Bonnici <nicolasbonnici@gmail.com>
 */
class AutocompleteTest extends Test
{

    /**
     * @var Autocomplete
     */
    protected $oAutocompleteInstance;

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
    
    protected $aTestOptions = array(
        1    => 'Test 1',
        2    => 'Test 2',
        3    => 'Test 3',
        4    => 'Test 4'
    );

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
    }

    public function setUp()
    {
        $this->oAutocompleteInstance = new Autocomplete($this->aTestOptions, array());
    }

    public function tearDown()
    {
    }

    public function testConstructor()
    {
        $this->assertTrue($this->oAutocompleteInstance instanceof Autocomplete);
    }

    public function testToString()
    {
        $this->assertEquals($this->oAutocompleteInstance->__toString(), $this->oAutocompleteInstance->render());
    }

    public function testSetThenGetValue()
    {
        $this->assertTrue($this->oAutocompleteInstance->setValue(self::TEST_STRING_VALUE) instanceof Autocomplete);

        $this->assertEquals($this->oAutocompleteInstance->getValue(), self::TEST_STRING_VALUE);
    }

    public function testSetAndGetAttribute()
    {
        // Also test if the setAttribute() overload properly the setAttributes()
        $this->assertTrue(
            $this->oAutocompleteInstance->setAttribute(self::TEST_STRING_KEY, self::TEST_STRING_VALUE) instanceof Autocomplete
        );
        $this->assertEquals($this->oAutocompleteInstance->getAttribute(self::TEST_STRING_KEY), self::TEST_STRING_VALUE);
    }

    public function testSetAndGetAttributes()
    {
        $this->assertTrue($this->oAutocompleteInstance->setAttributes($this->aTestDataArray) instanceof Autocomplete);

        # Just the default autocomplete and Bootstrap3 form element class
        $aData = $this->aTestDataArray;
        $aData['class'] = array('form-control', 'ui-autocomplete', 'some-class', 'otherone', 'andsoon');

        $this->assertEquals(
            $aData,
            $this->oAutocompleteInstance->getAttributes(),
            'Error with setAttributes() or getAttributes() method'
        );

    }

    public function testRender()
    {
        $this->assertTrue(is_string($this->oAutocompleteInstance->render()));
        $this->assertNotEmpty(strstr($this->oAutocompleteInstance->render(), self::INPUT_TAG));
    }

}