<?php
namespace Library\Core\Tests\Html\Elements;

use Library\Core\Tests\Test;
use Library\Core\Html\Elements\FormElements\Textarea;

/**
 * Form element InputNumber component unit tests
 * 
 * @author Nicolas Bonnici <nicolasbonnici@gmail.com>
 */
class TextareaTest extends Test
{

    protected $oTextareaInstance;

    const INPUT_TAG    = 'textarea';
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
        $this->oTextareaInstance = new Textarea();
    }

    public function tearDown()
    {
    }

    public function testConstructor()
    {
        $this->assertTrue($this->oTextareaInstance instanceof Textarea);
    }

    public function testToString()
    {
        $this->assertEquals($this->oTextareaInstance->__toString(), $this->oTextareaInstance->render());
    }

    public function testSetThenGetValue()
    {
        $this->assertTrue($this->oTextareaInstance->setValue(self::TEST_STRING_VALUE) instanceof Textarea);
        $this->assertEquals($this->oTextareaInstance->getValue(), self::TEST_STRING_VALUE);
    }

    public function testRender()
    {
        $this->assertTrue(is_string($this->oTextareaInstance->render()));
        $this->assertNotEmpty(strstr($this->oTextareaInstance->render(), self::INPUT_TAG));
    }

}