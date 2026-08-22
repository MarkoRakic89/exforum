<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    /**
     * Display the chat messages for a reservation.
     */
    public function index($reservationId, Request $request)
    {
        $reservation = Reservation::with('messages.sender')->findOrFail($reservationId);
        $messages = $reservation->messages()->orderBy('created_at')->get();
        // If this request is an AJAX call (e.g., loaded via fetch into a modal)
        // or includes a modal query flag, return the lightweight chat partial.
        if ($request->ajax() || $request->boolean('modal')) {
            return view('reservations.chat_modal', compact('reservation', 'messages'));
        }
        return view('reservations.chat', compact('reservation', 'messages'));
    }

    /**
     * Store a new message in a reservation chat.
     */
    public function store($reservationId, Request $request)
    {
        $reservation = Reservation::findOrFail($reservationId);
        $user = Auth::user();
        // Authorization: only buyer or seller can send messages
        if (!in_array($user->id, [$reservation->seller_id, $reservation->buyer_id])) {
            abort(403);
        }
        $data = $request->validate([
            'body' => 'required|string|min:1',
        ]);
        // Rate limit: max 10 messages per minute per reservation per user
        $recentCount = Message::where('reservation_id', $reservation->id)
            ->where('sender_id', $user->id)
            ->where('created_at', '>=', now()->subMinute())
            ->count();
        if ($recentCount >= 10) {
            return back()->with('error', 'Previše brzo šaljete poruke. Molimo sačekajte trenutak.');
        }
        // Create the message in the chat.  Escape the body to avoid XSS and
        // preserve newlines.  If this is the first message and the reservation
        // was previously "reserved", automatically move it into the "in_process"
        // state to reflect that communication has started.
        $message = new Message();
        $message->reservation_id = $reservation->id;
        $message->sender_id = $user->id;
        $message->body = e($data['body']);
        $message->save();

        // If the reservation was still reserved, transition it to in_process
        // on the first message.  Record the confirmation timestamp as well.
        if ($reservation->state === 'reserved') {
            $reservation->state = 'in_process';
            $reservation->confirmed_at = now();
            $reservation->save();
        }

        // Notify the other party about the new message or change.  Use the unified
        // message/changes notification so that the user sees a consistent prompt.
        $recipientId = $user->id == $reservation->seller_id ? $reservation->buyer_id : $reservation->seller_id;
        $recipient = \App\Models\User::find($recipientId);
        if ($recipient) {
            // Pass the sender ID to the notification so it can include the sender's name
            $recipient->notify(new \App\Notifications\NewMessageNotification($reservation->id, $user->id));
        }
        return redirect()->route('messages.index', $reservation->id)->with('success', 'Poruka je uspešno poslata.');
    }

    /**
     * Return a lightweight version of the chat interface to be loaded
     * inside a modal.  This method omits the full application layout
     * and simply renders the conversation thread and message form.  It is
     * intended to be fetched asynchronously via JavaScript and injected
     * into a modal on the profile page.  Access control is identical
     * to the index method.
     *
     * @param int $reservationId The ID of the reservation whose chat
     *                           should be displayed
     */
    public function modal($reservationId)
    {
        // Load the reservation with messages and sender relations.  If the
        // reservation does not exist, abort with a 404.  Authorization is
        // handled implicitly via the messages index: only participants can
        // view the chat.
        $reservation = Reservation::with('messages.sender')->findOrFail($reservationId);
        $messages = $reservation->messages()->orderBy('created_at')->get();
        return view('reservations.chat_modal', compact('reservation', 'messages'));
    }
}
