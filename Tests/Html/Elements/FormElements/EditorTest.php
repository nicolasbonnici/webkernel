<?php
namespace Core\Tests\Html\Elements;

use Core\Html\Elements\FormElements\Editor;
use \Core\Test as Test;

/**
 * Form element InputNumber component unit tests
 * 
 * @author Nicolas Bonnici <nicolasbonnici@gmail.com>
 */
class EditorTest extends Test
{

protected static $oEditorInstance;

    const INPUT_TAG    = 'div';
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
    }

    public function tearDown()
    {
    }

    public function testConstructor()
    {
    	self::$oEditorInstance = new Editor(array());
        $this->assertTrue(self::$oEditorInstance instanceof Editor);
        $this->assertEquals(self::$oEditorInstance->getAttribute('contenteditable'), 'true');
    }

    public function testToString()
    {
        $this->assertEquals(self::$oEditorInstance->__toString(), self::$oEditorInstance->render());
    }

    public function testSetValue()
    {
        $this->assertTrue(self::$oEditorInstance->setValue(self::TEST_STRING_VALUE) instanceof Editor);
    }

    public function testGetValue()
    {
        $this->assertEquals(self::$oEditorInstance->getValue(), self::TEST_STRING_VALUE);
    }

    public function testSetAttributes()
    {
        $this->assertTrue(self::$oEditorInstance->setAttributes($this->aTestDataArray) instanceof Editor);
    }

    public function testSetAttribute()
    {
        // Also test if the setAttribute() overload properly the setAttributes()
        $this->assertTrue(
            self::$oEditorInstance->setAttribute(self::TEST_STRING_KEY, self::TEST_STRING_VALUE) instanceof Editor
        );
    }

    public function testGetAttribute()
    {
        // Assert that the generic accessors work properly
        foreach ($this->aTestDataArray as $sKey=>$mValue) {
            $this->assertEquals(self::$oEditorInstance->getAttribute($sKey), $mValue);
        }
        $this->assertEquals(self::$oEditorInstance->getAttribute(self::TEST_STRING_KEY), self::TEST_STRING_VALUE);
    }

    public function testGetAttributes()
    {
        $this->assertEquals(
            self::$oEditorInstance->getAttributes(),
            array_merge($this->aTestDataArray, array(self::TEST_STRING_KEY=>self::TEST_STRING_VALUE))
        );
    }

    public function testRender()
    {
        $this->assertTrue(is_string(self::$oEditorInstance->render()));
        $this->assertNotEmpty(strstr(self::$oEditorInstance->render(), self::INPUT_TAG));
    }

}