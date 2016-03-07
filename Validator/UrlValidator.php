<?php
namespace Library\Core\Validator;

/**
 * Class UrlValidator
 * @package Library\Core\Validator
 */
class UrlValidator extends Validator
{
    /**
     * Data validation type
     */
    const DATA_TYPE = 'url';

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

        // This is not perfect regex (domain extension may not contain only letters for example)
        return (preg_match('#^https?://[a-z]+[a-z0-9.-]+\.[a-z]{2,6}#i', $this->getData()) === 1)
            ? self::STATUS_OK
            : self::STATUS_INVALID;
    }

}