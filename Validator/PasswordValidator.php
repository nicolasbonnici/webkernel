<?php
namespace Library\Core\Validator;

/**
 * Class PasswordValidator
 * @package Library\Core\Validator
 */
class PasswordValidator extends StringValidator
{
    /**
     * Data validation type
     */
    const DATA_TYPE = 'password';

    /**
     * The validation check method
     * @return int
     */
    protected function check()
    {
        # Set default password range parameter
        if ($this->hasMinRange() === false) {
            $this->setRangeMin(4);
        }

        if ($this->hasMaxRange() === false) {
            $this->setRangeMax(255);
        }

        # @see StringValidator
        return parent::check();
    }

}