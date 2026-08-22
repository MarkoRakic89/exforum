@php
    // Fetch all active reservations for this offer (reserved or in_process)
    $reservations = $offer->reservations()
        ->whereNotIn('state', ['canceled'])
        ->orderBy('reserved_at')
        ->with(['buyer'])
        ->get();
@endphp

@if($reservations->isEmpty() && $matches->isEmpty())
    <em>Nema odgovarajućih kupaca za ovu ponudu.</em>
@else
    <ul class="list-unstyled mb-0">
        {{-- First display any existing reservations so the seller can continue the process --}}
        @foreach($reservations as $res)
            @php
                $buyer = $res->buyer;
            @endphp
            <li class="d-flex align-items-center py-2 border-bottom bg-light">
                <div class="me-3" style="width: 50px; text-align: center;">
                    @if($buyer && $buyer->avatar)
                        <img src="{{ asset($buyer->avatar) }}" alt="Avatar" class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover;">
                    @else
                        <span class="avatar-placeholder rounded-circle d-inline-block bg-secondary" style="width: 40px; height: 40px;"></span>
                    @endif
                </div>
                <div class="flex-grow-1">
                    <strong>{{ $buyer->naziv ?? '-' }}</strong>
                    @if($buyer && $buyer->description)
                        <small class="d-block text-muted">{{ \Illuminate\Support\Str::limit($buyer->description, 80) }}</small>
                    @endif
                    <small class="d-block">
                        <strong>Status:</strong> @include('partials.status-badge', ['status' => $res->state])
                    </small>
                    <small class="d-block">
                        Iznos rezervisan: {{ number_format($res->amount_reserved_eur, 2) }} €
                    </small>
                    @if($res->messages_count ?? 0)
                        {{-- Display message count if available --}}
                        <small class="d-block text-muted">Poruka: {{ $res->messages()->count() }}</small>
                    @endif
                </div>
                <div class="d-flex flex-column flex-md-row gap-1 ms-auto">
                    <a href="{{ route('messages.index', $res->id) }}"
                       data-modal-url="{{ route('messages.modal', $res->id) }}"
                       class="btn btn-sm btn-outline-primary js-open-chat">
                        Poruke ({{ $res->messages()->count() }})
                    </a>
                    @if($res->state === 'reserved')
                        <form method="POST" action="{{ route('reservations.confirm', $res->id) }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-success">Potvrdi</button>
                        </form>
                        <form method="POST" action="{{ route('reservations.cancel', $res->id) }}" class="d-inline" onsubmit="return confirm('Da li ste sigurni da želite otkazati rezervaciju?');">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-danger">Otkaži</button>
                        </form>
                    @elseif($res->state === 'in_process')
                        <form method="POST" action="{{ route('reservations.complete', $res->id) }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-success">Završi</button>
                        </form>
                        <form method="POST" action="{{ route('reservations.cancel', $res->id) }}" class="d-inline" onsubmit="return confirm('Da li ste sigurni da želite otkazati rezervaciju?');">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-danger">Otkaži</button>
                        </form>
                    @elseif($res->state === 'completed')
                        <span class="badge bg-success">Završeno</span>
                    @elseif($res->state === 'canceled')
                        <span class="badge bg-danger">Otkazano</span>
                    @endif
                </div>
            </li>
        @endforeach

        {{-- Then display recommended matches, sorted as originally --}}
        @foreach($matches as $match)
            @php
                $matchUser = $match->user;
                $userCity = optional($matchUser->city)->name;
                $userIndustry = optional($matchUser->industry)->name;
                $reserveAmount = min(
                    $offer->remaining_amount,
                    $match->amount_eur
                );
            @endphp
            <li class="d-flex align-items-center py-2 border-bottom">
                <div class="me-3" style="width: 50px; text-align: center;">
                    @if($matchUser->avatar)
                        <img src="{{ asset($matchUser->avatar) }}" alt="Avatar" class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover;">
                    @else
                        <span class="avatar-placeholder rounded-circle d-inline-block bg-secondary" style="width: 40px; height: 40px;"></span>
                    @endif
                </div>
                <div class="flex-grow-1">
                    <strong>{{ $matchUser->naziv }}</strong>
                    @if($matchUser->description)
                        <small class="d-block text-muted">{{ \Illuminate\Support\Str::limit($matchUser->description, 80) }}</small>
                    @endif
                    <small class="text-muted d-block">
                        <strong>Grad:</strong> {{ $userCity }}
                    </small>
                    <small class="text-muted d-block">
                        <strong>Delatnost:</strong> {{ $userIndustry }}
                    </small>
                    <small class="d-block">
                        {{ number_format($match->amount_eur, 2) }} € |
                        {{ $match->percent }}% |
                        Ocena: {{ number_format($matchUser->avg_rating, 2) }}
                        ({{ $matchUser->ratings_count }})
                    </small>
                </div>
                <button type="button"
                    class="btn btn-sm btn-success js-contact-btn ms-auto"
                    data-offer-id="{{ $offer->id }}"
                    data-buyer-id="{{ $matchUser->id }}"
                    data-reserve-amount="{{ $reserveAmount }}"
                    data-max-amount="{{ $reserveAmount }}">
                    Kontaktiraj {{ number_format($reserveAmount, 2) }} €
                </button>
            </li>
        @endforeach
    </ul>
@endif