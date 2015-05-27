<?php
namespace Library\Core\Tests\Mocks;

use Library\Core\HtmlElement;

/**
 * HtmlElementMock
 *
 * @author niko <nicolasbonnici@gmail.com>
 */
class HtmlElementMock extends HtmlElement
{

    public function render()
    {
        return '<tag' . $this->oHtmlAttributes->render($this->getAttributes()) . '>Element content</tag>';
    }

}