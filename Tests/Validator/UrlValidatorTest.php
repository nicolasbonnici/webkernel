<?php
namespace Library\Core\Tests\Validator;

use Library\Core\Tests\Test;
use Library\Core\Validator\UrlValidator;
use Library\Core\Validator\Validator;

class UrlValidatorTest extends Test
{
    const TEST_VALUE    = 'http://domain.tld/us/someurl/';
    const BAD_VALUE     = 'dumb.com';

    /**
     * @var Validator
     */
    protected $oValidatorInstance;

    protected function setUp()
    {
       $this->oValidatorInstance = new UrlValidator();
    }

    public function testConstructorWithParameters()
    {
        $this->oValidatorInstance = new UrlValidator(self::TEST_VALUE, 5, 99);

        $this->assertEquals(
            5,
            $this->oValidatorInstance->getRangeMin(),
            'Unable to set min range directly from constructor'
        );

        $this->assertEquals(
            99,
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
        $this->oValidatorInstance->setData(self::BAD_VALUE);
        $this->assertEquals(
            Validator::STATUS_INVALID,
            $this->oValidatorInstance->process(),
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
        $this->oValidatorInstance->setRangeMin(20)
            ->setData('http://tco.fr');

        $this->assertEquals(
            Validator::STATUS_OUT_OF_RANGE,
            $this->oValidatorInstance->process(),
            'Invalid range behavior with only min range'
        );

        $this->oValidatorInstance->setRangeMin(3);

        $this->assertEquals(
            Validator::STATUS_OK,
            $this->oValidatorInstance->process(),
            'Invalid range behavior with only min range'
        );
    }

    public function testOnlyMaxRange()
    {
        $this->oValidatorInstance->setRangeMax(3)
            ->setData('http://tco.fr');

        $this->assertEquals(
            Validator::STATUS_OUT_OF_RANGE,
            $this->oValidatorInstance->process(),
            'Invalid range behavior with only min range'
        );

        $this->oValidatorInstance->setRangeMax(99);

        $this->assertEquals(
            Validator::STATUS_OK,
            $this->oValidatorInstance->process(),
            'Invalid range behavior with only min range'
        );
    }

    public function testProcessWithFullRangeParameter()
    {
        $this->oValidatorInstance->setData(self::TEST_VALUE)
            ->setRangeMin(1)
            ->setRangeMax(3);

        $this->assertEquals(
            Validator::STATUS_OUT_OF_RANGE,
            $this->oValidatorInstance->process(),
            'Validation must return a out of range status code.'
        );

        # Update range
        $this->oValidatorInstance->setRangeMax(99);
        $this->assertEquals(
            Validator::STATUS_OK,
            $this->oValidatorInstance->process(),
            'Invalid validation should return OK status.'
        );


    }

}