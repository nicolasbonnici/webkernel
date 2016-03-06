<?php
namespace Library\Core\Tests\Html\Elements;

use Library\Core\Tests\Test;

use Library\Core\Html\Elements\Form;
use Library\Core\Html\Elements\FormElements\Autocomplete;
use Library\Core\Html\Elements\FormElements\InputFile;
use Library\Core\Html\Elements\FormElements\InputNumber;
use Library\Core\Html\Elements\FormElements\InputText;
use Library\Core\Html\Elements\FormElements\Select;

/**
 * Form component unit tests
 * 
 * @author Nicolas Bonnici <nicolasbonnici@gmail.com>
 */
class FormTest extends Test
{

    /**
     * @var Form
     */
    protected $oFormInstance;

    const TEST_STRING_KEY   = 'test';
    const TEST_STRING_VALUE = 'test-value';

    protected $aTestDataArray = array(
        'id'     => 'form-dom-node-id',
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
        $this->oFormInstance = new Form();

    }

    public function tearDown()
    {
    }

    public function testConstructor()
    {
        $this->assertTrue($this->oFormInstance instanceof Form);
    }

    public function testToString()
    {
        $this->assertEquals($this->oFormInstance, $this->oFormInstance->render());
    }

    public function testRender()
    {
        $this->assertTrue(is_string($this->oFormInstance->render()));
    }

    public function testGetSubForms()
    {
        $this->assertTrue(is_array($this->oFormInstance->getSubForms()));
    }

    public function testAddElement()
    {
        $oInputText = new InputText();
        $this->assertTrue($this->oFormInstance->addElement($oInputText) instanceof Form);

        $aElements = $this->oFormInstance->getElements();
        $this->assertEquals(
            1,
            count($aElements),
            'Unable to add element to the form'
        );
    }

    public function testAddElementsThenGetThem()
    {
        $aElements = array(
            new InputText(array()),
            new InputNumber(array()),
            new InputFile(array()),
            new Select(array(), array()),
            new Autocomplete(array())
        );
        $this->assertTrue($this->oFormInstance->addElements($aElements) instanceof Form);
        $this->assertEquals(
            5,
            count($aElements),
            'Unable to add several elements to the form'
        );

        $this->assertTrue(is_array($this->oFormInstance->getElements()));
        // Assert that the previous tests add 6 elements... quick and dirty dependancy between tests... puke
        $this->assertTrue(count($this->oFormInstance->getElements()) === 5);
    }

    public function getValues()
    {
        $this->assertTrue(is_array($this->oFormInstance->getValues()));
    }

    /**
     * @todo test getValue('someKey')
     */
}