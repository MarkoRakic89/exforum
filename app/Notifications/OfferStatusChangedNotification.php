<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Offer;

class OfferStatusChangedNotification extends Notification
{

    /**
     * ID of the offer.
     */
    protected int $offerId;

    /**
     * Previous status of the offer.
     */
    protected string $oldStatus;

    /**
     * New status of the offer.
     */
    protected string $status;

    /**
     * Create a new notification instance.
     *
     * @param int $offerId The ID of the offer being updated
     * @param string $oldStatus The previous status
     * @param string $newStatus The new status
     */
    public function __construct(int $offerId, string $oldStatus, string $newStatus)
    {
        $this->offerId = $offerId;
        $this->oldStatus = $oldStatus;
        $this->status = $newStatus;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        // Unified notification: direct users to log in and check their updates on the site.
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
        $offer = Offer::find($this->offerId);
        $type = '';
        if ($offer) {
            $type = $offer->type === 'sell' ? 'Prodaja' : 'Kupovina';
        }
        return [
            'title' => 'Status ponude promenjen',
            'message' => 'ID=' . $this->offerId . ', ' . $type . ', Status promenjen iz ' .
                $this->oldStatus . ' u ' . $this->status . '.',
            'link' => url('/profile#my-offers'),
        ];
    }
}
