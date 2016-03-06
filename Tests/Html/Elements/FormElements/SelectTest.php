<?php
namespace Library\Core\Tests\Html\Elements;

use Library\Core\Tests\Test;
use Library\Core\Html\Elements\FormElements\Select;

/**
 * Form element InputNumber component unit tests
 * 
 * @author Nicolas Bonnici <nicolasbonnici@gmail.com>
 */
class SelectTest extends Test
{

    protected $oSelectInstance;

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
        $this->oSelectInstance = new Select($this->aTestOptions, array());

    }

    public function tearDown()
    {
    }

    public function testConstructor()
    {
        $this->assertTrue($this->oSelectInstance instanceof Select);
    }

    public function testToString()
    {
        $this->assertEquals($this->oSelectInstance->__toString(), $this->oSelectInstance->render());
    }

    public function testSetThenGetValue()
    {
        $this->assertTrue($this->oSelectInstance->setValue(self::TEST_STRING_VALUE) instanceof Select);
        $this->assertEquals($this->oSelectInstance->getValue(), self::TEST_STRING_VALUE);
    }

    public function testRender()
    {
        $this->assertTrue(is_string($this->oSelectInstance->render()));
        $this->assertNotEmpty(strstr($this->oSelectInstance->render(), self::INPUT_TAG));
    }

}