<?php
namespace Library\Core\Html;

/**
 * That component can handle a DOM node or the whole DOM content
 *
 * Class Node
 * @package Library\Core\Html
 */
class Node
{
    /**
     * Nodes elements
     * @var array
     */
    protected $aElements = array();

    /**
     * Render the Node
     * @return string
     */
    public function render()
    {
        $sOutput = '';
        /** @var Element $oElement */
        foreach ($this->aElements as $oElement) {
            $sOutput .= $oElement->render();
        }
        return $sOutput;
    }


    /**
     * Add a Html Element to node
     *
     * @param Element $oElement
     * @return Node
     */
    public function addElement(Element $oElement)
    {
        $this->aElements[] = $oElement;
        return $this;
    }

    /**
     * Add several Html Elements
     *
     * @param array $aElements
     * @return Node
     */
    public function addElements(array $aElements)
    {
        foreach ($aElements as $oElement) {
            $this->addElement($oElement);
        }
        return $this;
    }

    /**
     * Get node elements as an array
     * @return array
     */
    public function getElements()
    {
        return $this->aElements;
    }
}