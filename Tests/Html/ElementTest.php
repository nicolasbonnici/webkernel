<?php
namespace Library\Core\Tests\Html;

use Library\Core\Html\Elements\Div;
use Library\Core\Tests\Test;
use Library\Core\Tests\Dummy\Html\ElementMock;

/**
 * Html Element component unit tests
 * 
 * @author Nicolas Bonnici <nicolasbonnici@gmail.com>
 */
class ElementTest extends Test
{

    /**
     * @var \Library\Core\Html\Element
     */
    protected $oHtmlElementInstance;

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

    protected $sExpected = ' id="form-dom-node-id" method="post" action="/some/url/" multiple class="some-class otherone andsoon" data-key="value" data-otherKey="otherValue"';


    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
    }

    public function setUp()
    {
        $this->oHtmlElementInstance = new ElementMock();
    }

    public function tearDown()
    {
    }

    public function testConstructor()
    {
        $this->assertTrue($this->oHtmlElementInstance instanceof ElementMock);
    }


    public function testSetAttributesThenRetrieveThem()
    {
        $this->assertTrue($this->oHtmlElementInstance->setAttributes($this->aTestDataArray) instanceof ElementMock);

        // Also test if the setAttribute() overload properly the setAttributes()
        $this->assertTrue(
            $this->oHtmlElementInstance->setAttribute(self::TEST_STRING_KEY, self::TEST_STRING_VALUE) instanceof ElementMock
        );

        // Assert that the generic accessors work properly
        foreach ($this->aTestDataArray as $sKey=>$mValue) {
            $this->assertEquals(
                $this->oHtmlElementInstance->getAttribute($sKey),
                $mValue,
                'Unable to retrieve ' . $sKey . ' attribute'
            );
        }
        $this->assertEquals($this->oHtmlElementInstance->getAttribute(self::TEST_STRING_KEY), self::TEST_STRING_VALUE);
    }

    public function testGetAttributes()
    {
        $this->assertTrue($this->oHtmlElementInstance->setAttributes($this->aTestDataArray) instanceof ElementMock);
        $this->oHtmlElementInstance->setAttribute(self::TEST_STRING_KEY, self::TEST_STRING_VALUE);
        $this->assertEquals(
            array_merge($this->aTestDataArray, array(self::TEST_STRING_KEY=>self::TEST_STRING_VALUE)),
            $this->oHtmlElementInstance->getAttributes(),
            'Unable to retrieve correct attributes value'
        );
    }

    public function testSetSameAttributesMergeCorrectly()
    {
        $this->oHtmlElementInstance->setAttribute('class', 'foo');
        $this->oHtmlElementInstance->setAttribute('class', 'foo1');
        $this->oHtmlElementInstance->setAttribute('class', 'foo2');
        $this->oHtmlElementInstance->setAttribute('class', 'foo3');

        $this->assertEquals(
            array(
                'foo',
                'foo1',
                'foo2',
                'foo3'
            ),
            $this->oHtmlElementInstance->getAttribute('class'),
            'Unable to set several values from for the same attributes'
        );

        $this->oHtmlElementInstance->setAttribute('class', array('foo4', 'foo5'));


        $this->assertEquals(
            array(
                'foo',
                'foo1',
                'foo2',
                'foo3',
                'foo4',
                'foo5'
            ),
            $this->oHtmlElementInstance->getAttribute('class'),
            'Unable to set several values from the same attribute from an array'
        );
    }

    public function testSetAttributeThenSetAttributesOnSameProperty()
    {

    }

    public function testAddSubElement()
    {
        $oDiv = new Div();
        $oDiv->setContent('<p>test</p>');

        $this->oHtmlElementInstance->addSubElement($oDiv);
        $aSubElements = $this->oHtmlElementInstance->getSubElements();
        $this->assertEquals(
            1,
            count($aSubElements),
            'Unable to add a sub element'
        );

    }

    public function testAddSubElements()
    {
        $oDiv = new Div();
        $oDiv->setContent('<p>test</p>');
        $oDiv1 = new Div();
        $oDiv1->setContent('<p>test</p>');
        $oDiv2 = new Div();
        $oDiv2->setContent('<p>test</p>');

        $this->oHtmlElementInstance->addSubElements(array($oDiv, $oDiv1, $oDiv2));

        $aSubElements = $this->oHtmlElementInstance->getSubElements();

        $this->assertEquals(
            3,
            count($aSubElements),
            'Unable to add several sub elements in one call'
        );

    }

}