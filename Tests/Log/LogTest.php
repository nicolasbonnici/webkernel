<?php
namespace Library\Core\Tests\Log;

use Library\Core\Bootstrap;
use Library\Core\FileSystem\File;
use Library\Core\Log\Log;
use Library\Core\Log\LoggerAbstract;
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
        $this->aStack = array(
            get_called_class(),
            'phpunit'
        );
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
        $oLog->setDatetime($oDateTime);

        # Try to store for each log types
        foreach ($oLog->getTypes() as $sType) {
            $oLog->setType($sType);

            $this->assertTrue(
                $oLog->create($oLog),
                'Unable to store a log type: ' . $sType
            );

            $sLogTypePath = $oLog->getLoggerInstance()->getLogsPath() . $sType . LoggerAbstract::LOG_FILE_EXTENSION;

            $this->assertTrue(
                File::exists($sLogTypePath),
                'Unable to found a file for log type ' . $sType
            );

            $sLogContent = File::getContent($sLogTypePath);

            $this->assertNotEmpty(
                $sLogContent,
                'Empty log found for type ' . $sType
            );

            $this->assertNotFalse(
                strstr($sLogContent, $oLog->getMessage()),
                'Unable to find log message under log file'
            );

            $this->assertNotFalse(
                strstr($sLogContent, '13'),
                'Unable to find log error code under log file'
            );

            $this->assertNotFalse(
                strstr($sLogContent, $oLog->getDatetime()->format(Bootstrap::DEFAULT_DATE_FORMAT)),
                'Unable to find log datetime under log file'
            );
        }

    }

}