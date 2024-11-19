<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Kreait\Firebase\Factory;

class FcmChannel
{
    public function send($notifiable, CommonNotification $notification)
    {
        $data = $notification->toFcm($notifiable);

        $factory = (new Factory)->withServiceAccount(config('firebase.credentials'));
        $messaging = $factory->createMessaging();

        $messaging->send($data);
    }
}
