<?php
namespace Library\Core\Validator;

/**
 * Class FloatValidator
 * @package Library\Core\Validator
 */
class FloatValidator extends Validator
{
    /**
     * Data validation type
     */
    const DATA_TYPE = 'float';

    /**
     * The validation check method
     * @return int
     */
    protected function check()
    {
        # Check for type issue
        if (is_numeric($this->getData())  === false || $this->getData() != (float) $this->getData()) {
            return self::STATUS_INVALID;
        }

        if ($this->hasMinRange() === true && $this->getData() < $this->getRangeMin()) {
            return self::STATUS_OUT_OF_RANGE;
        }

        if ($this->hasMaxRange() === true && $this->getData() > $this->getRangeMax()) {
            return self::STATUS_OUT_OF_RANGE;
        }

        # Cast onto float
        $this->setData((float) $this->getData());

        return self::STATUS_OK;
    }

}