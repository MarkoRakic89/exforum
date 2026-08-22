<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Reservation;

class NewReservationNotification extends Notification
{

    protected $reservationId;

    /**
     * Create a new notification instance.
     */
    public function __construct(int $reservationId)
    {
        $this->reservationId = $reservationId;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable)
    {
        // A new reservation does not require an email.  Users will see
        // the reservation in their list when they log in.  Only an
        // in‑app notification is sent.
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        // Unified notification: direct users back to the site for any new reservation or update.
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
     * Get the array representation of the notification.
     */
    public function toArray($notifiable)
    {
        // Build a more descriptive message including offer ID, type and the
        // participants.  If the reservation or its offer cannot be loaded,
        // fall back to a generic message.  The link directs users to the
        // reservation chat so they can start communicating immediately.
        $reservation = Reservation::with(['offer', 'buyer', 'seller'])->find($this->reservationId);
        if ($reservation && $reservation->offer) {
            $offerId = $reservation->offer->id;
            $type = $reservation->offer->type === 'sell' ? 'Prodaja' : 'Kupovina';
            $buyerName = optional($reservation->buyer)->naziv;
            $sellerName = optional($reservation->seller)->naziv;
            $message = 'ID=' . $offerId . ', ' . $type . ', Nova rezervacija. Kupac: ' . ($buyerName ?? '') . ', Prodavac: ' . ($sellerName ?? '') . '.';
            $link = url('/reservations/' . $this->reservationId . '/messages');
        } else {
            $message = 'Kreirana je nova rezervacija na jednoj od vaših ponuda.';
            $link = url('/');
        }
        return [
            'title' => 'Nova rezervacija',
            'message' => $message,
            'link' => $link,
        ];
    }
}
