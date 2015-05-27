<?php
namespace Library\Core;

/**
 * HTML5 attributes processor class
 *
 * @author niko <nicolasbonnici@gmail.com>
 */
class HtmlAttributes
{

    /**
     * HTML5 DOM node data attributes key name
     */
    const HTML5_DATA_ATTRIBUTE = 'data';

    /**
     * Render form HTML DOM attributes
     *
     * @param array $aHtmlAttributes
     * @return string
     */
    public function render(array $aHtmlAttributes)
    {
        $sAttributes = '';
        foreach ($aHtmlAttributes as $sAttrName => $mAttrValue) {
            $sAttributes .= $this->renderAttribute($sAttrName, $mAttrValue);
        }
        return $sAttributes;
    }

    /**
     * Render HTML attribute
     *
     * @param string $sAttrName
     * @param mixed int|string|qrray $mAttrValue
     * @return string
     */
    protected function renderAttribute($sAttrName, $mAttrValue = '')
    {
        $sAttribute = '';
        if (empty($sAttrName) === false) {
            if (is_array($mAttrValue) === true && empty($mAttrValue) === false) {

                if ($sAttrName === self::HTML5_DATA_ATTRIBUTE) {
                    return $this->renderDataAttributes($mAttrValue);
                }

                $sAttribute .= ' ' . $sAttrName . '="' . implode(' ', $mAttrValue) . '"';
            } elseif (is_string($mAttrValue) === true && empty($mAttrValue) === false) {
                $sAttribute .=  ' ' . $sAttrName . '="' . $mAttrValue . '"';
            } else {
                // Just output the attribute name
                $sAttribute .= ' ' . $sAttrName;
            }
        }
        return $sAttribute;
    }

    /**
     * Render HTML5 data attributes
     *
     * @param array $aDataAttributes    The array to compute from
     * @return string
     */
    protected function renderDataAttributes(array $aDataAttributes)
    {
        $sDataAttributes = '';
        foreach ($aDataAttributes as $sKey => $sValue) {
            $sDataAttributes .= ' ' . self::HTML5_DATA_ATTRIBUTE . '-' . $sKey . '="' . $sValue . '"';
        }
        return $sDataAttributes;
    }

}