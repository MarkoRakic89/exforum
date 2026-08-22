<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Industry;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;

/**
 * Display an overview for the authenticated user.
 *
 * The dashboard aggregates a handful of useful statistics about the user's
 * activity on the platform. Showing a summary up front helps orient
 * users and reduces the amount of digging needed to understand their
 * current workload. Additional cards can be added here as new metrics
 * become relevant.
 */
class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        // Total offers the user has created
        $totalOffers = $user->offers()->count();
        // Active offers: anything still open for reservations or in progress
        $activeOffers = $user->offers()
            ->whereIn('status', ['published', 'reserved_partial', 'reserved_full', 'in_progress'])
            ->count();
        // Completed offers
        $completedOffers = $user->offers()->where('status', 'completed')->count();
        // Reservations as seller that are not cancelled
        $sellingReservations = $user->soldReservations()->where('state', '!=', 'canceled')->count();
        // Reservations as buyer that are not cancelled
        $buyingReservations = $user->boughtReservations()->where('state', '!=', 'canceled')->count();
        // Unread notifications count
        $unreadNotifications = $user->unreadNotifications->count();

        $cities = City::all();
        $industries = Industry::all();
        // Retrieve max limits for client-side validation
        $maxPercent = Setting::get('max_percent', 50);
        $maxAmount  = Setting::get('max_amount', 10000);
        return view('dashboard', compact(
            'totalOffers',
            'activeOffers',
            'completedOffers',
            'sellingReservations',
            'buyingReservations',
            'unreadNotifications',
            'cities',
            'industries',
            'maxPercent',
            'maxAmount'
        ));
    }
}
