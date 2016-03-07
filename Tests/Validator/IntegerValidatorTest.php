<?php
namespace Library\Core\Tests\Validator;

use Library\Core\Tests\Test;
use Library\Core\Validator\IntegerValidator;
use Library\Core\Validator\Validator;

class IntegerValidatorTest extends Test
{
    const TEST_VALUE    = 3;
    const BAD_VALUE     = 'a fucking string';

    /**
     * @var Validator
     */
    protected $oValidatorInstance;

    protected function setUp()
    {
       $this->oValidatorInstance = new IntegerValidator();
    }

    public function testConstructorWithParameters()
    {
        $this->oValidatorInstance = new IntegerValidator(self::TEST_VALUE, 1, 4);

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
            $this->oValidatorInstance->process(),
            Validator::STATUS_OK,
            'Invalid validation should return OK status.'
        );
    }

    public function testRangeAccessor()
    {
        $this->oValidatorInstance->setRangeMin(3);
        $this->oValidatorInstance->setRangeMax(99);

        $this->assertEquals(
            3,
            $this->oValidatorInstance->getRangeMin(),
            'Unable to range min range property'
        );

        $this->assertEquals(
            99,
            $this->oValidatorInstance->getRangeMax(),
            'Unable to range max range property'
        );
    }

    public function testProcessWithOnlyMinRange()
    {
        $this->oValidatorInstance->setRangeMin(5)
            ->setData(4);

        $this->assertEquals(
            Validator::STATUS_OUT_OF_RANGE,
            $this->oValidatorInstance->process(),
            'Invalid range behavior with only min range'
        );

        $this->oValidatorInstance->setData(8);

        $this->assertEquals(
            Validator::STATUS_OK,
            $this->oValidatorInstance->process(),
            'Invalid range behavior with only min range'
        );
    }

    public function testOnlyMaxRange()
    {
        $this->oValidatorInstance->setRangeMax(55)
            ->setData(99);

        $this->assertEquals(
            Validator::STATUS_OUT_OF_RANGE,
            $this->oValidatorInstance->process(),
            'Invalid range behavior with only min range'
        );

        $this->oValidatorInstance->setData(44);

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
            Validator::STATUS_OUT_OF_RANGE,
            $this->oValidatorInstance->process(),
            'Validation must return a out of range status code.'
        );

        # Update range
        $this->oValidatorInstance->setRangeMin(1);
        $this->assertEquals(
            Validator::STATUS_OK,
            $this->oValidatorInstance->process(),
            'Invalid validation should return OK status.'
        );


    }

}