<?php
namespace Core\Tests\Html;

use Core\Html\Attributes;
use \Core\Test as Test;

/**
 * Html Attributes component unit tests
 * 
 * @author Nicolas Bonnici <nicolasbonnici@gmail.com>
 */
class AttributesTest extends Test
{

    protected static $oAttributesInstance;

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

    protected $sExpected = ' id="form-dom-node-id" method="post" action="/some/url/" multiple class="some-class otherone andsoon" data-key="value" data-otherKey="otherValue" test="test-value"';


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
    	self::$oAttributesInstance = new Attributes();
        $this->assertTrue(self::$oAttributesInstance instanceof Attributes);
    }


    public function testSetAttributes()
    {
        $this->assertTrue(self::$oAttributesInstance->setAttributes($this->aTestDataArray) instanceof Attributes);
    }

    public function testSetAttribute()
    {
        // Also test if the setAttribute() overload properly the setAttributes()
        $this->assertTrue(
            self::$oAttributesInstance->setAttribute(self::TEST_STRING_KEY, self::TEST_STRING_VALUE) instanceof Attributes
        );
    }

    public function testGetAttribute()
    {
        // Assert that the generic accessors work properly
        foreach ($this->aTestDataArray as $sKey=>$mValue) {
            $this->assertEquals(self::$oAttributesInstance->getAttribute($sKey), $mValue);
        }
        $this->assertEquals(self::$oAttributesInstance->getAttribute(self::TEST_STRING_KEY), self::TEST_STRING_VALUE);
    }

    public function testGetAttributes()
    {
        $this->assertEquals(
            self::$oAttributesInstance->getAttributes(),
            array_merge($this->aTestDataArray, array(self::TEST_STRING_KEY=>self::TEST_STRING_VALUE))
        );
    }

    public function testRenderAttributes()
    {
        $this->assertTrue(is_string(self::$oAttributesInstance->renderAttributes()));
        $this->assertEquals(self::$oAttributesInstance->renderAttributes($this->aTestDataArray), $this->sExpected);
    }

}