<?php
namespace Library\Core;

/**
 * HTML5 form elements abstract layer
 *
 * @see Form component
 * @author niko <nicolasbonnici@gmail.com>
 */
abstract class FormElement
{
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
     * Validator instances to validate form element data integrity
     * @var array
     */
    protected $aValidators = array();


    /**
     * Form element constructor
     * @param array $aValidators
     */
    public function __construct(array $aValidators = array())
    {
        $this->aValidators = $aValidators;
    }

    /**
     * Build form element HTML markup
     * @return string
     */
    abstract public function render();

    /**
     * Set form element value
     * @param mixed int|string|array $mValue
     * @return FormElement instance
     */
    abstract public function setValue($mValue);

    /**
     * Get form element value
     * @return mixed int|string|array $mValue
     */
    abstract public function getValue();

    /**
     * Tell if the setted value is valid using validators array setted at instance constructor
     * @return bool
     */
    abstract public function isValid();

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
    final public function setDisable($bIsDisable)
    {
        $this->bIsDisabled = (bool) $bIsDisable;
        return $this;
    }

}