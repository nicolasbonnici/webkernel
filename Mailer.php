<?php
namespace Library\Core;

/**
 * Email managment class that implement Swift mailer
 * Dois etre instancier avant le message
 *
 * @dependancy \Library\Swift
 * @author Nicolas Bonnci <nicolasbonnici@gmail.com>
 *
 */
class Mailer
{

    /**
     * Message priority
     * @var integer
     */
    const PRIORITY_HIGHEST  = 1;
    const PRIORITY_HIGH     = 2;
    const PRIORITY_NORMAL   = 3;
    const PRIORITY_LOW      = 4;
    const PRIORITY_LOWEST   = 5;

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
     * @var \Swift_Message
     */
    protected $oMessage;

    /**
     * Constructeur de la classe \kernel\Email
     * @param type $iEmailId
     * @param type $sEmail
     */
    public function __construct($oTransporter = null)
    {
        require_once ROOT_PATH . 'Library/Swift/swift_required.php';

        if (! is_null($oTransporter) && $oTransporter instanceof \Swift_Transport) {
            $this->oTransporter = $oTransporter;
        } else {
            $oTransporter = \Swift_SendmailTransport::newInstance();
        }

        $this->oMailer = new \Swift_Mailer($oTransporter);

    }

    /**
     * Send an email
     *
     * @param \Swift_Message $oMessage
     * @return integer                  Number of recipients
     *
     * (non-PHPdoc)
     * @see Swift_Mailer::send()
     */
    public function send(\Swift_Message $oMessage)
    {
        return  $this->oMailer->send($oMessage);
    }

}

class MailerException extends \Exception
{}
