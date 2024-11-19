<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FirebaseNotification;
class CommonNotification extends Notification
{
    use Queueable;



    private $title;
    private $body;
    /**
     * Create a new notification instance.
     */
    public function __construct($title,$body)
    {
      $this->$title = $title;
      $this->body = $body;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['fcm'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable)
    {
       return [];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toFcm($notifiable)
    {
        $fcmToken = $notifiable->fcm_token; // Assuming you store the FCM token in the user model

        return CloudMessage::withTarget('token', $fcmToken)
            ->withNotification(FirebaseNotification::create($this->title, $this->body));
    }
}
