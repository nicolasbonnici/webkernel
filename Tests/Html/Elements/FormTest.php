<?php
namespace Library\Core\Tests\Html\Elements;

use Library\Core\Html\Elements\Form;
use Library\Core\Html\Elements\FormElements\Autocomplete;
use Library\Core\Html\Elements\FormElements\InputFile;
use Library\Core\Html\Elements\FormElements\InputNumber;
use Library\Core\Html\Elements\FormElements\InputText;
use Library\Core\Html\Elements\FormElements\Select;
use \Library\Core\Test as Test;

/**
 * Form component unit tests
 * 
 * @author Nicolas Bonnici <nicolasbonnici@gmail.com>
 */
class FormTest extends Test
{

    protected static $oFormInstance;

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
    	self::$oFormInstance = new Form();
        $this->assertTrue(self::$oFormInstance instanceof Form);
    }

    public function testToString()
    {
        $this->assertEquals(self::$oFormInstance, self::$oFormInstance->render());
    }

    public function testSetAttributes()
    {
        $this->assertTrue(self::$oFormInstance->setAttributes($this->aTestDataArray) instanceof Form);
    }

    public function testSetAttribute()
    {
        // Also test if the setAttribute() overload properly the setAttributes()
        $this->assertTrue(
            self::$oFormInstance->setAttribute(self::TEST_STRING_KEY, self::TEST_STRING_VALUE) instanceof Form
        );
    }

    public function testGetAttribute()
    {
        // Assert that the generic accessors work properly
        foreach ($this->aTestDataArray as $sKey=>$mValue) {
            $this->assertEquals(self::$oFormInstance->getAttribute($sKey), $mValue);
        }
        $this->assertEquals(self::$oFormInstance->getAttribute(self::TEST_STRING_KEY), self::TEST_STRING_VALUE);
    }

    public function testGetAttributes()
    {
        $this->assertEquals(
            self::$oFormInstance->getAttributes(),
            array_merge($this->aTestDataArray, array(self::TEST_STRING_KEY=>self::TEST_STRING_VALUE))
        );
    }

    public function testRender()
    {
        $this->assertTrue(is_string(self::$oFormInstance->render()));
    }

    public function testGetSubForms()
    {
        $this->assertTrue(is_array(self::$oFormInstance->getSubForms()));
    }

    public function testAddElement()
    {
        $oInputText = new InputText();
        $this->assertTrue(self::$oFormInstance->addElement($oInputText) instanceof Form);
    }

    public function testAddElements()
    {
        $aElements = array(
            new InputText(array()),
            new InputNumber(array()),
            new InputFile(array()),
            new Select(array(), array()),
            new Autocomplete(array())
        );
        $this->assertTrue(self::$oFormInstance->addElements($aElements) instanceof Form);
    }

    public function testGetElements()
    {
        $this->assertTrue(is_array(self::$oFormInstance->getElements()));
        // Assert that the previous tests add 6 elements... quick and dirty dependancy between tests... puke
        $this->assertTrue(count(self::$oFormInstance->getElements()) === 6);
    }

    public function getValues()
    {
        $this->assertTrue(is_array(self::$oFormInstance->getValues()));
    }

    /**
     * @todo test getValue('someKey')
     */
}