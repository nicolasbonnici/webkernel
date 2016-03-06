<?php
namespace Library\Core\Tests\Html\Elements;

use Library\Core\Tests\Test;
use Library\Core\Html\Elements\FormElements\InputNumber;

/**
 * Form element InputNumber component unit tests
 * 
 * @author Nicolas Bonnici <nicolasbonnici@gmail.com>
 */
class InputNumberTest extends Test
{

    protected $oInputNumberInstance;

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
        $this->oInputNumberInstance = new InputNumber();
    }

    public function tearDown()
    {
    }

    public function testConstructor()
    {
        $this->assertTrue($this->oInputNumberInstance instanceof InputNumber);
        $this->assertEquals($this->oInputNumberInstance->getAttribute('type'), self::INPUT_TYPE);
        $this->assertEquals($this->oInputNumberInstance->getAttribute('value'), self::INPUT_DEFAULT_VALUE);
    }

    public function testToString()
    {
        $this->assertEquals($this->oInputNumberInstance->__toString(), $this->oInputNumberInstance->render());
    }

    public function testSetThenGetValue()
    {
        $this->assertTrue($this->oInputNumberInstance->setValue(self::INPUT_TEST_VALUE) instanceof InputNumber);

        $this->assertEquals($this->oInputNumberInstance->getValue(), self::INPUT_TEST_VALUE);
    }

    public function testRender()
    {
        $this->assertTrue(is_string($this->oInputNumberInstance->render()));
        $this->assertNotEmpty(strstr($this->oInputNumberInstance->render(), self::INPUT_TAG));
    }

}