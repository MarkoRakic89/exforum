<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'maticni_broj',
        'naziv',
        'email',
        'password',
        'grad_id',
        'industry_id',
        'status',
        'is_featured',
        'featured_rank',
        'avg_rating',
        'ratings_count',
        // Optional company information: avatar image path and description
        'avatar',
        'description'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_featured' => 'boolean',
    ];

    /**
     * Get the city the user belongs to.
     */
    public function city()
    {
        return $this->belongsTo(City::class, 'grad_id');
    }
    public function industry()
    {
        return $this->belongsTo(Industry::class, 'industry_id');
    }

    /**
     * Get all offers created by the user.
     */
    public function offers()
    {
        return $this->hasMany(Offer::class);
    }

    /**
     * Get all reservations where the user is a seller.
     */
    public function soldReservations()
    {
        return $this->hasMany(Reservation::class, 'seller_id');
    }

    /**
     * Get all reservations where the user is a buyer.
     */
    public function boughtReservations()
    {
        return $this->hasMany(Reservation::class, 'buyer_id');
    }

    /**
     * Get all messages sent by the user.
     */
    public function messages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    /**
     * Get all ratings where this user is the person being rated.
     */
    public function ratingsReceived()
    {
        return $this->hasMany(Rating::class, 'ratee_id');
    }

    /**
     * Get the login attempt record associated with the user.
     */
    public function loginAttempt()
    {
        return $this->hasOne(LoginAttempt::class);
    }
}
