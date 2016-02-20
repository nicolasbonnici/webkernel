<?php
namespace Library\Core\Tests\Log;

use Library\Core\Log\Log;
use Library\Core\Tests\Test;

class LogTest extends Test
{
    private $aStack = array();

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->aStack = debug_backtrace();
    }


    public function testLogAccessors()
    {
        $oLog = new Log();
        $oDateTime = new \DateTime();
        $oLog->setMessage('Houston we got a problem...');
        $oLog->setErrorCode(13);
        $oLog->setStackTrace($this->aStack);
        $oLog->setType(Log::TYPE_INFO);
        $oLog->setDatetime($oDateTime);

        $this->assertEquals(
            'Houston we got a problem...',
            $oLog->getMessage(),
            'Unable to set log message'
        );

        $this->assertEquals(
            13,
            $oLog->getErrorCode(),
            'Unable to set log error code'
        );

        $this->assertEquals(
            $this->aStack,
            $oLog->getStackTrace(),
            'Unable to set log stack trace'
        );

        $this->assertEquals(
            Log::TYPE_INFO,
            $oLog->getType(),
            'Unable to set log type'
        );

        $this->assertInstanceOf(
            get_class($oDateTime),
            $oLog->getDatetime(),
            'Unable to get log Datetime object'
        );

    }

    public function testStoreLog()
    {
        $oLog = new Log();
        $oDateTime = new \DateTime();
        $oLog->setMessage('Houston we got a problem...');
        $oLog->setErrorCode(13);
        $oLog->setStackTrace($this->aStack);
        $oLog->setType(Log::TYPE_INFO);
        $oLog->setDatetime($oDateTime);

        $this->assertTrue(
            $oLog->create($oLog),
            'Unable to store a log type info'
        );
    }

}