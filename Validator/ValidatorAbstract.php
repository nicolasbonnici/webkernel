<?php
namespace Library\Core\Validator;

/**
 * Class ValidatorAbstract
 * @package Library\Core\Validator
 */
abstract class ValidatorAbstract implements ValidatorInterface
{

    /**
     * Validation status
     * @var integer
     */
    const STATUS_OK                 = 2;
    const STATUS_INVALID            = 3;
    const STATUS_OUT_OF_RANGE       = 4;
    const STATUS_EMPTY_MANDATORY    = 5;
    const STATUS_ALREADY_EXISTS     = 6;

    /**
     * Process the validator check method
     * @return int
     */
    public function process()
    {
        return $this->check();
    }

    /**
     * The validation check method
     * @return int
     */
    protected abstract function check();
}