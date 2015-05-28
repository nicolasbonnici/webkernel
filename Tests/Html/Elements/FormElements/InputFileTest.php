<?php
namespace Library\Core\Tests\Html\Elements;

use Library\Core\Html\Elements\FormElements\InputFile;
use \Library\Core\Test as Test;

/**
 * Form element InputFile component unit tests
 * 
 * @author Nicolas Bonnici <nicolasbonnici@gmail.com>
 */
class InputFileTest extends Test
{

    protected static $oInputFileInstance;

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
    }

    public function tearDown()
    {
    }

    public function testConstructor()
    {
    	self::$oInputFileInstance = new InputFile(array());
        $this->assertTrue(self::$oInputFileInstance instanceof InputFile);
        $this->assertEquals(self::$oInputFileInstance->getAttribute('type'), self::INPUT_TYPE);
        $this->assertEquals(self::$oInputFileInstance->getAttribute('value'), self::INPUT_DEFAULT_VALUE);
    }

    public function testToString()
    {
        $this->assertEquals(self::$oInputFileInstance->__toString(), self::$oInputFileInstance->render());
    }

    public function testSetValue()
    {
        $this->assertTrue(self::$oInputFileInstance->setValue(self::TEST_STRING_VALUE) instanceof InputFile);
    }

    public function testGetValue()
    {
        $this->assertEquals(self::$oInputFileInstance->getValue(), self::TEST_STRING_VALUE);
    }

    public function testSetAttributes()
    {
        $this->assertTrue(self::$oInputFileInstance->setAttributes($this->aTestDataArray) instanceof InputFile);
    }

    public function testSetAttribute()
    {
        // Also test if the setAttribute() overload properly the setAttributes()
        $this->assertTrue(
            self::$oInputFileInstance->setAttribute(self::TEST_STRING_KEY, self::TEST_STRING_VALUE) instanceof InputFile
        );
    }

    public function testGetAttribute()
    {
        // Assert that the generic accessors work properly
        foreach ($this->aTestDataArray as $sKey=>$mValue) {
            $this->assertEquals(self::$oInputFileInstance->getAttribute($sKey), $mValue);
        }
        $this->assertEquals(self::$oInputFileInstance->getAttribute(self::TEST_STRING_KEY), self::TEST_STRING_VALUE);
    }

    public function testGetAttributes()
    {
        $this->assertEquals(
            self::$oInputFileInstance->getAttributes(),
            array_merge($this->aTestDataArray, array(self::TEST_STRING_KEY=>self::TEST_STRING_VALUE))
        );
    }

    public function testRender()
    {
        $this->assertTrue(is_string(self::$oInputFileInstance->render()));
        $this->assertNotEmpty(strstr(self::$oInputFileInstance->render(), self::INPUT_TAG));
    }

}