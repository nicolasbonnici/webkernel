<?php
namespace Library\Core\Html;

/**
 * HTML5 attributes processor class
 *
 * @author niko <nicolasbonnici@gmail.com>
 */
class Attributes
{

    /**
     * HTML5 DOM node data attributes key name
     */
    const HTML5_DATA_ATTRIBUTE = 'data';

    /**
     * Element attributes
     *
     * Example :
     * array(
     *      'id'     => 'form-dom-node-id',
     *      'method' => ['post'|'get'],
     *      'action' => '/some/url/',
     *      'multiple' => [null|''],
     *      'class' => array('some-class', 'otherone', 'andsoon'),
     *      'data'  => array('key' => 'value', 'otherKey' => 'otherValue')
     * )
     *
     * @var array
     */
    protected $aAttributes = array();

    /**
     * Render form HTML DOM attributes
     *
     * @param array $aHtmlAttributes
     * @return string
     */
    public function renderAttributes()
    {
        $sAttributes = '';
        $aAttributes = $this->getAttributes();

        // if FormElement::bIsRequired || bIsDisabled || bIsReadOnly and no attribute related
        if (isset($this->bIsRequired, $this->bIsDisabled, $this->bIsReadOnly)) {
            if (
                $this->isRequired() === true &&
                (isset($aAttributes['required']) === false || $aAttributes['required'] === 'false')
            ) {
                $this->setAttribute('required', 'true');
            }

            if ($this->isDisabled() === true && isset($aAttributes['disabled']) === false) {
                $this->setAttribute('disabled', '');
            }

            if ($this->isReadOnly === true && isset($aAttributes['readonly']) === false) {
                $this->setAttribute('readonly', '');
            }
        }

        foreach ($aAttributes as $sAttrName => $mAttrValue) {
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

    /**
     * Set an attribute
     *
     * @param string $sAttrName
     * @param string $mAttrValue
     * @return Form
     */
    public function setAttribute($sAttrName, $mAttrValue)
    {
        if (isset($this->aAttributes[$sAttrName]) === true) {
            if (is_array($this->aAttributes[$sAttrName]) === true) {
                if (is_array($mAttrValue) === true) {
                    $this->aAttributes[$sAttrName] = array_merge_recursive($this->aAttributes[$sAttrName], $mAttrValue);
                } else {
                    $this->aAttributes[$sAttrName][] = $mAttrValue;
                }
            }
        }
        $this->aAttributes[$sAttrName] = $mAttrValue;
        return $this;
    }

    /**
     * Set all element attributes
     *
     * @param array $aAttributes
     * @return Form
     */
    public function setAttributes(array $aAttributes)
    {
        $this->aAttributes = array_merge_recursive($aAttributes);
        return $this;
    }

    /**
     * Get an element  attribute value
     * @param string $sAttrName
     * @return mixed string|int|array
     */
    public function getAttribute($sAttrName)
    {
        return (isset($this->aAttributes[$sAttrName]) === true) ? $this->aAttributes[$sAttrName] : null;
    }

    /**
     * Get all element attributes
     * @return array
     */
    public function getAttributes()
    {
        return $this->aAttributes;
    }

}