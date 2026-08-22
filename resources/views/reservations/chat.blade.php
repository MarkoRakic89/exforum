@extends('layouts.contentNavbarLayout')

@section('content')
<h1>Chat - Reservation #{{ $reservation->id }}</h1>
<div class="card mb-3" style="max-height: 400px; overflow-y: auto;">
    <div class="card-body">
        @foreach($messages as $message)
            <div class="mb-2">
                <strong>{{ $message->sender->naziv }}</strong>
                <span class="text-muted small">{{ $message->created_at->diffForHumans() }}</span>
                <p class="mb-1">{!! nl2br(e($message->body)) !!}</p>
            </div>
        @endforeach
    </div>
</div>
<form method="POST" action="{{ route('messages.store', $reservation->id) }}">
    @csrf
    <div class="mb-3">
        <textarea name="body" class="form-control" rows="3" placeholder="Type a message..." required></textarea>
    </div>
    <button type="submit" class="btn btn-primary">Send</button>
</form>
@endsection