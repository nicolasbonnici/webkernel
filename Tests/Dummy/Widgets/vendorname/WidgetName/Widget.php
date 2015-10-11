<?php
namespace Library\Core\Tests\Dummy\Widgets\vendorname\WidgetName;

use Library\Core\App\Widgets\WidgetAbstract;

/**
 * A simple plugin to easily integrate Google Analytics on your project
 *
 * Class Plugin
 * @package Widgets\GoogleAnalytics
 * @author Nicolas Bonnici nicolasbonnici@gmail.com
 */
class Widget extends WidgetAbstract
{
    protected $sVersion    = '0.1';

    /**
     * Build widget data
     * @return bool
     */
    protected function build()
    {
        $this->addParameter('sMessage', 'Hello world!');
    }

}