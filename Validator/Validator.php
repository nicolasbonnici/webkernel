<?php
namespace Library\Core\Validator;

abstract class Validator extends ValidatorAbstract
{
    /**
     * The data to validate
     * @var mixed
     */
    protected $mData;

    /**
     * Data range minimum parameter (optional)
     * @var int
     */
    protected $iRangeMin = null;

    /**
     * Data range maximum parameter (optional)
     * @var int
     */
    protected $iRangeMax = null;

    /**
     * Validator constructor.
     * @param $mData
     */
    public function __construct($mData = null, $iRangeMin = null, $iRangeMax = null)
    {
        if (is_null($mData) === false) {
            $this->setData($mData);
        }

        if (is_null($iRangeMin) === false) {
            $this->setRangeMin($iRangeMin);
        }

        if (is_null($iRangeMax) === false) {
            $this->setRangeMax($iRangeMax);
        }
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->mData;
    }

    /**
     * @param mixed $mData
     * @return Validator
     */
    public function setData($mData)
    {
        $this->mData = $mData;
        return $this;
    }

    /**
     * Tell if the minimum range parameter is used
     * @return bool
     */
    public function hasMinRange()
    {
        return (bool) (is_null($this->getRangeMin()) === false);
    }

    /**
     * Tell if the maximum range parameter is used
     * @return bool
     */
    public function hasMaxRange()
    {
        return (bool) (is_null($this->getRangeMax()) === false);
    }

    /**
     * Get minimum range value
     * @return int
     */
    public function getRangeMin()
    {
        return $this->iRangeMin;
    }

    /**
     * @param int $iRangeMin
     * @return Validator
     */
    public function setRangeMin($iRangeMin)
    {
        $this->iRangeMin = $iRangeMin;
        return $this;
    }

    /**
     * Get maximum range value
     * @return int
     */
    public function getRangeMax()
    {
        return $this->iRangeMax;
    }

    /**
     * @param int $iRangeMax
     * @return Validator
     */
    public function setRangeMax($iRangeMax)
    {
        $this->iRangeMax = $iRangeMax;
        return $this;
    }

    /**
     * Return validation type
     * @return string
     */
    public function getValidatorType()
    {
        return static::DATA_TYPE;
    }

}