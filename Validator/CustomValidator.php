<?php
namespace Library\Core\Validator;

/**
 * Class CustomValidator
 * @package Library\Core\Validator
 */
class CustomValidator extends Validator
{
    /**
     * Data validation type
     */
    const DATA_TYPE = 'custom';

    /**
     * Used regular expression to validate data
     * @var string
     */
    protected $sRegularExpression = '';

    /**
     * The validation check method
     * @return int
     */
    protected function check()
    {
        return (preg_match($this->getRegularExpression(), $this->getData()) === 1)
            ? self::STATUS_OK
            : self::STATUS_INVALID;

    }

    /**
     * @return string
     */
    public function getRegularExpression()
    {
        return $this->sRegularExpression;
    }

    /**
     * @param string $sRegularExpression
     */
    public function setRegularExpression($sRegularExpression)
    {
        $this->sRegularExpression = $sRegularExpression;
    }


}