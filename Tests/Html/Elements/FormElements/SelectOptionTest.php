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

    protected $oSelectOptionInstance;

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
        $this->oSelectOptionInstance = new SelectOption();
    }

    public function tearDown()
    {
    }

    public function testConstructor()
    {
        $this->assertTrue($this->oSelectOptionInstance instanceof SelectOption);
    }

    public function testToString()
    {
        $this->assertEquals($this->oSelectOptionInstance->__toString(), $this->oSelectOptionInstance->render());
    }

    public function testSetThenGetValue()
    {
        $this->assertTrue($this->oSelectOptionInstance->setValue(self::TEST_STRING_VALUE) instanceof SelectOption);
        $this->assertEquals($this->oSelectOptionInstance->getValue(), self::TEST_STRING_VALUE);
    }

    public function testRender()
    {
        $this->assertTrue(is_string($this->oSelectOptionInstance->render()));
        $this->assertNotEmpty(strstr($this->oSelectOptionInstance->render(), self::INPUT_TAG));
    }

}