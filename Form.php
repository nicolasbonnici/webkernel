<?php
namespace Library\Core;

/**
 * HTML5 form handler
 *
 * @author niko <nicolasbonnici@gmail.com>
 */
class Form
{

    const HTTP_METHOD_POST  = 'POST';
    const HTTP_METHOD_GET   = 'GET';

    /**
     * Form allowed values for attribute method
     * @var array
     */
    protected $aAllowedMethods = array(
        self::HTTP_METHOD_GET,
        self::HTTP_METHOD_POST
    );

    /**
     * Form attributes
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
    private $aAttributes = array();

    /**
     * Form's sub forms container
     * @var array
     */
    private $aSubForms = array();

    /**
     * Form's elements container
     * @var array
     */
    private $aElements = array();

    /**
     * Instance constructor
     */
    public function __construct()
    {

    }

    /**
     *  __toString overload to directly display the form
     */
    public function __toString()
    {
        return $this->render();
    }

    /**
     * Render the form HTML markup
     * @return string
     */
    public function render()
    {
        return '<form' . $this->renderAttributes() . '>' . $this->renderElements() . '</form>';
    }

    /**
     * Set a form attribute
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
     * Set all form attributes
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
     * Get a form attribute value
     * @param string $sAttrName
     * @return mixed string|int|array
     */
    public function getAttribute($sAttrName)
    {
        return (isset($this->aAttributes[$sAttrName]) === true) ? $this->aAttributes[$sAttrName] : null;
    }

    /**
     * Get all form attributes
     * @return array
     */
    public function getAttributes()
    {
        return $this->aAttributes;
    }

    /**
     * Form's subforms getter
     * @return array
     */
    public function getSubForms()
    {
        return $this->aSubForms;
    }

    /**
     * Form's elements getter
     * @return array
     */
    public function getElements()
    {
        return $this->aElements;
    }

    /**
     * Retrieve all elements values
     * @return array
     */
    public function getValues()
    {
        $aValues = array();
        foreach ($this->getElements() as $sName => $mValue) {
            $aValues[$sName] = $mValue;
        }

        // Also iterate on all sub forms elements
        foreach ($this->getSubForms() as $sSubFormName => $oSubForm) {
            $aValues[$sSubFormName] = $oSubForm->getValues();
        }

        return $aValues;
    }

    public function getValue($sElementName)
    {
        // @todo
    }

    /**
     * Render form HTML DOM attributes
     * @return string
     */
    protected function renderAttributes()
    {
        $sAttributes = '';
        foreach ($this->getAttributes() as $sAttrName => $mAttrValue) {
            $sAttributes .= $this->renderAttribute($sAttrName, $mAttrValue);
        }
        return $sAttributes;
    }

    /**
     * Render form HTML attribute
     *
     * @param string $sAttrName
     * @param string $mAttrValue
     * @return string
     */
    protected function renderAttribute($sAttrName, $mAttrValue = '')
    {
        $sAttribute = '';
        if (empty($sAttrName) === false) {
            if (is_array($mAttrValue) === true && empty($mAttrValue) === false) {

                if ($sAttrName === 'data') {
                    return $this->renderDataAttributes($mAttrValue);
                }

                $sAttribute .= ' ' . $sAttrName . '="' . implode(' ', $mAttrValue) . '"';
            } elseif (is_string($mAttrValue) === true && empty($mAttrValue) === false) {
                $sAttribute .=  ' ' . $sAttrName . '="' . $mAttrValue . '"';
            } else {
                // Just output the attribute name
                $sAttribute .= ' ' . $sAttrName;
            }
        }
        return $sAttribute;
    }

    /**
     * Render HTML5 data attributes
     *
     * @param array $aDataAttributes    The array to compute from
     * @return string
     */
    protected function renderDataAttributes(array $aDataAttributes)
    {
        $sDataAttributes = '';
        foreach ($aDataAttributes as $sKey => $sValue) {
            $sDataAttributes .= ' data-' . $sKey;
        }
        return $sDataAttributes;
    }

    /**
     * @todo
     */
    protected function renderElements()
    {
        foreach ($this->getElements() as $sElementName => $oFormElement) {
            // @todo ici appeler la methode de build de l element
        }
        return 'elements...';
    }

}