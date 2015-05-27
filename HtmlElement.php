<?php
namespace Library\Core;

/**
 * HTML5 DOM node element abstract
 *
 * @author niko <nicolasbonnici@gmail.com>
 */
abstract class HtmlElement
{

    /**
     * Element attributes
     *
     * Example :
     * array(
     *      'id'     => 'form-dom-node-id',
     *      'method' => ['post'|'get'],
     *      'action' => '/some/url/',
     *      'multiple' => [null|''],
     *      'class' => array('some-class', 'otherone', 'andsoon'),
     *      'data'  => array('key' => 'value', 'otherKey' => 'otherValue')
     * )
     *
     * @var array
     */
    protected $aAttributes = array();

    public function __construct()
    {

    }

    /**
     * Render the HTML markup
     * @return string
     */
    abstract public function render();

    /**
     * Set an attribute
     *
     * @param string $sAttrName
     * @param string $mAttrValue
     * @return Form
     */
    public function setAttribute($sAttrName, $mAttrValue)
    {
        $this->aAttributes[$sAttrName] = $mAttrValue;
        return $this;
    }

    /**
     * Set all element attributes
     *
     * @param array $aAttributes
     * @return Form
     */
    public function setAttributes(array $aAttributes)
    {
        $this->aAttributes = $aAttributes;
        return $this;
    }

    /**
     * Get an element  attribute value
     * @param string $sAttrName
     * @return mixed string|int|array
     */
    public function getAttribute($sAttrName)
    {
        return (isset($this->aAttributes[$sAttrName]) === true) ? $this->aAttributes[$sAttrName] : null;
    }

    /**
     * Get all element attributes
     * @return array
     */
    public function getAttributes()
    {
        return $this->aAttributes;
    }

}