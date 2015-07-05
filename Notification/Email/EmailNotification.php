<?php
namespace Library\Core\Notification\Email;

use Library\Core\Notification\NotificationAbstract;

/**
 * Email wrapper class
 *
 * @dependancy \Library\Swift
 * @author Nicolas Bonnci <nicolasbonnici@gmail.com>
 *
 */
class EmailNotification extends NotificationAbstract
{

    protected $oMessage;

    /**
     * Build the email for Swift Mailer
     *
     * @return \Swift_Message
     */
    public function build()
    {
        $oMessage = new \Swift_Message();

        $oMessage->setSubject($this->getSubject());
        $oMessage->setTo($this->getRecipient());
        $oMessage->setFrom($this->getExpeditor());
        $oMessage->setBody($this->getMessage());
        $oMessage->setPriority($this->getPriority());

        return $oMessage;
    }

}

class MailException extends \Exception
{}
