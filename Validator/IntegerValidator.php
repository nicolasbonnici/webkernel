<?php
namespace Library\Core\Validator;

/**
 * Class IntegerValidator
 * @package Library\Core\Validator
 */
class IntegerValidator extends Validator
{
    /**
     * Data validation type
     */
    const DATA_TYPE = 'integer';

    /**
     * The validation check method
     * @return int
     */
    protected function check()
    {
        # Check for type issue
        if (is_numeric($this->getData()) === false || $this->getData() != (int) $this->getData()) {
            return self::STATUS_INVALID;
        }

        # Check for range if available
        if ($this->hasMinRange() === true && $this->getData() < $this->getRangeMin()) {
            return self::STATUS_OUT_OF_RANGE;
        }

        if ($this->hasMaxRange() === true && $this->getData() > $this->getRangeMax()) {
            return self::STATUS_OUT_OF_RANGE;
        }

        # Cast explicitly onto integer
        $this->setData((int) $this->getData());

        return self::STATUS_OK;
    }

}