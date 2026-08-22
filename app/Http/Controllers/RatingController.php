<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Rating;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RatingController extends Controller
{
    /**
     * Store a new rating after a reservation has been completed.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'reservation_id' => 'required|exists:reservations,id',
            'score' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|min:20',
        ]);
        $reservation = Reservation::with(['seller', 'buyer'])->findOrFail($data['reservation_id']);
        if ($reservation->state !== 'completed') {
            return back()->withErrors(['reservation_id' => 'Reservation must be completed before rating']);
        }
        $rater = Auth::user();
        // Determine ratee
        $rateeId = $rater->id === $reservation->seller_id ? $reservation->buyer_id : $reservation->seller_id;
        // Check if rating already exists
        $existing = Rating::where('reservation_id', $reservation->id)
            ->where('rater_id', $rater->id)
            ->first();
        if ($existing) {
            return back()->withErrors(['reservation_id' => 'You have already rated this reservation']);
        }
        // Store rating
        $rating = Rating::create([
            'reservation_id' => $reservation->id,
            'rater_id' => $rater->id,
            'ratee_id' => $rateeId,
            'score' => $data['score'],
            'comment' => $data['comment'],
            'visible' => true,
        ]);
        // Update cached rating on ratee user
        $ratee = User::find($rateeId);
        $ratee->ratings_count += 1;
        $ratee->avg_rating = ($ratee->avg_rating * ($ratee->ratings_count - 1) + $data['score']) / $ratee->ratings_count;
        $ratee->save();
        return back()->with('success', 'Rating submitted');
    }
}