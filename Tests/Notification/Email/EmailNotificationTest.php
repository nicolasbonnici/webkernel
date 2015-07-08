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
                $this->oEmailNotificationInstance->{$sSetterMethod}(1),
                'Accessor ' . $sSetterMethod . ' of class ' . get_class($this->oEmailNotificationInstance) .' failed'
            );
        }

        foreach ($aInstanceAccessors['getter'] as $sGetterMethod) {
            $this->assertEquals(
                1,
                $this->oEmailNotificationInstance->{$sGetterMethod}(),
                'Accessor ' . $sGetterMethod . ' of class ' . get_class($this->oEmailNotificationInstance) .' failed'
            );
        }

    }

    public function testBuild()
    {
        $this->oEmailNotificationInstance->setSubject('Test subject');
        $this->oEmailNotificationInstance->setExpeditor('test@domain.tld');
        $this->oEmailNotificationInstance->setRecipient('root@localhost');
        $this->oEmailNotificationInstance->setMessage('Message de test.');

        /** @var \Swift_Message $oSwiftMessage */
        $oSwiftMessage = $this->oEmailNotificationInstance->build();

        $this->assertInstanceOf('\Swift_Message', $oSwiftMessage);
        $this->assertEquals('Test subject', $oSwiftMessage->getSubject());
        $this->assertArrayHasKey('test@domain.tld', $oSwiftMessage->getFrom());
        $this->assertArrayHasKey('root@localhost', $oSwiftMessage->getTo());
        $this->assertEquals('Message de test.', $oSwiftMessage->getBody());
    }

}