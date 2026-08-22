<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    /**
     * A city may have many users.
     */
    public function users()
    {
        return $this->hasMany(User::class, 'grad_id');
    }

    /**
     * A city may belong to many offers through the pivot table.
     */
    public function offers()
    {
        return $this->belongsToMany(Offer::class, 'offer_cities', 'grad_id', 'offer_id');
    }
}