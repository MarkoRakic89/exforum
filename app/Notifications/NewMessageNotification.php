<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Reservation;

class NewMessageNotification extends Notification
{

    protected $reservationId;

    /**
     * ID of the user who sent the message.
     */
    protected $senderId;

    /**
     * Create a new notification instance.
     */
    public function __construct(int $reservationId, int $senderId)
    {
        $this->reservationId = $reservationId;
        $this->senderId = $senderId;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable)
    {
        // Send via mail and store in database for in‑app display
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        // Send a unified notification for any new message or change.  The goal is to keep
        // email communication minimal and direct users back to the application.
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

    /**
     * Get the array representation of the notification for the database channel.
     */
    public function toArray($notifiable)
    {
        // Attempt to load the reservation and related offer to build a
        // descriptive message.  If the reservation or offer is missing
        // (unlikely), fall back to a generic text.  The link directs
        // the user straight to the chat for the reservation.
        $reservation = Reservation::with('offer')->find($this->reservationId);
        $senderName = null;
        // Try to load the sender name
        if ($this->senderId) {
            $sender = \App\Models\User::find($this->senderId);
            $senderName = $sender ? $sender->naziv : null;
        }
        if ($reservation && $reservation->offer) {
            $offerId = $reservation->offer->id;
            $type = $reservation->offer->type === 'sell' ? 'Prodaja' : 'Kupovina';
            $message = 'ID=' . $offerId . ', ' . $type . ', Nova poruka';
            if ($senderName) {
                $message .= ' od ' . $senderName;
            }
            $message .= '.';
            $link = url('/reservations/' . $this->reservationId . '/messages');
        } else {
            $message = 'Imate novu poruku u jednoj od vaših rezervacija.';
            if ($senderName) {
                $message .= ' Od ' . $senderName . '.';
            }
            $link = url('/');
        }
        return [
            'title' => 'Nova poruka',
            'message' => $message,
            'link' => $link,
        ];
    }
}
