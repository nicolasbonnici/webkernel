<?php
namespace Library\Core;

/**
 * HTML5 form handler
 *
 * @author niko <nicolasbonnici@gmail.com>
 */
class Form
{

    /**
     * DOM id
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
    private $sMethod;

    /**
     * Form's sub forms
     * @var array
     */
    private $aSubForms = array();

    /**
     * Form's elements
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
        return '<form id="" action="" method=""></form>';
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
     * Form's method getter
     * @return string
     */
    public function getMethod()
    {
        return $this->sMethod;
    }

    /**
     * Form's methode setter
     * @param string $sMethod
     * @return \Library\Core\Form
     */
    public function setMethod($sMethod)
    {
        $this->sMethod = $sMethod;
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

}