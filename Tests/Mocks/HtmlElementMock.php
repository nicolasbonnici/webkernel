<?php
namespace Core\Tests\Mocks;

use Core\Html\Element;

/**
 * HtmlElementMock
 *
 * @author niko <nicolasbonnici@gmail.com>
 */
class HtmlElementMock extends Element
{

    public function render()
    {
        return '<tag' . $this->oHtmlAttributes->render($this->getAttributes()) . '>Element content</tag>';
    }

}