<?php
namespace Library\Core\Notification\Email;

use Library\Core\Notification\NotificationAbstract;
use Library\Core\Notification\SenderAbstract;
use Library\Core\Notification\SenderInterface;

/**
 * Email management email sender class that use Swift mailer
 *
 * @dependancy \Library\Swift
 * @author Nicolas Bonnci <nicolasbonnici@gmail.com>
 *
 */
class EmailSender implements SenderInterface
{

    /**
     * Mailer
     * @var \Swift_Mailer
     */
    protected $oMailer;

    /**
     *
     * @var \Swift_Transport
     */
    protected $oTransporter;

    /**
     * Mailer instance constructor
     * @param \Swift_Transport $oTransporter
     */
    public function __construct($oTransporter = null)
    {
        require_once ROOT_PATH . 'Library/Swift/swift_required.php';

        if (is_null($oTransporter) === false && $oTransporter instanceof \Swift_Transport) {
            $this->oTransporter = $oTransporter;
        } else {
            $this->oTransporter = \Swift_SendmailTransport::newInstance();
        }

        $this->oMailer = new \Swift_Mailer($this->oTransporter);

    }

    /**
     * Send an email
     *
     * @param NotificationAbstract $oMessage
     * @return bool
     *
     * (non-PHPdoc)
     * @see Swift_Mailer::send()
     */
    public function send(NotificationAbstract $oMessage)
    {
        return  ($this->oMailer->send($oMessage->build()) > 0);
    }

}

class MailerException extends \Exception
{}
