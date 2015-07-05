<?php
namespace Library\Core\Tests\Notification\Email;

use Library\Core\Test;

use Library\Core\Notification\Email\EmailNotification;

class EmailNotificationTest extends Test {

    /**
     * @var EmailNotification
     */
    protected $oEmailNotificationInstance;

    protected function setUp()
    {
        $this->oEmailNotificationInstance = new EmailNotification();

        parent::setUp();
    }


    public function testAccessors()
    {
        $aInstanceAccessors = $this->getAccessors($this->oEmailNotificationInstance);
        foreach ($aInstanceAccessors['setter'] as $sSetterMethod) {
            $this->assertInstanceOf(
                '\Library\Core\Notification\Email\EmailNotification',
                $this->oEmailNotificationInstance->{$sSetterMethod}(1)
            );
        }

        foreach ($aInstanceAccessors['getter'] as $sGetterMethod) {
            $this->assertEquals(
                1,
                $this->oEmailNotificationInstance->{$sGetterMethod}()
            );
        }

    }

    public function testBuild()
    {
        $this->oEmailNotificationInstance->setSubject('Test subject');
        $this->oEmailNotificationInstance->setExpeditor('test@domain.tld');
        $this->oEmailNotificationInstance->setRecipient('root@localhost');
        $this->oEmailNotificationInstance->setMessage('Message de test.');

        $this->assertInstanceOf('\Swift_Message', $this->oEmailNotificationInstance->build());
    }

}