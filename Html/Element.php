<?php
namespace Core\Html;

/**
 * HTML5 DOM node element abstract
 *
 * @author niko <nicolasbonnici@gmail.com>
 */
abstract class Element extends Attributes
{

    /**
     * HTML dom node label
     * @var string
     */
    protected $sMarkupTag = 'tag';

    /**
     * Flag for auto close HTML dom node element Ex: <br />
     * @var bool
     */
    protected $bAutoCloseMarkup = false;

    /**
     * Element content
     * @var string
     */
    protected $sContent = '';

    /**
     * HtmlAttributes instance
     * @var HtmlAttributes
     */
    private $oHtmlAttributes;

    public function __construct()
    {

    }

    /**
     * Render the HTML markup
     * @return string
     */
    public function render()
    {
        return '<' . $this->sMarkupTag . $this->renderAttributes() .
                (
                    ($this->bAutoCloseMarkup === true)
                        ? ' />'
                        :'>' . $this->getContent() . '</' . $this->sMarkupTag . '>'
                );
    }

    /**
     *  __toString overload to directly display the dom node element
     */
    public function __toString()
    {
        return $this->render();
    }

    /**
     * Set element content
     * @param $sContent
     * @return Element
     */
    public function setContent($sContent)
    {
        $this->sContent = $sContent;
        return $this;
    }

    /**
     * Get element content
     * @return string
     */
    public function getContent()
    {
        return $this->sContent;
    }

}