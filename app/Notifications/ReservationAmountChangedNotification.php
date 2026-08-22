<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Reservation;

/**
 * Notification dispatched when the amount of a reservation changes.
 *
 * This event occurs when either party adjusts the reserved amount on an
 * existing reservation.  It delivers an in‑app notification to both
 * participants describing the change and linking them back to the chat
 * for the reservation.  No email is sent, since the parties can
 * address the change directly within the platform.
 */
class ReservationAmountChangedNotification extends Notification
{
    /**
     * The reservation being updated.
     */
    protected int $reservationId;

    /**
     * The previous amount reserved.
     */
    protected float $oldAmount;

    /**
     * The new amount reserved.
     */
    protected float $newAmount;

    /**
     * ID of the user who changed the amount (if available).
     */
    protected ?int $actorId = null;

    /**
     * Create a new notification instance.
     */
    public function __construct(int $reservationId, float $oldAmount, float $newAmount, ?int $actorId = null)
    {
        $this->reservationId = $reservationId;
        $this->oldAmount = $oldAmount;
        $this->newAmount = $newAmount;
        $this->actorId = $actorId;
    }

    /**
     * Determine the channels the notification should be sent on.
     *
     * Only an in‑app notification is required for amount changes.  The
     * parties can see the change when they next log in.
     */
    public function via($notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * Even though a mail channel is defined by Notification, we return
     * an empty message here because amount changes do not trigger
     * emails.  The platform aims to keep email communication to a
     * minimum.
     */
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage);
    }

    /**
     * Get the array representation of the notification for the database.
     */
    public function toArray($notifiable): array
    {
        $reservation = Reservation::with('offer')->find($this->reservationId);
        $offerId = $reservation ? $reservation->offer_id : null;
        $type = '';
        $actorName = null;
        if ($reservation && $reservation->offer) {
            $type = $reservation->offer->type === 'sell' ? 'Prodaja' : 'Kupovina';
        }
        if ($this->actorId) {
            $actor = \App\Models\User::find($this->actorId);
            $actorName = $actor ? $actor->naziv : null;
        }
        $message = 'ID=' . ($offerId ?? '') . ', ' . $type . ', Iznos promenjen iz ' .
            number_format($this->oldAmount, 2) . ' u ' . number_format($this->newAmount, 2);
        if ($actorName) {
            $message .= ' od ' . $actorName;
        }
        $message .= '.';
        return [
            'title' => 'Promena iznosa rezervacije',
            'message' => $message,
            'link' => url('/reservations/' . $this->reservationId . '/messages'),
        ];
    }
}
