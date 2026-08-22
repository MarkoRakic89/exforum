<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Reservation;

class ReservationConfirmedNotification extends Notification
{

    /**
     * The reservation identifier.
     */
    protected int $reservationId;

    /**
     * ID of the user who performed the confirmation.
     */
    protected ?int $actorId = null;

    /**
     * Previous state of the reservation.
     */
    protected string $oldState;

    /**
     * New state of the reservation.
     */
    protected string $newState;

    public function __construct(int $reservationId, string $oldState, string $newState, ?int $actorId = null)
    {
        $this->reservationId = $reservationId;
        $this->oldState = $oldState;
        $this->newState = $newState;
        $this->actorId = $actorId;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        // Unified notification for reservation confirmations
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
        // Load the reservation and its offer to produce a more descriptive
        // notification.  If the reservation or offer cannot be located,
        // fall back to a generic text.  The link directs the user back to
        // the chat for the reservation.
        $reservation = Reservation::with('offer')->find($this->reservationId);
        $message = '';
        $link = url('/');
        if ($reservation && $reservation->offer) {
            $offerId = $reservation->offer->id;
            $type = $reservation->offer->type === 'sell' ? 'Prodaja' : 'Kupovina';
            $actorName = null;
            if ($this->actorId) {
                $actor = \App\Models\User::find($this->actorId);
                $actorName = $actor ? $actor->naziv : null;
            }
            $message = 'ID=' . $offerId . ', ' . $type . ', Status rezervacije promenjen iz ' .
                $this->oldState . ' u ' . $this->newState;
            if ($actorName) {
                $message .= ' od ' . $actorName;
            }
            $message .= '.';
            $link = url('/reservations/' . $this->reservationId . '/messages');
        } else {
            $message = 'Jedna od vaših rezervacija je promenila status.';
            $link = url('/');
        }
        return [
            'title' => 'Status rezervacije promenjen',
            'message' => $message,
            'link' => $link,
        ];
    }
}
