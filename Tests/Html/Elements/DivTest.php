<?php
namespace Library\Core\Tests\Html\Elements;

use Library\Core\Tests\Test;
use Library\Core\Html\Elements\Div;

/**
 * Div component unit tests
 * 
 * @author Nicolas Bonnici <nicolasbonnici@gmail.com>
 */
class DivTest extends Test
{

    protected static $oDivInstance;

    const INPUT_TAG = 'div';
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
    	self::$oDivInstance = new Div();
        $this->assertTrue(self::$oDivInstance instanceof Div);
    }

    public function testToString()
    {
        $this->assertEquals(self::$oDivInstance, self::$oDivInstance->render());
    }

    public function testSetAttributes()
    {
        $this->assertTrue(self::$oDivInstance->setAttributes($this->aTestDataArray) instanceof Div);
    }

    public function testSetAttribute()
    {
        // Also test if the setAttribute() overload properly the setAttributes()
        $this->assertTrue(
            self::$oDivInstance->setAttribute(self::TEST_STRING_KEY, self::TEST_STRING_VALUE) instanceof Div
        );
    }

    public function testGetAttribute()
    {
        // Assert that the generic accessors work properly
        foreach ($this->aTestDataArray as $sKey=>$mValue) {
            $this->assertEquals(self::$oDivInstance->getAttribute($sKey), $mValue);
        }
        $this->assertEquals(self::$oDivInstance->getAttribute(self::TEST_STRING_KEY), self::TEST_STRING_VALUE);
    }

    public function testGetAttributes()
    {
        $this->assertEquals(
            self::$oDivInstance->getAttributes(),
            array_merge($this->aTestDataArray, array(self::TEST_STRING_KEY=>self::TEST_STRING_VALUE))
        );
    }

    public function testRender()
    {
        $this->assertTrue(is_string(self::$oDivInstance->render()));
        $this->assertNotEmpty(strstr(self::$oDivInstance->render(), self::INPUT_TAG));
    }

}