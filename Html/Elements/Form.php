<?php
namespace Library\Core\Html\Elements;

use Library\Core\Html\Element;
use Library\Core\Html\FormElement;

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

    const HTTP_METHOD_POST  = 'post';
    const HTTP_METHOD_GET   = 'get';

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

    public function __construct()
    {
        # Important
        parent::__construct();

        // Set default method attribute
        $this->setAttribute('method', self::HTTP_METHOD_POST);
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
     *
     * Add a new element to the form
     *
     * @param FormElement $oFormElement
     * @return $this
     */
    public function addElement(FormElement $oFormElement)
    {
        $this->addSubElement($oFormElement);
        return $this;
    }

    /**
     *
     * Add a new elements to the form
     *
     * @param array $aFormElements
     * @return $this
     */
    public function addElements(array $aFormElements)
    {

        foreach ($aFormElements as $oFormElement) {
            try {
                $this->addElement($oFormElement);
            } catch (\Exception $oException) {
                continue;
            }
        }
        return $this;
    }

    /**
     * Form's elements getter
     * @return array
     */
    public function getElements()
    {
        return $this->oNode->getElements();
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
     * @return Button
     */
    public function buildAsynchSubmitButton()
    {
        $oButton = new Button();
        $oButton->setAttributes(array(
            'type' => 'button',
            'class' => array(
                'btn',
                'btn-lg',
                'btn-primary',
                'ui-sendform'
            ),
            'data' => array(
                'form' => '#' . $this->getAttribute('id')
            )
        ));
        $oButton->setContent('<span class="glyphicon glyphicon-floppy-saved"></span> Sauvegarder');
        return $oButton;
    }
}