<?php
namespace Library\Core\Notification;


abstract class SenderAbstract implements SenderInterface {

    /**
     * @var NotificationAbstract
     */
    protected $oMessage;

    /**
     * @return NotificationAbstract
     */
    public function getMessage()
    {
        return $this->oMessage;
    }

    /**
     * @param NotificationAbstract
     */
    public function setMessage(NotificationAbstract $oMessage)
    {
        $this->oMessage = $oMessage;
        return $this;
    }

}