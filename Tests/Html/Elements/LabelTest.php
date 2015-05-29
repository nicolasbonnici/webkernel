<?php
namespace Library\Core\Tests\Html\Elements;

use Library\Core\Html\Elements\Label;
use \Library\Core\Test as Test;

/**
 * Label component unit tests
 * 
 * @author Nicolas Bonnici <nicolasbonnici@gmail.com>
 */
class LabelTest extends Test
{

    protected static $oLabelInstance;

    const INPUT_TAG = 'label';
    const TEST_STRING_KEY   = 'test';
    const TEST_STRING_VALUE = 'test-value';

    protected $aTestDataArray = array(
        'id'     => 'form-dom-node-id',
        'method' => 'post',
        'action' => '/some/url/',
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
    	self::$oLabelInstance = new Label();
        $this->assertTrue(self::$oLabelInstance instanceof Label);
    }

    public function testToString()
    {
        $this->assertEquals(self::$oLabelInstance, self::$oLabelInstance->render());
    }

    public function testSetAttributes()
    {
        $this->assertTrue(self::$oLabelInstance->setAttributes($this->aTestDataArray) instanceof Label);
    }

    public function testSetAttribute()
    {
        // Also test if the setAttribute() overload properly the setAttributes()
        $this->assertTrue(
            self::$oLabelInstance->setAttribute(self::TEST_STRING_KEY, self::TEST_STRING_VALUE) instanceof Label
        );
    }

    public function testGetAttribute()
    {
        // Assert that the generic accessors work properly
        foreach ($this->aTestDataArray as $sKey=>$mValue) {
            $this->assertEquals(self::$oLabelInstance->getAttribute($sKey), $mValue);
        }
        $this->assertEquals(self::$oLabelInstance->getAttribute(self::TEST_STRING_KEY), self::TEST_STRING_VALUE);
    }

    public function testGetAttributes()
    {
        $this->assertEquals(
            self::$oLabelInstance->getAttributes(),
            array_merge($this->aTestDataArray, array(self::TEST_STRING_KEY=>self::TEST_STRING_VALUE))
        );
    }

    public function testRender()
    {
        $this->assertTrue(is_string(self::$oLabelInstance->render()));
        $this->assertNotEmpty(strstr(self::$oLabelInstance->render(), self::INPUT_TAG), 'Element tag "' . self::INPUT_TAG . '" not found');
    }

}