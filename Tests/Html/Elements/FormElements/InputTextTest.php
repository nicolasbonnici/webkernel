<?php
namespace Library\Core\Tests\Html\Elements;

use Library\Core\Tests\Test;
use Library\Core\Html\Elements\FormElements\InputText;

/**
 * Form element InputText component unit tests
 * 
 * @author Nicolas Bonnici <nicolasbonnici@gmail.com>
 */
class InputTextTest extends Test
{

    protected $oInputTextInstance;

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
        $this->oInputTextInstance = new InputText(array());
    }

    public function tearDown()
    {
    }

    public function testConstructor()
    {
        $this->assertTrue($this->oInputTextInstance instanceof InputText);
        $this->assertEquals($this->oInputTextInstance->getAttribute('type'), self::INPUT_TYPE);
        $this->assertEquals($this->oInputTextInstance->getAttribute('value'), self::INPUT_DEFAULT_VALUE);
    }

    public function testToString()
    {
        $this->assertEquals($this->oInputTextInstance->__toString(), $this->oInputTextInstance->render());
    }

    public function testSetThenGetValue()
    {
        $this->assertTrue($this->oInputTextInstance->setValue(self::TEST_STRING_VALUE) instanceof InputText);
        $this->assertEquals($this->oInputTextInstance->getValue(), self::TEST_STRING_VALUE);
    }

    public function testRender()
    {
        $this->assertTrue(is_string($this->oInputTextInstance->render()));
        $this->assertNotEmpty(strstr($this->oInputTextInstance->render(), self::INPUT_TAG));
    }

}