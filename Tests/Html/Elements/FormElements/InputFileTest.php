<?php
namespace Library\Core\Tests\Html\Elements;

use Library\Core\Tests\Test;
use Library\Core\Html\Elements\FormElements\InputFile;

/**
 * Form element InputFile component unit tests
 * 
 * @author Nicolas Bonnici <nicolasbonnici@gmail.com>
 */
class InputFileTest extends Test
{

    /**
     * @var InputFile
     */
    protected $oInputFileInstance;

    const INPUT_TAG    = 'input';
    const INPUT_TYPE   = 'file';
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
        $this->oInputFileInstance = new InputFile(array());
    }

    public function tearDown()
    {
    }

    public function testConstructor()
    {
        $this->assertTrue($this->oInputFileInstance instanceof InputFile);
        $this->assertEquals($this->oInputFileInstance->getAttribute('type'), self::INPUT_TYPE);
        $this->assertEquals($this->oInputFileInstance->getAttribute('value'), self::INPUT_DEFAULT_VALUE);
    }

    public function testToString()
    {
        $this->assertEquals($this->oInputFileInstance->__toString(), $this->oInputFileInstance->render());
    }

    public function testSetThenGetValue()
    {
        $this->assertTrue($this->oInputFileInstance->setValue(self::TEST_STRING_VALUE) instanceof InputFile);
        $this->assertEquals($this->oInputFileInstance->getValue(), self::TEST_STRING_VALUE);
    }

    public function testSetAttributes()
    {
        $this->assertTrue($this->oInputFileInstance->setAttributes($this->aTestDataArray) instanceof InputFile);
    }

    public function testSetAttribute()
    {
        // Also test if the setAttribute() overload properly the setAttributes()
        $this->assertTrue(
            $this->oInputFileInstance->setAttribute(self::TEST_STRING_KEY, self::TEST_STRING_VALUE) instanceof InputFile
        );
    }

    public function testRender()
    {
        $this->assertTrue(is_string($this->oInputFileInstance->render()));
        $this->assertNotEmpty(strstr($this->oInputFileInstance->render(), self::INPUT_TAG));
    }

}