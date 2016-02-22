<?php
namespace Library\Core\Log;

use Library\Core\Bootstrap;

abstract class LogAbstract
{
    /**
     * Log types
     */
    const TYPE_INFO         = 'info';
    const TYPE_ERROR        = 'error';
    const TYPE_EXCEPTION    = 'exception';
    const TYPE_FATAL        = 'fatal';

    /**
     * Allowed log types scope
     * @var array
     */
    protected $aTypes = array(
        self::TYPE_INFO,
        self::TYPE_ERROR,
        self::TYPE_EXCEPTION,
        self::TYPE_FATAL
    );

    /**
     * Log type
     * @var string
     */
    protected $sType;

    /**
     * Exception or error message
     * @var string
     */
    protected $sMessage;

    /**
     * Exception or error code (if available)
     * @var int
     */
    protected $iErrorCode = null;

    /**
     * The call stack
     * @var array
     */
    protected $aStackTrace = array();

    /**
     * The log date information
     * @var \Datetime
     */
    protected $oDatetime = null;

    /**
     * Logger instance
     * @var Logger
     */
    protected $oLoggerInstance = null;

    public function __construct()
    {
        # Set Logger instance
        $this->oLoggerInstance = new Logger();
    }

    /**
     * Store a Log object directly on log file
     * @return bool
     */
    public function create()
    {
        if (is_null($this->getLoggerInstance()) === false) {
            return $this->getLoggerInstance()->store($this->get());
        }
        return false;
    }

    /**
     * The __toString method allows a class to decide how it will react when it is converted to a string.
     *
     * @return string
     * @link http://php.net/manual/en/language.oop5.magic.php#language.oop5.magic.tostring
     */
    function __toString()
    {
        $sLog = 'Type: ' .$this->getType()
            . ' | Datetime: ' . $this->getDatetime()->format(Bootstrap::DEFAULT_DATE_FORMAT)
            . ' | Message: ' . $this->getMessage()
            . ' | Code: ' . $this->getErrorCode()
            . ' | Stack trace: ';
        $sLog .= serialize($this->getStackTrace()) . "\n";
        return $sLog;
    }


    /**
     * Check the log type
     * @return bool
     */
    public function checkType()
    {
        $sType = $this->getType();
        return (bool) (empty($sType) === false && in_array($sType, $this->aTypes) === true);
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->sType;
    }

    /**
     * @param string $sType
     */
    public function setType($sType)
    {
        $this->sType = $sType;
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->sMessage;
    }

    /**
     * @param mixed $sMessage
     */
    public function setMessage($sMessage)
    {
        $this->sMessage = $sMessage;
    }

    /**
     * @return null
     */
    public function getErrorCode()
    {
        return $this->iErrorCode;
    }

    /**
     * @param null $iErrorCode
     */
    public function setErrorCode($iErrorCode)
    {
        $this->iErrorCode = $iErrorCode;
    }

    /**
     * @return array
     */
    public function getStackTrace()
    {
        return $this->aStackTrace;
    }

    /**
     * @param array $aStackTrace
     */
    public function setStackTrace($aStackTrace)
    {
        $this->aStackTrace = $aStackTrace;
    }

    /**
     * @return \Datetime
     */
    public function getDatetime()
    {
        return $this->oDatetime;
    }

    /**
     * @param mixed $oDatetime
     */
    public function setDatetime(\Datetime $oDatetime)
    {
        $this->oDatetime = $oDatetime;
    }

    /**
     * @return Logger
     */
    public function getLoggerInstance()
    {
        return $this->oLoggerInstance;
    }

    /**
     * @param Logger $oLoggerInstance
     */
    public function setLoggerInstance(Logger $oLoggerInstance)
    {
        $this->oLoggerInstance = $oLoggerInstance;
    }

    /**
     * Get allowed log types scope
     * @return array
     */
    public function getTypes()
    {
        return $this->aTypes;
    }
}