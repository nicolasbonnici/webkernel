<?php
namespace Library\Core\Log;


use Library\Core\Bootstrap;
use Library\Core\FileSystem\File;
use Library\Core\FileSystem\FileSystem;

abstract class LoggerAbstract
{

    const LOG_FILE_EXTENSION = '.log';

    /**
     * Absolute path to store logs (default configuration in config.ini)
     * @var string
     */
    protected $sLogsPath;

    public function __construct()
    {
        $this->sLogsPath = Bootstrap::getPath(Bootstrap::PATH_TMP_LOGS);
    }

    /**
     * @param Log $oLog
     * @return bool
     */
    public function store(Log $oLog)
    {
        try {
            # We need at least a type and a message in order to log something
            if ($oLog->checkType() === true && empty($oLog->getMessage()) === false) {
                return $this->storeLog($oLog);
            }
            return false;
        } catch(\Exception $oException) {
            return false;
        }
    }

    /**
     * The computed absolute log file path for a given Log type
     *
     * @see Log
     *
     * @param string $sType
     * @return string
     * @throws LoggerException
     */
    protected function getLogFile($sType)
    {
        $sLogFilePath = $this->sLogsPath . FileSystem::DS . $sType . self::LOG_FILE_EXTENSION;
        if (File::exists($sLogFilePath) === false && File::create($sLogFilePath) === false) {
            throw new LoggerException(
                sprintf(
                    LoggerException::$aErrors[LoggerException::ERROR_ON_CREATE_LOG_FILE],
                    $sLogFilePath
                ),
                LoggerException::ERROR_ON_CREATE_LOG_FILE
            );
        }

        return $sLogFilePath;
    }

    /**
     * Store the log
     * @return bool
     */
    abstract protected function storeLog(Log $oLog);

    /**
     * Get the absolute logs root path
     * @return string
     */
    public function getLogsPath()
    {
        return $this->sLogsPath;
    }

}