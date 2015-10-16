<?php
namespace Library\Core\Html\Elements;

/**
 * HTML5 Twitter Bootstrap 3+ Form layer
 *
 * @author niko <nicolasbonnici@gmail.com>
 */
class Bootstrap3Form extends Form
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