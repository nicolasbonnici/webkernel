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

    public function __construct()
    {
        require_once ROOT_PATH . 'Library/Swift/swift_required.php';
    }

    /**
     * Build the email for Swift Mailer
     *
     * @param string $sContentType
     * @return \Swift_Message
     */
    public function build($sContentType = 'text/html')
    {
        $oMessage = new \Swift_Message();

        $oMessage->setSubject($this->getSubject())
            ->setTo($this->getRecipient())
            ->setFrom($this->getExpeditor())
            ->setPriority($this->getPriority())
            ->setBody($this->getMessage(), $sContentType);

        # Cc field
        $aCc = $this->getCopyRecipients();
        if (is_array($aCc) === true && count($aCc) > 0) {
            $oMessage->setCc($aCc);
        }

        return $oMessage;
    }

}

class MailException extends \Exception
{}
