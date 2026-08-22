{{--
    This view renders a chat conversation for a reservation without the
    surrounding application layout.  It is intended to be loaded via
    AJAX into a modal on the profile page.  The markup largely mirrors
    the `reservations.chat` view but avoids extending a layout.  A
    data-action attribute is added to the form to expose the URL for
    sending messages via fetch.
--}}
<div class="card mb-3" style="max-height: 400px; overflow-y: auto;">
    <div class="card-body">
        @forelse($messages as $message)
            <div class="mb-2">
                <strong>{{ $message->sender->naziv }}</strong>
                <span class="text-muted small">{{ $message->created_at->diffForHumans() }}</span>
                <p class="mb-1">{!! nl2br(e($message->body)) !!}</p>
            </div>
        @empty
            <p class="text-muted">Nema poruka u ovoj konverzaciji.</p>
        @endforelse
    </div>
</div>
<form id="modalChatForm"
      data-action="{{ route('messages.store', $reservation->id) }}"
      data-modal-url="{{ route('messages.modal', $reservation->id) }}">
    @csrf
    <div class="mb-3">
        <textarea name="body" class="form-control" rows="3" placeholder="Unesite poruku..." required></textarea>
    </div>
    <button type="submit" class="btn btn-primary">Pošalji</button>
</form>