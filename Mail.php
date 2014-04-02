<?php
namespace Library\Core;

/**
 * Email managment class that implement Swift mailer
 *
 * @dependancy \Library\Swift
 *
 * @author Nicolas Bonnci <nicolasbonnici@gmail.com>
 *
 */
class Mail
{
    /**
     * @var \app\Entity\Email
     */
    protected $oEmail;

    /**
     * Constructeur de la classe \kernel\Email
     * @param type $iEmailId
     * @param type $sEmail
     */
    public function __construct()
    {
        require_once ROOT_PATH . 'Library/Swift/swift_required.php';

        // @todo
        $this->oEmail = new \app\Entities\Mail();

    }

    /**
     * Methode qui envoi le/les mails
     * @param integer $iPriority
     * @return integer                  Number of recipients
     */
    public function sendEmail($iPriority = 3)
    {
        assert('isset($aParameters["email"]) && \\core\\Validator::email($aParameters["email"]) === \\core\\Validator::STATUS_OK');

        $sContent = $this->addTrackingMails($this->oEmail->idemail, $aParameters['email']);

        $oMessage = \Swift_Message::newInstance()
            ->setSubject($this->oEmail->sujet)
            ->setFrom(array('notification@bazarchic.com' => 'BazarChic'))
            ->setTo(array($aParameters['email']))
            ->setBody($sContent, 'text/html', 'utf-8')
            ->addPart($this->oEmail->corps_txt, 'text/plain')
            ->setPriority($iPriority);

        $oMailer = \Swift_Mailer::newInstance(\Swift_SendmailTransport::newInstance());
        return  $oMailer->send($oMessage);
    }

}

class MailException extends \Exception
{}
