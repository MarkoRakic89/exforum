<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Industry;
use App\Models\Offer;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OfferController extends Controller
{
    /**
     * Show the form for creating a new offer.
     */
    public function create()
    {
        $cities = City::all();
        $industries = Industry::all();
        // Retrieve max limits for client-side validation
        $maxPercent = Setting::get('max_percent', 50);
        $maxAmount  = Setting::get('max_amount', 10000);
        return view('offers.create', compact('cities', 'industries', 'maxPercent', 'maxAmount'));
    }

    /**
     * Store a newly created offer in storage.
     */
    public function store(Request $request)
    {
        // Pull configurable limits from settings. If not set, use sensible defaults.
        $maxPercent = Setting::get('max_percent', 50);
        $maxAmount  = Setting::get('max_amount', 10000);

        $data = $request->validate([
            'type' => 'required|in:sell,buy',
            // Amount must be positive and not exceed the configured maximum amount
            'amount_eur' => ['required', 'numeric', 'min:0.01', 'max:' . $maxAmount],
            // Percent must be positive and not exceed the configured maximum percent
            'percent' => ['required', 'numeric', 'min:0', 'max:' . $maxPercent],
            // Repeat type is optional: if absent (e.g. for sell offers) default to once
            'repeat_type' => ['nullable', 'in:once,monthly'],
            'repeat_until' => ['nullable', 'date', 'after_or_equal:today'],
            'cities' => ['nullable', 'array'],
            'cities.*' => 'exists:cities,id',
            // Industries are required only for sell offers (buyers don't pick industry)
            'industries' => $request->type == 'sell' ? ['nullable', 'array'] : ['nullable', 'array'],
            'industries.*' => 'exists:industries,id',
        ]);

        // Prevent creating multiple active offers of the same type for a user.  A user may
        // have at most one active sell and one active buy offer at a time.  This check
        // enforces the rule server‑side in case the UI fails to disable the button.
        $existing = Offer::where('user_id', auth()->id())
            ->where('type', $data['type'])
            ->whereNotIn('status', ['completed', 'canceled'])
            ->exists();
        if ($existing) {
            return back()->with('error', 'Već imate aktivnu ponudu za ovu vrstu. Nije moguće kreirati novu ponudu.');
        }

        $user = Auth::user();
        $offer = new Offer();
        $offer->user_id = $user->id;
        $offer->type = $data['type'];
        $offer->amount_eur = $data['amount_eur'];
        $offer->percent = $data['percent'];
        // Default repeat_type to once if it is not provided (e.g. sell offers)
        $repeatType = $data['repeat_type'] ?? 'once';
        $offer->repeat_type = $repeatType;
        $offer->repeat_until = $repeatType === 'monthly' ? $data['repeat_until'] : null;
        $offer->status = 'published';
        $offer->save();
        $offer->cities()->sync($data['cities'] ?? []);
        // Sync industries only if provided; otherwise leave empty for buyers
        $offer->industries()->sync($data['industries'] ?? []);
        // After creating an offer redirect the user back to their profile page
        // anchored to the offers section rather than the dashboard.  This
        // improves the workflow on the unified page by bringing them
        // immediately to the list of offers and reservations.
        $request->session()->flash('success', 'Ponuda je uspešno kreirana');
    }

    /**
     * Search for matching buy offers when selling.
     */
    public function search(Request $request)
    {
        $data = $request->validate([
            'amount_eur' => 'required|numeric|min:0.01',
            'cities' => ['required', 'array', 'min:1'],
            'cities.*' => 'exists:cities,id',
            'industries' => ['required', 'array', 'min:1'],
            'industries.*' => 'exists:industries,id',
        ]);
        $matches = Offer::rankedMatches($data['amount_eur'], $data['cities'], $data['industries']);
        return view('offers.results', [
            'matches' => $matches,
            'amount' => $data['amount_eur'],
        ]);
    }

    /**
     * Display a form for searching buyers.
     *
     * This separate page contains the search form previously embedded in the
     * dashboard. Relocating the form here keeps the dashboard focused on
     * summary information while still giving users a dedicated screen to
     * discover potential buyers.
     */
    public function searchForm()
    {
        // Load cities and industries for the select lists
        $cities = City::all();
        $industries = Industry::all();
        return view('customers.search', compact('cities', 'industries'));
    }

    /**
     * Clone an existing offer, preserving all fields and relations.
     */
    public function clone($id, Request $request)
    {
        $offer = Offer::findOrFail($id);
        // Ensure the authenticated user owns the offer being cloned
        if ($offer->user_id != auth()->id()) {
            abort(403);
        }
        // Prevent cloning if the user already has an active offer of the same type.  A user
        // may have at most one active sell offer and one active buy offer concurrently.
        $hasActive = Offer::where('user_id', auth()->id())
            ->where('type', $offer->type)
            ->whereNotIn('status', ['completed', 'canceled'])
            ->exists();
        if ($hasActive) {
            return redirect()->back()->with('error', 'Već imate aktivnu ponudu ovog tipa. Nije moguće ponoviti ponudu dok je ona aktivna.');
        }
        $new = $offer->replicate();
        $new->status = 'published';
        $new->created_at = now();
        $new->updated_at = now();
        $new->save();
        // copy pivot relations
        $new->cities()->sync($offer->cities->pluck('id')->toArray());
        $new->industries()->sync($offer->industries->pluck('id')->toArray());
        $request->session()->flash('success', 'Ponuda je uspešno ponovljena.');
    }

    /**
     * Return a list of matching buy offers for the given sell offer.
     * This endpoint is used by the profile page to refresh the
     * recommended buyers without reloading the entire page.
     * It returns a JSON payload containing rendered HTML for the list
     * items so that the client can simply replace the existing list.
     */
    public function matches($id)
    {
        $offer = Offer::with(['cities', 'industries'])->findOrFail($id);

        // Ensure the authenticated user owns this offer
        if ($offer->user_id != auth()->id()) {
            abort(403);
        }

        // Only provide matches for active sell offers
        if (in_array($offer->status, ['completed', 'canceled'])) {
            return response()->json([
                'html' => '<em>Nema odgovarajućih kupaca za ovu ponudu.</em>',
                'hasMore' => false
            ]);
        }

        $cityIds = $offer->cities->pluck('id')->toArray();
        $industryIds = $offer->industries->pluck('id')->toArray();

        // ⬇ NOVI SQL MATCHING
        $matches = Offer::matchesForSell(
            amount: $offer->amount_eur,
            sellOfferId: $offer->id,
            sellIndustryIds: $industryIds,
            sellCityIds: $cityIds
        )
            ->with(['user.city', 'user.industry'])
            ->get();

        // Exclude buyers who already reserved this offer
        $reservedBuyerIds = $offer->reservations()
            ->whereNotIn('state', ['canceled'])
            ->pluck('buyer_id')
            ->toArray();

        $matches = $matches->reject(function ($match) use ($reservedBuyerIds) {
            return in_array($match->user_id, $reservedBuyerIds);
        });

        // Pagination (manual)
        $perPage = 5;
        $page = (int) request()->query('page', 1);
        $offset = ($page - 1) * $perPage;

        $paged = $matches->slice($offset, $perPage);
        $hasMore = $matches->count() > ($offset + $perPage);

        $html = view('offers.partials.match_list', [
            'offer' => $offer,
            'matches' => $paged,
        ])->render();

        return response()->json([
            'html' => $html,
            'hasMore' => $hasMore,
            'page' => $page,
        ]);
    }


    /**
     * Edit form for an existing offer.  Only active offers can be edited.
     */
    public function edit($id)
    {
        $offer = Offer::findOrFail($id);
        if ($offer->user_id != auth()->id()) {
            abort(403);
        }
        if (in_array($offer->status, ['completed', 'canceled'])) {
            return redirect()->back()->with('error', 'Završene ponude nije moguće izmeniti.');
        }
        $cities = City::all();
        $industries = Industry::all();
        $maxPercent = Setting::get('max_percent', 50);
        $maxAmount  = Setting::get('max_amount', 10000);
        return view('offers.edit', compact('offer', 'cities', 'industries', 'maxPercent', 'maxAmount'));
    }

    /**
     * Update an existing offer.  Amount and percent can be changed only up to
     * the reserved amount.  Type cannot be changed once reservations exist.
     */
    public function update($id, Request $request)
    {
        $offer = Offer::findOrFail($id);
        if ($offer->user_id != auth()->id()) {
            abort(403);
        }
        // Validate input
        $maxPercent = Setting::get('max_percent', 50);
        $maxAmount  = Setting::get('max_amount', 10000);
        $data = $request->validate([
            'amount_eur' => ['required', 'numeric', 'min:0.01', 'max:' . $maxAmount],
            'percent' => ['required', 'numeric', 'min:0', 'max:' . $maxPercent],
            'repeat_type' => 'required|in:once,monthly',
            'repeat_until' => 'nullable|date|after_or_equal:today',
            'cities' => ['nullable', 'array'],
            'cities.*' => 'exists:cities,id',
            'industries' => ['nullable', 'array'],
            'industries.*' => 'exists:industries,id',
        ]);
        // Ensure new amount is not below reserved amount
        $reserved = $offer->reservations()->whereNotIn('state', ['canceled'])->sum('amount_reserved_eur');
        if ($data['amount_eur'] < $reserved) {
            return back()->with('error', 'Novi iznos ne može biti manji od već rezervisanog iznosa.');
        }
        // Disallow changing offer type if there are reservations
        if ($offer->reservations()->count() > 0 && $data['type'] ?? $offer->type !== $offer->type) {
            return back()->with('error', 'Nije moguće promeniti tip ponude nakon rezervacija.');
        }
        // Update fields
        $offer->amount_eur = $data['amount_eur'];
        $offer->percent = $data['percent'];
        $offer->repeat_type = $data['repeat_type'];
        $offer->repeat_until = $data['repeat_type'] === 'monthly' ? $data['repeat_until'] : null;
        $offer->save();
        // Sync pivot tables only if no reservations exist
        if ($offer->reservations()->count() === 0) {
            $offer->cities()->sync($data['cities'] ?? []);
            if ($offer->type === 'sell') {
                $offer->industries()->sync($data['industries'] ?? []);
            }
        }
        $request->session()->flash('success', 'Ponuda je uspešno izmenjena.');
    }

    /**
     * Deactivate (cancel) an offer.  This sets the status to canceled and
     * prevents further reservations.  Existing reservations remain unchanged.
     */
    public function destroy($id)
    {
        $offer = Offer::findOrFail($id);
        if ($offer->user_id != auth()->id()) {
            abort(403);
        }
        // If the offer is already canceled, inform the user
        if ($offer->status === 'canceled') {
            return redirect()->back()->with('info', 'Ponuda je već deaktivirana.');
        }

        // Gather all reservations that are not already canceled
        $reservations = $offer->reservations()->whereNotIn('state', ['canceled'])->get();
        // Determine if there are confirmed or completed reservations.  These
        // indicate that the buyer has already confirmed and the deal is in
        // progress or finished; the offer should not be deactivated without
        // manual intervention.
        $hasConfirmed = $reservations->contains(function ($res) {
            return in_array($res->state, ['in_process', 'completed']);
        });

        if ($hasConfirmed) {
            return redirect()->back()->with('error', 'Ponuda ima potvrđene ili završene rezervacije i ne može se deaktivirati dok ne obavestite kupce i ne otkažete rezervacije.');
        }

        // Cancel any pending reservations and notify both parties
        foreach ($reservations as $res) {
            // Capture the old state before canceling
            $oldState = $res->state;
            $res->state = 'canceled';
            $res->canceled_at = now();
            $res->save();
            // Send cancellation notification to seller and buyer with actor ID
            $seller = $res->seller;
            $buyer = $res->buyer;
            $notification = new \App\Notifications\ReservationCanceledNotification(
                $res->id,
                $oldState,
                'canceled',
                \Illuminate\Support\Facades\Auth::id()
            );
            if ($seller) {
                $seller->notify($notification);
            }
            if ($buyer) {
                $buyer->notify($notification);
            }
        }

        // Finally, cancel the offer itself.  Capture the previous status before
        // mutation so we can include it in the notification.
        $previousStatus = $offer->status;
        $offer->status = 'canceled';
        $offer->save();
        // Notify the owner about the status change with both old and new values
        $owner = $offer->user;
        if ($owner) {
            $owner->notify(
                new \App\Notifications\OfferStatusChangedNotification(
                    $offer->id,
                    $previousStatus,
                    'canceled'
                )
            );
        }
        return redirect()->to(route('profile') . '#my-offers')->with('success', 'Ponuda je deaktivirana.');
    }
}
