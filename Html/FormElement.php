<?php
namespace Library\Core\Html;

/**
 * HTML5 form elements abstract layer
 *
 * @see Form component
 * @author niko <nicolasbonnici@gmail.com>
 */
abstract class FormElement extends Element
{
    const DEFAULT_VALUE = '';

    /**
     * Required form element flag
     * @var bool
     */
    protected $bIsRequired = false;

    /**
     * Form element state flag
     * @var bool
     */
    protected $bIsDisabled = false;

    /**
     * Form element read/write flag
     * @var bool
     */
    protected $bIsReadOnly = false;

    /**
     * Validator instances to validate form element data integrity
     * @var array
     */
    protected $aValidators = array();

    /**
     * Form element label content
     * @var string
     */
    protected $sLabel = '';

    /**
     * Form element constructor
     * @param array $aValidators
     */
    public function __construct()
    {
        parent::__construct();

        $this->setAttribute('class', array('form-control'));

    }

    /**
     * Set form element value
     * @param mixed int|string|array $mValue
     * @return FormElement instance
     */
    public function setValue($mValue)
    {
        $this->aAttributes['value'] = $mValue;
        return $this;
    }

    /**
     * Get form element value
     * @return mixed int|string|array $mValue
     */
    public function getValue()
    {
        return (isset($this->aAttributes['value']) ? $this->aAttributes['value'] : null);
    }

    /**
     * Form element label setter
     *
     * @param $sLabel
     * @return $this
     */
    public function setLabel($sLabel)
    {
        $this->sLabel = $sLabel;
        return $this;
    }

    /**
     * Form element label getter
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->sLabel;
    }

    /**
     * Tell if the setted value is valid using validators array setted at instance constructor
     * @return bool
     */
    public function isValid()
    {
        $aValidation = array();
        $aValidators = $this->getValidators();
        foreach ($aValidators as $oValidator) {
            $aValidation[] = $oValidator->process();
        }
        return (bool) (in_array(false, $aValidation) === false);
    }

    /**
     * Set element required
     *
     * @param bool $bIsRequired
     * @return FormElement instance
     */
    final public function setRequired($bIsRequired)
    {
        $this->bIsRequired = (bool) $bIsRequired;
        return $this;
    }

    /**
     * Set form element disable
     *
     * @param bool $bIsDisable
     * @return FormElement instance
     */
    final public function setDisabled($bIsDisable)
    {
        $this->bIsDisabled = (bool) $bIsDisable;
        return $this;
    }

    /**
     * Set form element read/write status
     *
     * @param bool $bIsDisable
     * @return FormElement instance
     */
    final public function setReadOnly($bIsReadOnly)
    {
        $this->bIsReadOnly = (bool) $bIsReadOnly;
        return $this;
    }

    /**
     * Tell if the FormElement is required
     * @return bool
     */
    final public function isRequired()
    {
        return ($this->bIsRequired === true);
    }

    /**
     * Tell if the FormElement is disabled
     * @return bool
     */
    final public function isDisabled()
    {
        return ($this->bIsDisabled === true);
    }

    /**
     * Tell if the FormElement is on readonly mode
     * @return bool
     */
    final public function isReadOnly()
    {
        return ($this->bIsReadOnly === true);
    }

    /**
     * @return array
     */
    public function getValidators()
    {
        return $this->aValidators;
    }

    /**
     * @param array $aValidators
     */
    public function setValidators(array $aValidators)
    {
        $this->aValidators = $aValidators;
    }



}