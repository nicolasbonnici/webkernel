<?php
namespace Library\Core\Tests\Html\Elements;

use Library\Core\Tests\Test;
use Library\Core\Html\Elements\FormElements\Editor;

/**
 * Form element InputNumber component unit tests
 * 
 * @author Nicolas Bonnici <nicolasbonnici@gmail.com>
 */
class EditorTest extends Test
{

    protected $oEditorInstance;

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
        $this->oEditorInstance = new Editor();
    }

    public function tearDown()
    {
    }

    public function testConstructor()
    {
        $this->assertTrue($this->oEditorInstance instanceof Editor);
    }

    public function testToString()
    {
        $this->assertEquals($this->oEditorInstance->__toString(), $this->oEditorInstance->render());
    }

    public function testSetThenGetValue()
    {
        $this->assertTrue($this->oEditorInstance->setValue(self::TEST_STRING_VALUE) instanceof Editor);

        $this->assertEquals($this->oEditorInstance->getValue(), self::TEST_STRING_VALUE);
    }

    public function testSetAttributesThenRetrieveItBack()
    {
        $this->assertTrue($this->oEditorInstance->setAttributes($this->aTestDataArray) instanceof Editor);

        // Also test if the setAttribute() overload properly the setAttributes()
        $this->assertTrue(
            $this->oEditorInstance->setAttribute(self::TEST_STRING_KEY, self::TEST_STRING_VALUE) instanceof Editor
        );

        # Just the default editor and Bootstrap3 form element class
        $aData = $this->aTestDataArray;
        $aData['class'] = array('form-control', 'ui-editor', 'some-class', 'otherone', 'andsoon');

        // Assert that the generic accessors work properly
        foreach ($aData as $sKey=>$mValue) {
            $this->assertEquals($this->oEditorInstance->getAttribute($sKey), $mValue);
        }
        $this->assertEquals($this->oEditorInstance->getAttribute(self::TEST_STRING_KEY), self::TEST_STRING_VALUE);

    }

    public function testRender()
    {
        $this->assertTrue(is_string($this->oEditorInstance->render()));
        $this->assertNotEmpty(strstr($this->oEditorInstance->render(), self::INPUT_TAG));
    }

}