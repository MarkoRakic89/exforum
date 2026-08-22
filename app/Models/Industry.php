<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Industry extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'name'];

    /**
     * Get the offers associated with this industry.
     */
    public function offers()
    {
        return $this->belongsToMany(Offer::class, 'offer_industries');
    }
}