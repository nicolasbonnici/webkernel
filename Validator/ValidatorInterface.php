<?php
namespace Library\Core\Validator;

/**
 * Interface ValidatorInterface
 * @package Library\Core\Validator
 */
interface ValidatorInterface
{
    /**
     * Validation process
     * @return int
     */
    public function process();
}