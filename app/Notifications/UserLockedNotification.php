<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserLockedNotification extends Notification
{

    protected $until;

    public function __construct(?string $until)
    {
        $this->until = $until;
    }

    public function via($notifiable)
    {
        // Account locks should not trigger an email.  Users can see the
        // notification when they attempt to log in.
        return ['database'];
    }

    public function toMail($notifiable)
    {
        // Unified notification even for account locks
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
            'title' => 'Nalog zaključan',
            'message' => $this->until
                ? 'Vaš nalog je zaključan do ' . $this->until . '.'
                : 'Vaš nalog je zaključan.',
            'link' => url('/'),
        ];
    }
}
