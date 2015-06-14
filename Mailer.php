<?php
namespace Core;

/**
 * Email managment class that implement Swift mailer
 * Dois etre instancier avant le message
 *
 * @dependancy \Swift
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
     * Mailer instance constructor
     * @param type $iEmailId
     * @param type $sEmail
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
