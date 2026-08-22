<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    /**
     * Display the authenticated user's profile and history.
     */
    public function show(\Illuminate\Http\Request $request)
    {
        $user = Auth::user();
        // Separate offers by type so they can be rendered in distinct tables on the
        // profile page.  We also capture completed and canceled offers in
        // a separate collection.  An optional status filter applies to both
        // directions.
        $offerStatus = $request->input('offer_status');
        $offersSellQuery = $user->offers()->where('type', 'sell')->orderByDesc('created_at');
        $offersBuyQuery  = $user->offers()->where('type', 'buy')->orderByDesc('created_at');
        if ($offerStatus) {
            $offersSellQuery->where('status', $offerStatus);
            $offersBuyQuery->where('status', $offerStatus);
        }
        // Active offers exclude completed and canceled
        $activeSellOffers = (clone $offersSellQuery)
            ->whereNotIn('status', ['completed', 'canceled'])
            ->with(['cities', 'industries'])
            ->get();
        $activeBuyOffers = (clone $offersBuyQuery)
            ->whereNotIn('status', ['completed', 'canceled'])
            ->with(['cities', 'industries'])
            ->get();
        // Completed and canceled offers grouped together
        $completedOffers = $user->offers()
            ->whereIn('status', ['completed', 'canceled'])
            ->orderByDesc('created_at')
            ->with(['cities', 'industries'])
            ->get();

        // Pull configurable limits for client-side validation of new offers.
        $maxPercent = \App\Models\Setting::get('max_percent', 50);
        $maxAmount  = \App\Models\Setting::get('max_amount', 10000);

        // Load lists of cities and industries for the create‑offer modal.
        $cities = \App\Models\City::all();
        $industries = \App\Models\Industry::all();

        // Determine whether the authenticated user currently has any active sell or buy offer.
        // We allow one active sell and one active buy offer simultaneously.  These
        // flags drive the UI in the profile page to enable or disable the
        // respective “Nova ponuda” buttons.
        $hasActiveSellOffer = $activeSellOffers->isNotEmpty();
        $hasActiveBuyOffer  = $activeBuyOffers->isNotEmpty();

        $offerMatches = [];
        foreach ($activeSellOffers as $sellOffer) {

            $cityIds = $sellOffer->cities->pluck('id')->toArray();
            $industryIds = $sellOffer->industries->pluck('id')->toArray();

            // ⬇ NOVI SQL MATCHING (UMESTO rankedMatches)
            $matches = \App\Models\Offer::matchesForSell(
                amount: $sellOffer->amount_eur,
                sellOfferId: $sellOffer->id,
                sellIndustryIds: $industryIds,
                sellCityIds: $cityIds
            )->get();

            // izbaci kupce koji su već rezervisali ovu ponudu
            $reservedBuyerIds = $sellOffer->reservations()
                ->whereNotIn('state', ['canceled'])
                ->pluck('buyer_id')
                ->toArray();

            $matches = $matches->reject(function ($match) use ($reservedBuyerIds) {
                return in_array($match->user_id, $reservedBuyerIds);
            });

            $perPage = 5;
            $firstPageMatches = $matches->slice(0, $perPage);
            $hasMore = $matches->count() > $perPage;

            $offerMatches[$sellOffer->id] = [
                'matches' => $firstPageMatches,
                'hasMore' => $hasMore,
                'page' => 1,
            ];
        }

        $firstActiveSellOffer = $activeSellOffers->first();

        // Reservations sold with optional state filter
        $reservationState = $request->input('reservation_state');
        $soldQuery = $user->soldReservations()->with(['offer', 'buyer'])->orderByDesc('created_at');
        $boughtQuery = $user->boughtReservations()->with(['offer', 'seller'])->orderByDesc('created_at');
        if ($reservationState) {
            $soldQuery->where('state', $reservationState);
            $boughtQuery->where('state', $reservationState);
        }
        $soldReservations = $soldQuery->get();
        $boughtReservations = $boughtQuery->get();

        return view('profile.show', [
            'user' => $user,
            'activeSellOffers' => $activeSellOffers,
            'activeBuyOffers' => $activeBuyOffers,
            'completedOffers' => $completedOffers,
            'soldReservations' => $soldReservations,
            'boughtReservations' => $boughtReservations,
            'offerStatus' => $offerStatus,
            'reservationState' => $reservationState,
            'cities' => $cities,
            'industries' => $industries,
            'maxPercent' => $maxPercent,
            'maxAmount' => $maxAmount,
            // pass separate flags for sell and buy offers
            'hasActiveSellOffer' => $hasActiveSellOffer,
            'hasActiveBuyOffer' => $hasActiveBuyOffer,
            'offerMatches' => $offerMatches,
            'activeOfferId' => optional($firstActiveSellOffer)->id,
        ]);
    }

    /**
     * Update the authenticated user's profile information.  Users can
     * upload a new avatar image and update a textual description of
     * their company.  Uploaded images are stored on the public disk
     * under the "avatars" directory.  After saving, the user is
     * redirected back to their profile with a success message.
     */
    public function update(\Illuminate\Http\Request $request)
    {
        $user = \Illuminate\Support\Facades\Auth::user();

        // Validate optional fields: avatar must be an image if provided
        $data = $request->validate([
            'description' => ['nullable', 'string'],
            'avatar' => ['nullable', 'image', 'max:2048'],
        ]);

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            // Ensure the avatars directory exists inside public
            $avatarDir = public_path('avatars');
            if (!is_dir($avatarDir)) {
                @mkdir($avatarDir, 0755, true);
            }
            $avatar = $request->file('avatar');
            $filename = uniqid('avatar_') . '.' . $avatar->getClientOriginalExtension();
            // Move the uploaded file directly to the public avatars directory.  This avoids
            // reliance on a storage symlink and makes the image immediately accessible.
            $avatar->move($avatarDir, $filename);
            $data['avatar'] = 'avatars/' . $filename;
        }

        $user->update($data);

        return redirect()->back()->with('status', 'Profil je ažuriran.');
    }

    /**
     * Export user's offers to CSV.
     */
    public function exportOffers()
    {
        $user = Auth::user();
        $offers = $user->offers()->orderBy('created_at')->get();
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="offers.csv"',
        ];
        $callback = function () use ($offers) {
            $FH = fopen('php://output', 'w');
            // Header row
            fputcsv($FH, ['ID', 'Type', 'Amount_EUR', 'Percent', 'Status', 'Created_At']);
            foreach ($offers as $offer) {
                fputcsv($FH, [
                    $offer->id,
                    $offer->type,
                    $offer->amount_eur,
                    $offer->percent,
                    $offer->status,
                    $offer->created_at,
                ]);
            }
            fclose($FH);
        };
        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export user's reservations to CSV.
     */
    public function exportReservations()
    {
        $user = Auth::user();
        // Combine sold and bought reservations with additional columns for direction and counterparty
        $reservations = collect();
        foreach ($user->soldReservations()->orderBy('created_at')->get() as $res) {
            $res->direction = 'sell';
            $res->counterparty = $res->buyer->naziv;
            $reservations->push($res);
        }
        foreach ($user->boughtReservations()->orderBy('created_at')->get() as $res) {
            $res->direction = 'buy';
            $res->counterparty = $res->seller->naziv;
            $reservations->push($res);
        }
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="reservations.csv"',
        ];
        $callback = function () use ($reservations) {
            $FH = fopen('php://output', 'w');
            fputcsv($FH, ['ID', 'Direction', 'Amount_EUR', 'State', 'Counterparty', 'Reserved_At', 'Confirmed_At', 'Completed_At', 'Canceled_At']);
            foreach ($reservations as $res) {
                fputcsv($FH, [
                    $res->id,
                    $res->direction,
                    $res->amount_reserved_eur,
                    $res->state,
                    $res->counterparty,
                    $res->reserved_at,
                    $res->confirmed_at,
                    $res->completed_at,
                    $res->canceled_at,
                ]);
            }
            fclose($FH);
        };
        return response()->stream($callback, 200, $headers);
    }
}
