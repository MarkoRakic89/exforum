<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserUnlockedNotification extends Notification
{

    public function via($notifiable)
    {
        // Do not send an email when an account is unlocked.  A database
        // notification suffices.
        return ['database'];
    }

    public function toMail($notifiable)
    {
        // Unified notification when account is unlocked
        $subject = 'Imate nove poruke/izmene na sajtu exForum.rs';
        $message = 'Imate nove poruke ili izmene na sajtu exForum.rs. Molimo da se prijavite i proverite.';
        return (new MailMessage)
            ->subject($subject)
            ->markdown('emails.notification', [
                'title' => $subject,
                'message' => $message,
                'actionUrl' => url('/'),
                'actionText' => 'Otvori exForum.rs'
            ]);
    }

    public function toArray($notifiable)
    {
        return [
            'title' => 'Nalog otključan',
            'message' => 'Vaš nalog je otključan. Možete ponovo koristiti platformu.',
            'link' => url('/'),
        ];
    }
}
