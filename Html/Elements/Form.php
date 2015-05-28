<?php
namespace Library\Core\Html\Elements;

use Library\Core\Html\Element;

/**
 * HTML5 form handler
 *
 * @author niko <nicolasbonnici@gmail.com>
 */
class Form extends Element
{
    /**
     * HTML dom node label
     * @var string
     */
    protected $sMarkupTag = 'form';

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
     * Form's sub forms container
     * @var array
     */
    protected $aSubForms = array();

    /**
     * Form's elements container
     * @var array
     */
    protected $aElements = array();

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