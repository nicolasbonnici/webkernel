<?php
namespace Library\Core\Tests\Dummy\Widgets\vendorname\WidgetName;

use Library\Core\App\Widgets\WidgetAbstract;
use Library\Core\Html\Elements\Div;

/**
 * A simple plugin to easily integrate Google Analytics on your project
 *
 * Class Plugin
 * @package Widgets\GoogleAnalytics
 * @author Nicolas Bonnici nicolasbonnici@gmail.com
 */
class WidgetNameWidget extends WidgetAbstract
{
    protected $sVersion    = '0.1';

    /**
     * Build widget data
     * @return bool
     */
    protected function build()
    {

        $oDivElement = new Div();
        $oDivElement->setContent('Hello world!');

        $this->addHtmlElement($oDivElement);

        $this->bIsLoaded = true;

        return $this->isLoaded();
    }

}