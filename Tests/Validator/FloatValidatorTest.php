<?php
namespace Library\Core\Tests\Validator;

use Library\Core\Tests\Test;
use Library\Core\Validator\FloatValidator;
use Library\Core\Validator\Validator;

class FloatValidatorTest extends Test
{
    const TEST_VALUE    = 3.123;
    const BAD_VALUE     = 'a fucking string';

    /**
     * @var Validator
     */
    protected $oValidatorInstance;

    protected function setUp()
    {
       $this->oValidatorInstance = new FloatValidator();
    }

    public function testConstructorWithParameters()
    {
        $this->oValidatorInstance = new FloatValidator(self::TEST_VALUE, 1, 4);

        $this->assertEquals(
            1,
            $this->oValidatorInstance->getRangeMin(),
            'Unable to set min range directly from constructor'
        );

        $this->assertEquals(
            4,
            $this->oValidatorInstance->getRangeMax(),
            'Unable to set max range directly from constructor'
        );

        $this->assertEquals(
            self::TEST_VALUE,
            $this->oValidatorInstance->getData(),
            'Unable to set data value directly from constructor'
        );
    }

    public function testProcess()
    {
        $this->oValidatorInstance->setData(self::TEST_VALUE);
        $this->assertEquals(
            Validator::STATUS_OK,
            $this->oValidatorInstance->process(),
            'Invalid validation should return OK status.'
        );
    }

    public function testRangeAccessor()
    {
        $this->oValidatorInstance->setRangeMin(3.123);
        $this->oValidatorInstance->setRangeMax(99.888);

        $this->assertEquals(
            3.123,
            $this->oValidatorInstance->getRangeMin(),
            'Unable to range min range property'
        );

        $this->assertEquals(
            99.888,
            $this->oValidatorInstance->getRangeMax(),
            'Unable to range max range property'
        );
    }

    public function testProcessWithOnlyMinRange()
    {
        $this->oValidatorInstance->setRangeMin(4.55)
            ->setData(3.123);

        $this->assertEquals(
            Validator::STATUS_OUT_OF_RANGE,
            $this->oValidatorInstance->process(),
            'Invalid range behavior with only min range'
        );

        $this->oValidatorInstance->setData(8.123);

        $this->assertEquals(
            Validator::STATUS_OK,
            $this->oValidatorInstance->process(),
            'Invalid range behavior with only min range'
        );
    }

    public function testOnlyMaxRange()
    {
        $this->oValidatorInstance->setRangeMax(8.551)
            ->setData(9.122);

        $this->assertEquals(
            Validator::STATUS_OUT_OF_RANGE,
            $this->oValidatorInstance->process(),
            'Invalid range behavior with only min range'
        );

        $this->oValidatorInstance->setData(7.559);

        $this->assertEquals(
            Validator::STATUS_OK,
            $this->oValidatorInstance->process(),
            'Invalid range behavior with only min range'
        );
    }

    public function testProcessWithFullRangeParameter()
    {
        $this->oValidatorInstance->setData(self::TEST_VALUE)
            ->setRangeMin(10)
            ->setRangeMax(99);

        $this->assertEquals(
            $this->oValidatorInstance->process(),
            Validator::STATUS_OUT_OF_RANGE,
            'Validation must return a out of range status code.'
        );

        # Update range
        $this->oValidatorInstance->setRangeMin(1.25);
        $this->assertEquals(
            $this->oValidatorInstance->process(),
            Validator::STATUS_OK,
            'Invalid validation should return OK status.'
        );


    }

}