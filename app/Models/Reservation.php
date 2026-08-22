<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'offer_id',
        'seller_id',
        'buyer_id',
        'amount_reserved_eur',
        'state',
        'reserved_at',
        'confirmed_at',
        'completed_at',
        'canceled_at'
    ];

    protected $casts = [
        'reserved_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'completed_at' => 'datetime',
        'canceled_at' => 'datetime',
    ];

    /**
     * Offer belonging to this reservation.
     */
    public function offer()
    {
        return $this->belongsTo(Offer::class);
    }

    /**
     * Seller of this reservation.
     */
    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    /**
     * Buyer of this reservation.
     */
    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    /**
     * Messages associated with this reservation.
     */
    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Ratings associated with this reservation.
     */
    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    /**
     * Boot the model and register event listeners.
     *
     * When the reserved amount changes on an existing reservation, both
     * the buyer and seller are notified.  The notification includes
     * the previous and new amounts and a link back to the chat so
     * participants can discuss the adjustment.  This hook does not
     * fire on creation.
     */
    protected static function booted(): void
    {
        // Ensure parent boot behaviour is invoked first
        parent::booted();

        static::updating(function (self $reservation): void {
            // Only trigger when the reserved amount has changed
            if ($reservation->isDirty('amount_reserved_eur')) {
                $oldAmount = (float) $reservation->getOriginal('amount_reserved_eur');
                $newAmount = (float) $reservation->amount_reserved_eur;
                // Avoid sending a notification when there is no real change
                if (abs($oldAmount - $newAmount) > 0.0001) {
                    // Include the actor ID (if authenticated) in the notification so
                    // recipients know who changed the amount
                    $notification = new \App\Notifications\ReservationAmountChangedNotification(
                        $reservation->id,
                        $oldAmount,
                        $newAmount,
                        \Illuminate\Support\Facades\Auth::id()
                    );
                    // Notify both seller and buyer if present
                    if ($reservation->seller) {
                        $reservation->seller->notify($notification);
                    }
                    if ($reservation->buyer) {
                        $reservation->buyer->notify($notification);
                    }
                }
            }
        });
    }
}
