<?php


namespace Library\Core\Html\Elements\FormElements;


use Library\Core\Html\FormElement;

/**
 * Autocomplete FormElement
 * User: niko
 * Date: 27/05/15
 * Time: 16:24
 */
class Autocomplete extends Select {

    /**
     * HTML dom node label
     * @var string
     */
    protected $sMarkupTag = 'select';

    protected $aAttributes = array(
        'class' => array('form-control', 'ui-autocomplete')
    );

    /**
     * Select options
     * @var array
     */
    protected $aOptions = array();

    /**
     * Constructor overload to pass the select options directly at instance construction
     *
     * @param array $aOptions
     * @param array $aValidators
     */
    public function __construct(array $aOptions = array(), array $aValidators)
    {
        $this->setOptions($aOptions);
        $this->setContent($this->renderOptions());

        parent::__construct($aValidators);
    }

    /**
     * Set options
     *
     * @param array $aOptions
     * @return Select
     */
    public function setOptions(array $aOptions)
    {
        $this->aOptions = $aOptions;
        return $this;
    }

    /**
     * Set option
     *
     * @param array $aOption
     * @return $this
     */
    public function setOption(array $aOption)
    {
        $this->aOptions[] = $aOption;
        return $this;
    }

    /**
     * Option accessor
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->aOptions;
    }

    /**
     * Render slect options
     *
     * @return string
     */
    protected function renderOptions()
    {
        $sOptions = '';
        foreach ($this->aOptions as $sOptionValue => $sLabel) {
            $oOption = new SelectOption(array());
            $oOption->setAttribute('value', $sOptionValue);
            $oOption->setContent($sLabel);
            $sOptions .= $oOption->render();
        }
        return $sOptions;
    }
}