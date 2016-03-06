<?php
namespace Library\Core\Tests\Html\Elements;

use Library\Core\Tests\Test;
use Library\Core\Html\Elements\FormElements\Tag;

/**
 * Form element autocomplete component unit tests
 * 
 * @author Nicolas Bonnici <nicolasbonnici@gmail.com>
 */
class TagTest extends Test
{

    protected $oTagInstance;

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
        $this->oTagInstance = new Tag();
    }

    public function tearDown()
    {
    }

    public function testConstructor()
    {
        $this->assertTrue($this->oTagInstance instanceof Tag);
    }

    public function testToString()
    {
        $this->assertEquals($this->oTagInstance->__toString(), $this->oTagInstance->render());
    }

    public function testSetThenGetValue()
    {
        $this->assertTrue($this->oTagInstance->setValue(self::TEST_STRING_VALUE) instanceof Tag);
        $this->assertEquals($this->oTagInstance->getValue(), self::TEST_STRING_VALUE);
    }

    public function testRender()
    {
        $this->assertTrue(is_string($this->oTagInstance->render()));
        $this->assertNotEmpty(strstr($this->oTagInstance->render(), self::INPUT_TAG));
    }

}