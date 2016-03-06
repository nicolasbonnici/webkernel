<?php
namespace Library\Core\Html\Elements;
use Library\Core\Html\Element;

/**
 * HTML5 Twitter Bootstrap 3+ Form layer
 *
 * @author niko <nicolasbonnici@gmail.com>
 */
class Bootstrap3Form extends Form
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Override the addSubElement() method to add the Bootstrap3 form markup
     *
     * @param Element $oElement
     * @param bool $bSkipDecoration
     * @return Element
     */
    public function addSubElement(Element $oElement, $bSkipDecoration = false)
    {
        if ($bSkipDecoration === true) {
            return parent::addSubElement($oElement);
        } else {
            $oDivElement = new Div();
            $oDivElement->setAttribute('class', 'form-group');

            # If no label provided we skip the html label element creationgrid
            $aSubElements = array();
            if (empty($oElement->getLabel()) === false) {
                $oLabelElement = new Label();
                $oLabelElement->setContent($oElement->getLabel());
                $aSubElements[] = $oLabelElement;
            }

            $aSubElements[] = $oElement;

            $oDivElement->addSubElements(
                $aSubElements
            );

            return parent::addSubElement($oDivElement);
        }
    }


}