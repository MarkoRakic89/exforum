<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Notification sent to a new user when their account is created by an admin.
 *
 * It includes a generated password and a link to the login page.
 */
class NewUserNotification extends Notification
{
    use Queueable;

    /**
     * The generated plain-text password.
     */
    protected string $password;

    public function __construct(string $password)
    {
        $this->password = $password;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        $subject = 'Dobrodošli na platformu!';
        // Compose message using double‑quoted strings so that newlines are properly rendered.
        $message = "Poštovani {$notifiable->naziv},\n"
            . "Vaš nalog je uspešno kreiran.\n"
            . "Matični broj: {$notifiable->maticni_broj}\n"
            . "Privremena lozinka: {$this->password}\n"
            . "Preporučujemo da se prijavite i promenite lozinku što pre.";
        return (new MailMessage)
            ->subject($subject)
            ->markdown('emails.notification', [
                'title' => $subject,
                'message' => $message,
                'actionUrl' => url('/login'),
                'actionText' => 'Prijavite se'
            ]);
    }
}