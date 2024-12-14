<?php

namespace App\Notifications;

use Kreait\Firebase\Factory;

class FcmChannel
{
    public function send($notifiable, CommonNotification $notification)
    {
        $data = $notification->toFcm($notifiable);

        $factory = (new Factory)->withServiceAccount(public_path('firebase_credentials.json'));
        $messaging = $factory->createMessaging();

        $messaging->send($data);
    }
}
