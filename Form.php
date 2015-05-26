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
     * Form's target url
     * @var string
     */
    private $sAction;

    /**
     * Form's method (GET|POST)
     * @var string
     */
    private $sMethod = self::HTTP_METHOD_POST;

    /**
     * Form's enctype attribute
     * @var string
     */
    private $sEnctype = '';

    /**
     * Form classes
     * @var array
     */
    private $aFormClasses = array();

    /**
     * Form's data attributes
     * @var array
     */
    private $aFormDataAttributes = array();

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
        // @todo support des classes
        // @todo support des data attributes
        // @todo support du enctype
        $sOutput = '<form id="' . $this->getId() . '" action="' . $this->getAction() . '" method="' . $this->getMethod() . '"></form>';
        return $sOutput;
    }

    /**
     * Form's DOM id getter
     * @return string
     */
    public function getId()
    {
        return $this->sId;
    }

    /**
     * Form'sDOM id setter
     * @param string $sId
     * @return \Library\Core\Form
     */
    public function setId($sId)
    {
        $this->sId = $sId;
        return $this;
    }

    /**
     * Form's action getter
     * @return string
     */
    public function getAction()
    {
        return $this->sAction;
    }

    /**
     * Form's action setter
     * @param string $sAction
     * @return \Library\Core\Form
     */
    public function setAction($sAction)
    {
        $this->sAction = $sAction;
        return $this;
    }

    /**
     * Form's method attribute getter
     * @return string
     */
    public function getMethod()
    {
        return $this->sMethod;
    }

    /**
     * Form's method attribute setter
     * @param string $sMethod
     * @return \Library\Core\Form
     */
    public function setMethod($sMethod)
    {
        $this->sMethod = $sMethod;
        return $this;
    }

    /**
     * Enctype attribute accessor
     * @return string
     */
    public function getEnctype()
    {
        return $this->sEnctype;
    }

    /**
     * Enctype attribute setter
     * @param $sEnctype
     * @return Form
     */
    public function setEnctype($sEnctype)
    {
        $this->sEnctype = $sEnctype;
        return $this;
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