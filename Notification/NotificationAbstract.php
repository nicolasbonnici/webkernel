<?php
namespace Library\Core\Notification;


abstract class NotificationAbstract {

    /**
     * Message priority
     * @var integer
     */
    const PRIORITY_HIGHEST  = 1;
    const PRIORITY_HIGH     = 2;
    const PRIORITY_NORMAL   = 3;
    const PRIORITY_LOW      = 4;
    const PRIORITY_LOWEST   = 5;
    const MIN_PRIORITY      = self::PRIORITY_HIGHEST;
    const MAX_PRIORITY      = self::PRIORITY_LOWEST;

    /**
     * Notification recipient
     * @var string
     */
    private $sRecipient;

    /**
     * From expeditor
     * @var string
     */
    private $sExpeditor;

    /**
     * Notification Subject
     * @var string
     */
    private $sSubject;

    /**
     * Message
     * @var string
     */
    private $sMessage;

    /**
     * Notification priority
     * @var int
     */
    private $iPriority = self::PRIORITY_HIGHEST;

    /**
     * Return the built notification for Sender instance
     * @return mixed
     */
    abstract public function build();

    /**
     * @return mixed array|string
     */
    public function getRecipient()
    {
        return $this->sRecipient;
    }

    /**
     * @param mixed array|string $sRecipient
     */
    public function setRecipient($sRecipient)
    {
        $this->sRecipient = $sRecipient;
        return $this;
    }

    /**
     * @return mixed array|string
     */
    public function getExpeditor()
    {
        return $this->sExpeditor;
    }

    /**
     * @param mixed array|string $sExpeditor
     */
    public function setExpeditor($sExpeditor)
    {
        $this->sExpeditor = $sExpeditor;
        return $this;
    }

    /**
     * @return string
     */
    public function getSubject()
    {
        return $this->sSubject;
    }

    /**
     * @param string $sSubject
     */
    public function setSubject($sSubject)
    {
        $this->sSubject = $sSubject;
        return $this;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->sMessage;
    }

    /**
     * @param string $sMessage
     */
    public function setMessage($sMessage)
    {
        $this->sMessage = $sMessage;
        return $this;
    }

    /**
     * @return int
     */
    public function getPriority()
    {
        return $this->iPriority;
    }

    /**
     * @param int $iPriority
     */
    public function setPriority($iPriority)
    {
        if ($iPriority >= self::MIN_PRIORITY && $iPriority <= self::MAX_PRIORITY) {
            $this->iPriority = $iPriority;
            return $this;
        }
        return false;
    }
}