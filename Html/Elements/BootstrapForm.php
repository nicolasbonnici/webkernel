<?php
namespace Library\Core\Html\Elements;

use Library\Core\Html\Element;
use Library\Core\Html\FormElement;

/**
 * HTML5 form for Twitter Bootstrap 3+
 *
 * @author niko <nicolasbonnici@gmail.com>
 */
class BootstrapForm extends Form
{

    public function __construct()
    {
        // Set default method attribute
        $this->setAttribute('method', self::HTTP_METHOD_POST);
    }

    /**
     * Build the form elements markup for Twitter Bootstrap
     * @return string
     */
    public function getContent()
    {
        $sElementsMarkup = '';

        $oDivElement = new Div();
        $oLabelElement = new Label();
        $oDivElement->setAttribute('class', 'form-group');

        foreach ($this->getElements() as $iIndex => $oFormElement) {

            $oLabelElement->setContent($oFormElement->getLabel());
            $oDivElement->setContent($oLabelElement->render() . $oFormElement->render());

            $sElementsMarkup .= $oDivElement->render();
        }
        return $sElementsMarkup;
    }

}