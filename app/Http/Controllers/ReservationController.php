<?php

namespace App\Http\Controllers;

use App\Models\Offer;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReservationController extends Controller
{
    /**
     * Create a new reservation for a given buyer from a sell offer.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'offer_id' => 'required|exists:offers,id',
            'buyer_id' => 'required|exists:users,id',
            'amount_reserved_eur' => 'required|numeric|min:0.01',
            // Optional message body; if provided the reservation will immediately move into the
            // "in_process" state and the message will be persisted.
            'message' => 'nullable|string',
        ]);
        $seller = Auth::user();
        $offer = Offer::findOrFail($data['offer_id']);
        // Ensure the authenticated seller actually owns the offer.  Only the owner
        // of a sell offer can reserve buyers for it.
        if ($offer->user_id != $seller->id) {
            return back()->with('error', 'Možete rezervisati kupce samo za svoje ponude.');
        }
        // Ensure there is enough amount left
        if ($data['amount_reserved_eur'] > $offer->remaining_amount) {
            return back()->with('error', 'Rezervisani iznos prelazi preostali iznos.');
        }
        // Create reservation with default state "reserved".  This may be overridden to
        // "in_process" if a message body is provided below.
        $reservation = Reservation::create([
            'offer_id' => $offer->id,
            'seller_id' => $seller->id,
            'buyer_id' => $data['buyer_id'],
            'amount_reserved_eur' => $data['amount_reserved_eur'],
            'state' => 'reserved',
            'reserved_at' => now(),
        ]);

        // If a message was provided, persist it and immediately mark the reservation
        // as "in_process".  This simulates sending a chat message alongside the
        // reservation.  Otherwise, leave the reservation in the default "reserved"
        // state.
        if (!empty($data['message'])) {
            // Update reservation state and confirmation timestamp
            $reservation->state = 'in_process';
            $reservation->confirmed_at = now();
            $reservation->save();
            // Create the initial message in the reservation chat.  We escape the
            // body to avoid XSS and allow multiline messages.
            \App\Models\Message::create([
                'reservation_id' => $reservation->id,
                'sender_id' => $seller->id,
                'body' => e($data['message']),
            ]);
            // Send a unified notification to the buyer that they have new messages/changes.
            // Include the sender ID so the notification can show who sent the message
            $reservation->buyer->notify(new \App\Notifications\NewMessageNotification($reservation->id, $seller->id));
        } else {
            // No message was provided.  Send a new reservation notification to both parties.
            // The notification itself will render a generic message to keep things simple.
            $notification = new \App\Notifications\NewReservationNotification($reservation->id);
            $seller->notify($notification);
            $reservation->buyer->notify($notification);
        }

        // Update offer status.  This ensures the parent offer knows there is an active
        // reservation and may update its availability.
        $offer->updateStatusFromReservations();

        // If the client expects JSON (AJAX), return a JSON response with the reservation ID.
        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'reservation_id' => $reservation->id,
                'state' => $reservation->state,
            ]);
        }

        return redirect()->to(route('profile') . '#my-offers')->with('success', 'Rezervacija je uspešno kreirana.');
    }

    /**
     * Confirm a reservation by buyer.
     */
    public function confirm($id)
    {
        $reservation = Reservation::findOrFail($id);
        if ($reservation->state !== 'reserved') {
            return back()->with('error', 'Rezervacija nije u stanju rezervisano za potvrdu.');
        }
        $oldState = $reservation->state;
        $reservation->state = 'in_process';
        $reservation->confirmed_at = now();
        $reservation->save();
        // Notify both parties with old and new states
        $seller = $reservation->seller;
        $buyer = $reservation->buyer;
        $notification = new \App\Notifications\ReservationConfirmedNotification(
            $reservation->id,
            $oldState,
            $reservation->state,
            Auth::id()
        );
        if ($seller) {
            $seller->notify($notification);
        }
        if ($buyer) {
            $buyer->notify($notification);
        }
        return back()->with('success', 'Rezervacija je potvrđena.');
    }

    /**
     * Complete a reservation by both parties.
     */
    public function complete($id)
    {
        $reservation = Reservation::findOrFail($id);
        if ($reservation->state !== 'in_process') {
            return back()->with('error', 'Rezervacija nije u procesu.');
        }
        $oldState = $reservation->state;
        $reservation->state = 'completed';
        $reservation->completed_at = now();
        $reservation->save();
        // Update offer status if all reservations completed
        $reservation->offer->updateStatusFromReservations();
        $reservation->offer->tryCompleteIfFullyDone();
        // Notify both parties
        $seller = $reservation->seller;
        $buyer = $reservation->buyer;
        $notification = new \App\Notifications\ReservationCompletedNotification(
            $reservation->id,
            $oldState,
            $reservation->state,
            Auth::id()
        );
        if ($seller) {
            $seller->notify($notification);
        }
        if ($buyer) {
            $buyer->notify($notification);
        }
        return back()->with('success', 'Rezervacija je završena.');
    }

    /**
     * Cancel a reservation by either party.
     */
    public function cancel($id, Request $request)
    {
        $reservation = Reservation::findOrFail($id);
        if ($reservation->state === 'canceled') {
            return back()->with('info', 'Rezervacija je već otkazana.');
        }
        $oldState = $reservation->state;
        $reservation->state = 'canceled';
        $reservation->canceled_at = now();
        $reservation->save();
        $reservation->offer->updateStatusFromReservations();
        // Notify both parties
        $seller = $reservation->seller;
        $buyer = $reservation->buyer;
        $notification = new \App\Notifications\ReservationCanceledNotification(
            $reservation->id,
            $oldState,
            $reservation->state,
            Auth::id()
        );
        if ($seller) {
            $seller->notify($notification);
        }
        if ($buyer) {
            $buyer->notify($notification);
        }
        return back()->with('success', 'Rezervacija je otkazana.');
    }
}
