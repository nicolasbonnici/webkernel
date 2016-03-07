<?php
namespace Library\Core\Validator;

/**
 * Class StringValidator
 * @package Library\Core\Validator
 */
class StringValidator extends Validator
{
    /**
     * Data validation type
     */
    const DATA_TYPE = 'string';

    /**
     * The validation check method
     * @return int
     */
    protected function check()
    {
        if (is_string($this->getData()) === false) {
            return self::STATUS_INVALID;
        }

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


        return self::STATUS_OK;
    }

}