@extends('layouts.contentNavbarLayout')

@section('content')
<h1>Notifikacije</h1>
<div class="mb-3 d-flex justify-content-between align-items-center">
    <form method="POST" action="{{ route('notifications.readAll') }}">
        @csrf
        <button type="submit" class="btn btn-sm btn-outline-secondary">Označi sve kao pročitane</button>
    </form>
    <span>Ukupno notifikacija: {{ $notifications->total() }}</span>
</div>
<div class="row">
    @forelse($notifications as $notification)
        @php
            $isUnread = $notification->read_at === null;
            $data = $notification->data;
        @endphp
        <div class="col-12">
            <div class="card mb-3 shadow-sm {{ $isUnread ? 'border-info' : '' }}">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <h5 class="card-title mb-1">{{ $data['title'] ?? 'Notifikacija' }}</h5>
                            <p class="card-text mb-0">{{ $data['message'] ?? '' }}</p>
                            {{-- Display additional structured information if available --}}
                            @php
                                // Format amounts with two decimals and comma separators for clarity
                                $formatAmount = function($amount) {
                                    return number_format($amount, 2, ',', '.');
                                };
                            @endphp
                            <div class="mt-1">
                                @if(isset($data['offer_id']) && isset($data['type']))
                                    <span class="d-block"><strong>Ponuda ID:</strong> {{ $data['offer_id'] }} ({{ $data['type'] }})</span>
                                @endif
                                @if(isset($data['reservation_id']))
                                    <span class="d-block"><strong>Rezervacija ID:</strong> {{ $data['reservation_id'] }}</span>
                                @endif
                                @if(isset($data['old_state']) && isset($data['new_state']))
                                    <span class="d-block"><strong>Status:</strong> {{ $data['old_state'] }} → {{ $data['new_state'] }}</span>
                                @endif
                                @if(isset($data['old_amount']) && isset($data['new_amount']))
                                    <span class="d-block"><strong>Iznos:</strong> {{ $formatAmount($data['old_amount']) }} → {{ $formatAmount($data['new_amount']) }}</span>
                                @endif
                                @if(isset($data['buyer_name']) && isset($data['seller_name']))
                                    <span class="d-block"><strong>Kupac:</strong> {{ $data['buyer_name'] }}; <strong>Prodavac:</strong> {{ $data['seller_name'] }}</span>
                                @endif
                                @if(isset($data['actor_name']))
                                    <span class="d-block"><strong>Klijent:</strong> {{ $data['actor_name'] }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="ms-3 text-end" style="white-space: nowrap;">
                            <small class="text-muted d-block">{{ $notification->created_at->diffForHumans() }}</small>
                            @if($isUnread)
                                <form method="POST" action="{{ route('notifications.read', $notification->id) }}" class="mt-1">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-secondary">Markiraj kao pročitano</button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="alert alert-info" role="alert">Nemate notifikacija.</div>
        </div>
    @endforelse
    <div class="col-12">
        <div class="mt-3">
            {{ $notifications->links('admin.bootstrap-5') }}
        </div>
    </div>
</div>
@endsection