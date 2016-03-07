<?php
namespace Library\Core\Tests\Validator;

use Library\Core\Tests\Test;
use Library\Core\Validator\CustomValidator;
use Library\Core\Validator\Validator;

class CustomValidatorTest extends Test
{
    const TEST_REGEXP   = '/^[0-9]{1,3}-[a-z]{1,16}$/i';
    const TEST_VALUE    = '1-string';
    const TEST_UPPER    = '1-STRING';
    const BAD_VALUE     = 'sds-666';
    const WEIRD_VALUE   = '999-jdjdjddjzajhdajzhdadjh';

    /**
     * @var CustomValidator
     */
    protected $oValidatorInstance;

    protected function setUp()
    {
       $this->oValidatorInstance = new CustomValidator(self::TEST_VALUE);
    }

    public function testConstructorWithParameters()
    {
        $this->oValidatorInstance = new CustomValidator(self::TEST_VALUE);

        $this->assertEquals(
            self::TEST_VALUE,
            $this->oValidatorInstance->getData(),
            'Unable to set data value directly from constructor'
        );
    }

    public function testProcess()
    {
        $this->oValidatorInstance->setRegularExpression(self::TEST_REGEXP);

        $this->assertEquals(
            Validator::STATUS_OK,
            $this->oValidatorInstance->process(),
            'Invalid validation should return OK status.'
        );

        $this->oValidatorInstance->setData(self::TEST_UPPER);

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

        $this->oValidatorInstance->setData(self::WEIRD_VALUE);

        $this->assertEquals(
            Validator::STATUS_INVALID,
            $this->oValidatorInstance->process(),
            'Invalid validation should return OK status.'
        );
    }

    public function testRegexpSetter()
    {
        $this->oValidatorInstance->setRegularExpression(self::TEST_REGEXP);
        $this->assertEquals(
            self::TEST_REGEXP,
            $this->oValidatorInstance->getRegularExpression(),
            'Unable to retrieve regular expression setted at construct.'
        );

    }
}