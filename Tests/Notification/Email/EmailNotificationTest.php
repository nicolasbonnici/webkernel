<?php
namespace Library\Core\Tests\Notification\Email;

use Library\Core\Test;

use Library\Core\Notification\Email\EmailNotification;

class EmailNotificationTest extends Test {

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

}