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
     * Form DOM node id
     * @var string
     */
    private $sId;

    /**
     * Form attributes
     * @var array
     */
    private $aFormAttributes = array();

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
     * @todo
     * @return string
     */
    public function render()
    {
        // @todo render des attributs
        $sOutput = '<form id="' . $this->getId() . '" action="' . $this->getAction() . '" method="' . $this->getMethod() . '"></form>';
        return $sOutput;
    }

    public function renderAttributes()
    {
        // @todo
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
        $this->aFormAttributes[$sAttrName] = $mAttrValue;
        return $this;
    }

    /**
     * Get a form attribute value
     * @param string $sAttrName
     * @return mixed string|int|array
     */
    public function getAttribute($sAttrName)
    {
        return (isset($this->aFormAttributes[$sAttrName]) === true) ? $this->aFormAttributes[$sAttrName] : null;
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

}