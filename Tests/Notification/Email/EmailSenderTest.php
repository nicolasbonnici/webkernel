<?php
namespace Library\Core\Tests\Notification\Email;

use Library\Core\Test;

use Library\Core\Notification\Email\EmailNotification;
use Library\Core\Notification\Email\EmailSender;

class EmailSenderTest extends Test {

    /**
     * @var EmailSender
     */
    protected $oEmailSenderInstance;

    /**
     * @var EmailNotification
     */
    protected $oEmailNotificationInstance;

    protected function setUp()
    {
        $this->oEmailSenderInstance = new EmailSender();

        parent::setUp();
    }


    public function testSend()
    {
        $this->oEmailNotificationInstance = new EmailNotification();

        $this->oEmailNotificationInstance->setSubject('Test subject');
        $this->oEmailNotificationInstance->setExpeditor('nicolas.bonnici@gmail.com');
        $this->oEmailNotificationInstance->setRecipient('nicolasbonnici@gmail.com');
        $this->oEmailNotificationInstance->setMessage('Message de test.');
        $this->assertTrue(
            $this->oEmailSenderInstance->send($this->oEmailNotificationInstance),
            'Unable to send a Mail Notification.'
        );
    }

}