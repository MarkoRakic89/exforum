<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Offer extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'amount_eur',
        'percent',
        'repeat_type',
        'repeat_until',
        'status',
    ];

    protected $casts = [
        'repeat_until' => 'date',
        'amount_eur'   => 'float',
        'percent'      => 'float',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function cities()
    {
        return $this->belongsToMany(
            City::class,
            'offer_cities',
            'offer_id',
            'grad_id'
        );
    }

    public function industries()
    {
        return $this->belongsToMany(
            Industry::class,
            'offer_industries',
            'offer_id',
            'industry_id'
        );
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Computed
    |--------------------------------------------------------------------------
    */

    public function getReservedAmountAttribute(): float
    {
        return (float) $this->reservations()
            ->whereNotIn('state', ['canceled'])
            ->sum('amount_reserved_eur');
    }

    public function getRemainingAmountAttribute(): float
    {
        return max(0, $this->amount_eur - $this->reserved_amount);
    }

    /*
    |--------------------------------------------------------------------------
    | Status logic
    |--------------------------------------------------------------------------
    */
    public function tryCompleteIfFullyDone(): void
    {
        // Ako ponuda nije puna – nema šta da se završava
        if ($this->status !== 'reserved_full') {
            return;
        }

        // Ako postoji ijedna rezervacija koja NIJE completed → ne završavaj
        $hasUnfinished = $this->reservations()
            ->whereNotIn('state', ['completed'])
            ->exists();

        if ($hasUnfinished) {
            return;
        }

        // Sve rezervacije su completed → završi ponudu
        $oldStatus = $this->status;
        $this->status = 'completed';
        $this->save();
        // Obavesti vlasnika ponude o promeni statusa (sa starog na novi)
        if ($this->user) {
            $this->user->notify(
                new \App\Notifications\OfferStatusChangedNotification(
                    $this->id,
                    $oldStatus,
                    'completed'
                )
            );
        }
    }

    public function updateStatusFromReservations(): void
    {
        if (in_array($this->status, ['canceled', 'completed'])) {
            return;
        }

        $reservedSum = $this->reservations()
            ->whereNotIn('state', ['canceled'])
            ->sum('amount_reserved_eur');

        $oldStatus = $this->status;

        if ($reservedSum <= 0) {
            $this->status = 'published';
        } elseif ($reservedSum < $this->amount_eur) {
            $this->status = 'reserved_partial';
        } else {
            $this->status = 'reserved_full';
        }

        if ($this->isDirty('status') && $this->status !== $oldStatus) {
            $this->save();
            // Notify the owner about the status change with both old and new values
            if ($this->user) {
                $this->user->notify(
                    new \App\Notifications\OfferStatusChangedNotification(
                        $this->id,
                        $oldStatus,
                        $this->status
                    )
                );
            }
        } else {
            $this->save();
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * BUY ponude rangirane prema SELL ponudi
     *
     * PRIORITET:
     * 1. BUY.user.industry_id ∈ SELL.offer_industries + SELL ima grad
     * 2. BUY.user.industry_id ∈ SELL.offer_industries
     * 3. SELL ima grad
     * 4. ostali
     */
    public function scopeMatchesForSell(
        Builder $query,
        float $amount,
        int $sellOfferId,
        array $sellIndustryIds = [],
        array $sellCityIds = []
    ): Builder {

        $cases = [];

        if (!empty($sellIndustryIds) && !empty($sellCityIds)) {
            $cases[] = "
            WHEN users.industry_id IN (" . implode(',', array_map('intval', $sellIndustryIds)) . ")
             AND EXISTS (
                SELECT 1
                FROM offer_cities oc
                WHERE oc.offer_id = " . (int)$sellOfferId . "
                AND oc.grad_id IN (" . implode(',', array_map('intval', $sellCityIds)) . ")
             ) THEN 3
        ";
        }

        if (!empty($sellIndustryIds)) {
            $cases[] = "
            WHEN users.industry_id IN (" . implode(',', array_map('intval', $sellIndustryIds)) . ")
            THEN 2
        ";
        }

        if (!empty($sellCityIds)) {
            $cases[] = "
            WHEN EXISTS (
                SELECT 1
                FROM offer_cities oc
                WHERE oc.offer_id = " . (int)$sellOfferId . "
                AND oc.grad_id IN (" . implode(',', array_map('intval', $sellCityIds)) . ")
            ) THEN 1
        ";
        }

        $caseSql = !empty($cases)
            ? 'CASE ' . implode(' ', $cases) . ' ELSE 0 END'
            : '0';

        return $query
            ->select('offers.*')
            ->join('users', 'users.id', '=', 'offers.user_id')
            ->where('offers.type', 'buy')
            ->where('offers.amount_eur', '<=', $amount)
            ->where('offers.user_id', '<>', auth()->id())
            ->whereNotIn('offers.status', [
                'canceled',
                'completed',
                'reserved_full',
            ])
            ->where('users.status', 'active')
            ->orderByDesc('offers.amount_eur');
    }

    /*
    |--------------------------------------------------------------------------
    | Existing ranking (unchanged)
    |--------------------------------------------------------------------------
    */

    public static function rankedMatches(
        float $amount,
        array $cityIds,
        array $industryIds,
        array $weights = []
    ): \Illuminate\Database\Eloquent\Collection {
        $defaults = [
            'featured'      => 0.5,
            'rating'        => 0.3,
            'ratings_count' => 0.1,
            'freshness'     => 0.1,
        ];

        $weights = array_merge($defaults, $weights);

        $offers = self::query()
            ->matchesForSell($amount, $industryIds, $cityIds)
            ->with(['user', 'cities', 'industries'])
            ->get();

        return $offers->sortByDesc(function ($offer) use ($weights) {
            $user = $offer->user;
            $score = 0;

            $score += ($user->is_featured ? 1 : 0) * $weights['featured'];

            $avg = $user->avg_rating > 0 ? $user->avg_rating / 5 : 0;
            $score += $avg * $weights['rating'];

            $count = $user->ratings_count;
            $normalizedCount = $count > 0
                ? log(1 + $count) / log(1 + 100)
                : 0;

            $score += $normalizedCount * $weights['ratings_count'];

            $freshness = 1 / max(1, now()->diffInDays($offer->created_at) + 1);
            $score += $freshness * $weights['freshness'];

            return $score;
        })->values();
    }
}
