<?php
namespace Library\Core\Notification;


interface SenderInterface {

    /**
     * Send the notification
     *
     * @param NotificationAbstract $oNotification
     * @return bool
     */
    public function send(NotificationAbstract $oNotification);

}