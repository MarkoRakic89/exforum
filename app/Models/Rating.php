<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    use HasFactory;

    protected $fillable = [
        'reservation_id',
        'rater_id',
        'ratee_id',
        'score',
        'comment',
        'visible'
    ];

    /**
     * Reservation associated with the rating.
     */
    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    /**
     * User who gave the rating.
     */
    public function rater()
    {
        return $this->belongsTo(User::class, 'rater_id');
    }

    /**
     * User who was rated.
     */
    public function ratee()
    {
        return $this->belongsTo(User::class, 'ratee_id');
    }
}