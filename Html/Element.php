<?php
namespace Library\Core\Html;

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
     * Node to store sub elements
     *
     * @var Node
     */
    protected $oNode;

    public function __construct()
    {
        $this->oNode = new Node();
    }

    /**
     *  __toString overload to directly display the dom node element
     */
    public function __toString()
    {
        return $this->render();
    }

    /**
     * Render the HTML markup
     *
     * @return string
     */
    public function render()
    {
        return '<' . $this->sMarkupTag . $this->renderAttributes() .
                (
                    ($this->bAutoCloseMarkup === true)
                        ? ' />'
                        :'>' . $this->getContent() . $this->renderSubElements() . '</' . $this->sMarkupTag . '>'
                );
    }

    /**
     * Render all sub elements
     *
     * @return string
     */
    private function renderSubElements()
    {
        $sBuffer = '';
        /** @var Element $oElement */
        foreach ($this->oNode->getElements() as $oElement) {
            $sBuffer .= $oElement->render();
        }
        return $sBuffer;
    }

    /**
     * Get all Element sub Elements
     *
     * @return array
     */
    public function getSubElements()
    {
       return $this->oNode->getElements();
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

    /**
     * Add a sub element to item
     *
     * @param Element $oElement
     * @return Element
     */
    public function addSubElement(Element $oElement)
    {
        $this->oNode->addElement($oElement);
        return $this;
    }

    /**
     * Add several sub elements to item
     *
     * @param Element $oElement
     * @return Element
     */
    public function addSubElements(array $aElements)
    {
        foreach ($aElements as $oElement) {
            $this->oNode->addElement($oElement);
        }
        return $this;
    }

}