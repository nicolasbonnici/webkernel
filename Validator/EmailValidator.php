<?php
namespace Library\Core\Validator;

/**
 * Class EmailValidator
 * @package Library\Core\Validator
 */
class EmailValidator extends Validator
{
    /**
     * Data validation type
     */
    const DATA_TYPE = 'email';

    /**
     * The validation check method
     * @return int
     */
    protected function check()
    {
        if ($this->hasMinRange() === true) {
            if (is_null($this->getRangeMin()) === false && strlen($this->getData()) < $this->getRangeMin()) {
                return self::STATUS_OUT_OF_RANGE;
            }
        }

        if ($this->hasMaxRange() === true) {
            if (is_null($this->getRangeMax()) === false && strlen($this->getData()) > $this->getRangeMax()) {
                return self::STATUS_OUT_OF_RANGE;
            }
        }

        return (filter_var($this->getData(), FILTER_VALIDATE_EMAIL) !== false)
            ? self::STATUS_OK
            : self::STATUS_INVALID;
    }

}