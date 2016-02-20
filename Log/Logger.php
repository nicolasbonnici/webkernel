<?php
namespace Library\Core\Log;


use Library\Core\FileSystem\Directory;
use Library\Core\FileSystem\File;

class Logger extends LoggerAbstract implements LoggerInterface
{

    protected function storeLog(Log $oLog)
    {
        $sLogPath = $this->getLogsPath();
        if (Directory::exists($sLogPath) === false) {
            throw new LoggerException(
                LoggerException::$aErrors[LoggerException::ERROR_ROOT_LOG_PATH_NOT_FOUND],
                LoggerException::ERROR_ROOT_LOG_PATH_NOT_FOUND
            );
        }

        $sLogFilePath = $sLogPath . $oLog->getType() . LoggerAbstract::LOG_FILE_EXTENSION;

        if (File::exists($sLogFilePath) === false) {
            if (File::create($sLogFilePath) === false) {
                throw new LoggerException(
                    sprintf(
                        LoggerException::$aErrors[LoggerException::ERROR_ON_CREATE_LOG_FILE],
                        $sLogFilePath
                    ),
                    LoggerException::ERROR_ON_CREATE_LOG_FILE
                );
            }
        }

        return File::write($sLogFilePath, File::getContent($sLogFilePath) . $oLog->__toString());
    }

}

class LoggerException extends \Exception {
    const ERROR_ROOT_LOG_PATH_NOT_FOUND = 2;
    const ERROR_ON_CREATE_LOG_FILE      = 3;

    public static $aErrors = array(
        self::ERROR_ROOT_LOG_PATH_NOT_FOUND => 'Log root path not found.',
        self::ERROR_ON_CREATE_LOG_FILE      => 'Unable to create "%s" log file'
    );
}