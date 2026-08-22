@extends('layouts.contentNavbarLayout')

@section('content')
<h1>Moje ponude i rezervacije</h1>

<!-- Company Information Card (collapsed by default) -->
<div class="card mb-4">
    <!-- Make the entire header clickable to toggle the collapse -->
    <div class="card-header d-flex justify-content-between align-items-center" role="button" data-bs-toggle="collapse" data-bs-target="#collapseCompany" aria-expanded="false" aria-controls="collapseCompany">
        <span class="h4 mb-0 text-body">Podaci o firmi</span>
        <i class="ri ri-arrow-down-s-line"></i>
    </div>
    <div id="collapseCompany" class="collapse">
        <div class="card-body">
            <!-- Show session status if present -->
            @if(session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
            @endif
            <p><strong>Naziv:</strong> {{ $user->naziv }}</p>
            <p><strong>Matični broj:</strong> {{ $user->maticni_broj }}</p>
            <p><strong>Email (za notifikacije):</strong> {{ $user->email }}</p>
            <p><strong>Grad:</strong> {{ $user->city->name ?? '-' }}</p>
            <p><strong>Prosečna ocena:</strong> {{ number_format($user->avg_rating, 2) }} ({{ $user->ratings_count }}
                ocena)</p>
            <!-- Display avatar if available -->
            @if($user->avatar)
            <p><strong>Avatar:</strong><br>
                <img src="{{ asset($user->avatar) }}" alt="Avatar" class="rounded-circle" style="width: 80px; height: 80px;">
            </p>
            @endif
            @if($user->description)
            <p><strong>Opis:</strong> {{ $user->description }}</p>
            @endif
            <!-- Profile update form -->
            <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="mt-4">
                @csrf
                <div class="mb-3">
                    <label for="avatar" class="form-label">Avatar (slika)</label>
                    <input type="file" name="avatar" id="avatar" class="form-control @error('avatar') is-invalid @enderror">
                    @error('avatar')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Opis čime se bavite</label>
                    <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description', $user->description) }}</textarea>
                    @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <button type="submit" class="btn btn-primary">Sačuvaj podatke</button>
            </form>
        </div>
    </div>
</div>

<!-- Active Sell Offers Card -->
<div class="card mb-4" id="sell-offers-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-start flex-column">
            <a class="h4 mb-0 text-body" data-bs-toggle="collapse" href="#collapseSellOffers" role="button"
                aria-expanded="false" aria-controls="collapseSellOffers">Aktivne ponude - Prodaja</a>
            (Može biti samo jedna Aktivna ponuda Prodaje)
        </div>
        <div class="d-flex align-items-center">
            @if(!$hasActiveSellOffer)
            <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal"
                data-bs-target="#createSellOfferModal">
                <i class="ri ri-add-circle-line me-1"></i> Nova ponuda prodaje
            </button>
            @else
            <button type="button" class="btn btn-secondary btn-sm" disabled title="Imate aktivnu ponudu prodaje">
                <i class="ri ri-add-circle-line me-1"></i> Nova ponuda prodaje
            </button>
            @endif
            <button class="btn btn-sm btn-link text-body ms-2" data-bs-toggle="collapse"
                data-bs-target="#collapseSellOffers"><i class="ri ri-arrow-down-s-line"></i></button>
        </div>
    </div>
    <div id="collapseSellOffers" class="collapse {{ $activeSellOffers->isEmpty() ? '' : 'show' }}">
        <div class="card-body">
            @if($activeSellOffers->isEmpty())
            <p>Nema aktivnih ponuda.</p>
            @else
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Budžet (€)</th>
                            <th>Procenat (%)</th>
                            <th>Status</th>
                            <th>Kreirana</th>
                            <th>Akcije</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($activeSellOffers as $offer)
                        <tr>
                            <td>{{ $offer->id }}</td>
                            <td>{{ number_format($offer->amount_eur, 2) }}</td>
                            <td>{{ $offer->percent }}</td>
                            <td>@include('partials.status-badge', ['status' => $offer->status])</td>
                            <td>{{ $offer->created_at->format('d.m.Y H:i') }}</td>
                            <td class="d-flex gap-1 flex-wrap">
                                <a href="{{ route('offers.edit', $offer->id) }}"
                                    class="btn btn-sm btn-outline-primary">Izmeni</a>
                                <form method="POST" action="{{ route('offers.destroy', $offer->id) }}" class="d-inline"
                                    onsubmit="return confirm('Da li ste sigurni da želite deaktivirati ponudu?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Deaktiviraj</button>
                                </form>
                            </td>
                        </tr>
                        <tr class="table-secondary">
                            <td colspan="6" class="p-2">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <strong>Kupci (preporučene rezervacije)</strong>
                                    <button type="button" class="btn btn-sm btn-outline-secondary js-refresh-matches" id="js-refresh-matches"
                                        data-offer-id="{{ $offer->id }}">Osveži</button>
                                </div>
                                <div id="matches-container-{{ $offer->id }}">
                                    @php
                                    $data = $offerMatches[$offer->id] ?? null;
                                    $matches = $data['matches'] ?? collect();
                                    $hasMore = $data['hasMore'] ?? false;
                                    $page = $data['page'] ?? 1;
                                    @endphp
                                    @include('offers.partials.match_list', ['offer' => $offer, 'matches' => $matches])
                                    @if($hasMore)
                                    <div class="text-center mt-2">
                                        <button class="btn btn-sm btn-outline-primary js-load-more-matches"
                                            data-offer-id="{{ $offer->id }}" data-page="{{ $page + 1 }}">Učitaj
                                            još</button>
                                    </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Active Buy Offers Card -->
<div class="card mb-4" id="buy-offers-card">
    <div class="card-header d-flex justify-content-between align-items-center" data-bs-toggle="collapse">
        <div class="d-flex align-items-start flex-column">
            <a class="h4 mb-0 text-body" href="#collapseBuyOffers" role="button"
                aria-expanded="false" aria-controls="collapseBuyOffers">Aktivne ponude - Kupovina</a>
            (Može biti samo jedna Aktivna ponuda kupovine)
        </div>
        <div class="d-flex align-items-center">
            @if(!$hasActiveBuyOffer)
            <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal"
                data-bs-target="#createBuyOfferModal">
                <i class="ri ri-add-circle-line me-1"></i> Nova ponuda kupovine
            </button>
            @else
            <button type="button" class="btn btn-secondary btn-sm" disabled title="Imate aktivnu ponudu kupovine">
                <i class="ri ri-add-circle-line me-1"></i> Nova ponuda kupovine
            </button>
            @endif
            <button class="btn btn-sm btn-link text-body ms-2"
                data-bs-target="#collapseBuyOffers"><i class="ri ri-arrow-down-s-line"></i></button>
        </div>
    </div>
    <div id="collapseBuyOffers" class="collapse {{ $activeBuyOffers->isEmpty() ? '' : 'show' }}">
        <div class="card-body">
            @if($activeBuyOffers->isEmpty())
            <p>Nema aktivnih ponuda.</p>
            @else
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Budžet (€)</th>
                            <th>Procenat (%)</th>
                            <th>Status</th>
                            <th>Kreirana</th>
                            <th>Akcije</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($activeBuyOffers as $offer)
                        <tr>
                            <td>{{ $offer->id }}</td>
                            <td>{{ number_format($offer->amount_eur, 2) }}</td>
                            <td>{{ $offer->percent }}</td>
                            <td>@include('partials.status-badge', ['status' => $offer->status])</td>
                            <td>{{ $offer->created_at->format('d.m.Y H:i') }}</td>
                            <td class="d-flex gap-1 flex-wrap">
                                <a href="{{ route('offers.edit', $offer->id) }}"
                                    class="btn btn-sm btn-outline-primary">Izmeni</a>
                                <form method="POST" action="{{ route('offers.destroy', $offer->id) }}" class="d-inline"
                                    onsubmit="return confirm('Da li ste sigurni da želite deaktivirati ponudu?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Deaktiviraj</button>
                                </form>
                            </td>
                        </tr>
                        <tr class="table-secondary">
                            <td colspan="6" class="p-2">
                                <div id="matches-container-{{ $offer->id }}">
                                    @php
                                        // Fetch all active reservations for this offer (reserved or in_process)
                                        $reservations = \App\Models\Reservation::where('buyer_id',$offer->user_id)
                                            ->whereNotIn('state', ['canceled'])
                                            ->orderBy('reserved_at')
                                            ->with(['seller'])
                                            ->get();
                                    @endphp

                                    @if($reservations->isEmpty())
                                        <em>Nema odgovarajućih kupaca za ovu ponudu.</em>
                                    @else
                                        <ul class="list-unstyled mb-0">
                                            {{-- First display any existing reservations so the seller can continue the process --}}
                                            @foreach($reservations as $res)
                                                @php
                                                    $buyer = $res->seller;
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
                                                        @if($res->state === 'in_process')
                                                            <!-- Buyer can now complete a reservation just like the seller -->
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
                                        </ul>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Completed Offers Card -->
<div class="card mb-4" id="completed-offers-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <a class="h4 mb-0 text-body" data-bs-toggle="collapse" href="#collapseCompletedOffers" role="button"
            aria-expanded="false" aria-controls="collapseCompletedOffers">Završene ponude</a>
        <button class="btn btn-sm btn-link text-body" data-bs-toggle="collapse"
            data-bs-target="#collapseCompletedOffers"><i class="ri ri-arrow-down-s-line"></i></button>
    </div>
    <div id="collapseCompletedOffers" class="collapse">
        <div class="card-body">
            @if($completedOffers->isEmpty())
            <p>Nema završenih ponuda.</p>
            @else
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Tip</th>
                            <th>Budžet (€)</th>
                            <th>Procenat (%)</th>
                            <th>Status</th>
                            <th>Kreirana</th>
                            <th>Akcije</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($completedOffers as $offer)
                        <tr>
                            <td>{{ $offer->id }}</td>
                            <td>{{ $offer->type === 'sell' ? 'Prodaja' : 'Kupovina' }}</td>
                            <td>{{ number_format($offer->amount_eur, 2) }}</td>
                            <td>{{ $offer->percent }}</td>
                            <td>@include('partials.status-badge', ['status' => $offer->status])</td>
                            <td>{{ $offer->created_at->format('d.m.Y H:i') }}</td>
                            <td>
                                <button type="button" class="btn btn-sm btn-outline-primary js-show-history"
                                    data-offer-id="{{ $offer->id }}">Istorija</button>
                                @if(($offer->type === 'sell' &&!$hasActiveSellOffer) || ($offer->type === 'buy'
                                &&!$hasActiveBuyOffer))
                                <form method="POST" action="{{ route('offers.clone', $offer->id) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-primary">Ponovi</button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        <!-- Hidden element containing history data encoded as base64 JSON -->
                        <tr class="d-none">
                            <td colspan="7">
                                @php
                                // Prepare reservation history data as a base64-encoded JSON string. We do this here to
                                // embedding complex PHP logic directly in the HTML attribute.
                                $historyData = base64_encode(json_encode(
                                $offer->reservations()->with(['buyer','seller'])->orderBy('reserved_at')->get()->map(function($res)
                                {
                                return [
                                'id' => $res->id,
                                'amount' => $res->amount_reserved_eur,
                                'buyer' => optional($res->buyer)->naziv,
                                'seller' => optional($res->seller)->naziv,
                                'state' => $res->state,
                                'reserved_at' => optional($res->reserved_at)->format('d.m.Y H:i'),
                                'confirmed_at' => optional($res->confirmed_at)->format('d.m.Y H:i'),
                                // updated_at can be used to signal when the reserved amount changed.
                                'updated_at' => optional($res->updated_at)->format('d.m.Y H:i'),
                                'completed_at' => optional($res->completed_at)->format('d.m.Y H:i'),
                                'canceled_at' => optional($res->canceled_at)->format('d.m.Y H:i'),
                                ];
                                })
                                ));
                                @endphp
                                <div id="history-data-{{ $offer->id }}" data-history="{{ $historyData }}"></div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- The reservation history tables (both as seller and buyer) are intentionally
removed from the profile page.  In the redesigned workflow, all
communication and reservation management occurs within the context of
each active offer.  Users can view and manage conversations and
reservations directly via the offer’s match list and chat modal.  Keeping
the profile focused on active offers simplifies the interface and avoids
duplicated information. --}}
{{-- Create Offer Modal (reusing existing partial) --}}
<!-- Separate modals for creating new offers: one for selling and one for buying -->

<div class="modal fade" id="createSellOfferModal" tabindex="-1" aria-labelledby="createSellOfferModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="height: auto;">
            <div class="modal-header">
                <h5 class="modal-title" id="createSellOfferModalLabel">Kreiraj novu ponudu prodaje</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Vaša agencija ima prostora za dodatne usluge za ovaj mesec. Unesite koliko i obeležite ukoliko je to mogućnost svakog meseca</p>
                @include('offers._create_sell_form', [
                'cities' => $cities,
                'industries' => $industries,
                'maxPercent' => $maxPercent,
                'maxAmount' => $maxAmount,
                ])
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="createBuyOfferModal" tabindex="-1" aria-labelledby="createBuyOfferModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="height: auto;">
            <div class="modal-header">
                <h5 class="modal-title" id="createBuyOfferModalLabel">Kreiraj novu ponudu kupovine</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Kompanije imaju potrebe za vašom uslugom, ali su sa ograničenim budžetom. Vi možete da im pružite usluge u skladu sa vašim mogućnostima koje mogu u potpunosti ili delimično pokriti budžet. Ispod navedite koliko imate prostora, i opciono ostale od agencija</p>
                @include('offers._create_buy_form', [
                'cities' => $cities,
                'industries' => $industries,
                'maxPercent' => $maxPercent,
                'maxAmount' => $maxAmount,
                ])
            </div>
        </div>
    </div>
</div>

{{-- History Modal --}}
<div class="modal fade" id="historyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Istorija ponude</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="historyModalBody">
                <!-- Popunjeno dinamički -->
            </div>
        </div>
    </div>
</div>

{{-- Contact Modal for initiating chat with a buyer --}}
<div class="modal fade" id="contactModal" tabindex="-1" aria-labelledby="contactModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="contactModalLabel">Pošalji poruku kupcu</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Zatvori"></button>
            </div>
            <div class="modal-body">
                <form id="contactModalForm">
                    @csrf
                    <input type="hidden" name="offer_id">
                    <input type="hidden" name="buyer_id">
                    <div class="mb-3">
                        <label for="contactAmount" class="form-label">Iznos rezervacije (€)</label>
                        <input type="number" name="amount_reserved_eur" id="contactAmount" class="form-control" step="0.01" min="0.01">
                    </div>
                    <div class="mb-3">
                        <label for="contactMessage" class="form-label">Poruka</label>
                        <textarea name="message" id="contactMessage" class="form-control" rows="3" placeholder="Unesite poruku (opciono)"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Zatvori</button>
                <button type="button" class="btn btn-primary" id="contactModalSendBtn">Pošalji</button>
            </div>
        </div>
    </div>
</div>

{{-- Chat Modal for viewing reservation conversations in a modal.  When a user
    clicks on a "Poruke" button marked with the .js-open-chat class, this
    modal is opened and the corresponding chat is loaded via AJAX into the
    `#chatModalContent` element.  Using a div instead of an iframe allows
    seamless integration with the page and avoids navigating away from
    the profile. --}}
<div class="modal fade" id="chatModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Poruke</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div id="chatModalContent" class="p-3"></div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.js-refresh-matches');
        if (!btn) return;

        console.log('test');

        const offerId = btn.getAttribute('data-offer-id');
        const container = document.getElementById('matches-container-' + offerId);
        if (!container) return;

        container.innerHTML =
            '<div class="text-center py-2">' +
            '<span class="spinner-border spinner-border-sm"></span> Učitavanje...' +
            '</div>';

        fetch(`{{ url('/offers') }}/` + offerId + '/matches')
            .then(r => r.json())
            .then(data => {
                container.innerHTML = data.html;
            })
            .catch(() => {
                container.innerHTML = '<em>Greška pri osvežavanju kupaca.</em>';
            });
    });
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.js-show-history');
        if (!btn) return;

        const offerId = btn.getAttribute('data-offer-id');
        const dataEl = document.getElementById('history-data-' + offerId);
        if (!dataEl) return;

        const encoded = dataEl.getAttribute('data-history');
        if (!encoded) return;

        const history = JSON.parse(atob(encoded));

        let html = '';

        if (!history || history.length === 0) {
            html = '<p>Nema rezervacija za ovu ponudu.</p>';
        } else {
            html += `
            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Iznos (€)</th>
                            <th>Kupac</th>
                            <th>Prodavac</th>
                            <th>Status</th>
                            <th>Napravljena</th>
                            <th>Kontaktiran</th>
                            <th>Promenjen iznos</th>
                            <th>Završena</th>
                            <th>Otkazana</th>
                        </tr>
                    </thead>
                    <tbody>
        `;

            history.forEach(item => {
                html += `
                <tr>
                    <td>${item.id}</td>
                    <td>${parseFloat(item.amount).toFixed(2)}</td>
                    <td>${item.buyer || ''}</td>
                    <td>${item.seller || ''}</td>
                    <td>${item.state}</td>
                    <td>${item.reserved_at || ''}</td>
                    <td>${item.confirmed_at || ''}</td>
                    <td>${item.updated_at || ''}</td>
                    <td>${item.completed_at || ''}</td>
                    <td>${item.canceled_at || ''}</td>
                </tr>
            `;
            });

            html += `
                    </tbody>
                </table>
            </div>
        `;
        }

        document.getElementById('historyModalBody').innerHTML = html;

        const historyModal = new bootstrap.Modal(
            document.getElementById('historyModal')
        );

        historyModal.show();
    });

    // Note: We previously attempted to reset validation styles after a modal
    // hides by targeting an element with id "yourModalId".  That element
    // does not exist and caused a JavaScript error preventing the contact
    // modal from opening.  The following event listener has been removed
    // entirely to ensure scripts run without interruption.  Validation
    // feedback will be reset on form submission instead.

    function normalize(str) {
        return str
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '');
    }

    function filterOptions(input, containerId) {
        const filter = normalize(input.value);
        const container = document.getElementById(containerId);
        if (!container) return;

        const items = container.querySelectorAll('.form-check');

        items.forEach(item => {
            const label = item.querySelector('label');
            if (!label) return;

            const text = normalize(label.textContent);
            item.style.display = text.includes(filter) ? '' : 'none';
        });
    }
    document.addEventListener('DOMContentLoaded', function() {
        // Refresh matches for a specific offer
        // Delegate click for loading more matches
        document.addEventListener('click', function(e) {
            const target = e.target;
            if (target.classList.contains('js-load-more-matches')) {
                const offerId = target.getAttribute('data-offer-id');
                let page = parseInt(target.getAttribute('data-page'));
                const container = document.getElementById('matches-container-' + offerId);
                if (!container) return;
                target.disabled = true;
                fetch(`{{ url('/offers') }}/` + offerId + '/matches?page=' + page)
                    .then(response => response.json())
                    .then(data => {
                        // Remove existing "load more" button
                        const loadBtns = container.querySelectorAll('.js-load-more-matches');
                        loadBtns.forEach(btn => btn.parentElement.remove());
                        // Append new content
                        const temp = document.createElement('div');
                        temp.innerHTML = data.html;
                        container.appendChild(temp);
                        if (data.hasMore) {
                            const wrapper = document.createElement('div');
                            wrapper.className = 'text-center mt-2';
                            wrapper.innerHTML = `<button class="btn btn-sm btn-outline-primary js-load-more-matches" data-offer-id="${offerId}" data-page="${page + 1}">Učitaj još</button>`;
                            container.appendChild(wrapper);
                        }
                    })
                    .catch(() => {
                        alert('Greška pri učitavanju kupaca.');
                    });
            }
        });

        // Make entire card headers clickable (except interactive buttons).
        // When the user clicks anywhere on a card header that is not a button or an anchor with
        // the `btn` class, toggle the associated collapse panel.  This improves the UX by
        // allowing users to expand/collapse sections without needing to click only the arrow.
        document.querySelectorAll('.card').forEach(card => {
            const header = card.querySelector('.card-header');
            if (!header) return;
            header.addEventListener('click', function(e) {
                // ignore clicks on buttons (including the add/new buttons) or anchor buttons
                if (e.target.closest('button') || e.target.closest('a.btn')) {
                    return;
                }
                const collapse = header.nextElementSibling;
                if (collapse && collapse.classList.contains('collapse')) {
                    const bsCollapse = new bootstrap.Collapse(collapse, {
                        toggle: true
                    });
                }
            });
        });

        // Handle clicks on contact buttons to open a modal for sending a message and reserving.
        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.js-contact-btn');
            if (!btn) return;
            const offerId = btn.getAttribute('data-offer-id');
            const buyerId = btn.getAttribute('data-buyer-id');
            // Determine the suggested reserve amount.  Support both legacy
            // data-amount and the newer data-reserve-amount attributes.
            let amount = btn.getAttribute('data-reserve-amount');
            if (!amount) {
                amount = btn.getAttribute('data-amount');
            }
            const form = document.getElementById('contactModalForm');
            form.querySelector('input[name="offer_id"]').value = offerId;
            form.querySelector('input[name="buyer_id"]').value = buyerId;
            // Populate and set constraints on the reservation amount input.  If
            // the element exists, set its value and max attribute (if provided on
            // the button).
            const amountInput = form.querySelector('input[name="amount_reserved_eur"]');
            if (amountInput) {
                amountInput.value = amount || '';
                const maxAttr = btn.getAttribute('data-max-amount');
                if (maxAttr) {
                    amountInput.max = maxAttr;
                } else {
                    amountInput.removeAttribute('max');
                }
            }
            // Clear previous message
            const msgInput = form.querySelector('textarea[name="message"]');
            if (msgInput) {
                msgInput.value = '';
            }
            // Show the modal
            const modal = new bootstrap.Modal(document.getElementById('contactModal'));
            modal.show();
        });

        // Send the message and reservation when the user clicks "Pošalji" in the modal.
        const contactSendBtn = document.getElementById('contactModalSendBtn');
        if (contactSendBtn) {
            contactSendBtn.addEventListener('click', function() {
                const form = document.getElementById('contactModalForm');
                const formData = new FormData(form);
                fetch("{{ route('reservations.store') }}", {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value
                    },
                    body: formData
                }).then(response => {
                    if (response.ok) {
                        // Reload the page to reflect new reservation and chat state
                        location.reload();
                    } else if (response.status === 422) {
                        response.json().then(data => {
                            let msg = 'Greška:';
                            if (data.errors) {
                                msg = Object.values(data.errors).map(arr => arr.join(', ')).join('\n');
                            }
                            alert(msg);
                        });
                    } else {
                        alert('Došlo je do greške prilikom slanja poruke.');
                    }
                }).catch(() => {
                    alert('Greška pri slanju poruke.');
                });
            });
        }
    });

    // Global click handler for opening reservation chats in a modal.  When a
    // user clicks on an element with the `.js-open-chat` class, prevent
    // navigation, fetch the chat HTML via AJAX and inject it into the
    // chat modal.  After loading the chat, attach an event listener to
    // handle message submissions via fetch.  This avoids using an iframe
    // and keeps the user on the profile page.
    document.addEventListener('click', function(e) {
        const link = e.target.closest('.js-open-chat');
        if (!link) return;
        e.preventDefault();
        // Determine the URL used to load the chat via AJAX.  If the link has a
        // `data-modal-url` attribute (set in the Blade templates), use that.  Otherwise,
        // derive it by appending `/modal` to the standard messages URL.
        let modalUrl;
        const modalAttr = link.getAttribute('data-modal-url');
        if (modalAttr) {
            modalUrl = modalAttr;
        } else {
            const href = link.getAttribute('href');
            if (!href) return;
            // Fall back to the messages index with a query parameter indicating modal usage.
            // The backend will detect ajax/modal requests and return the partial view.
            if (href.includes('?')) {
                modalUrl = href + '&modal=1';
            } else {
                modalUrl = href + '?modal=1';
            }
        }
        const modalEl = document.getElementById('chatModal');
        const container = document.getElementById('chatModalContent');
        if (!modalEl || !container) return;
        // Fetch the chat HTML and inject it into the container
        fetch(modalUrl, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        }).then(function(response) {
            return response.text();
        }).then(function(html) {
            container.innerHTML = html;
            // After injecting, attach submit handler to the chat form.  Define a helper
            // function that binds the submit event and rebinds itself after
            // reloading the conversation.  This avoids using `arguments.callee`
            // which is disallowed in strict mode.
            function attachChatFormHandler() {
                const chatForm = container.querySelector('#modalChatForm');
                if (!chatForm) return;
                chatForm.addEventListener('submit', function handleSubmit(ev) {
                    ev.preventDefault();
                    const actionUrl = chatForm.getAttribute('data-action');
                    const reloadUrl = chatForm.getAttribute('data-modal-url');
                    const formData = new FormData(chatForm);
                    // Send the message via AJAX
                    fetch(actionUrl, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': chatForm.querySelector('input[name="_token"]').value
                        },
                        body: formData
                    }).then(function(resp) {
                        if (resp.ok) {
                            // After sending a message, reload the chat content
                            return fetch(reloadUrl, {
                                headers: { 'X-Requested-With': 'XMLHttpRequest' }
                            }).then(function(r) { return r.text(); });
                        } else if (resp.status === 422) {
                            return resp.json().then(function(data) {
                                let msg = 'Greška:';
                                if (data.errors) {
                                    msg = Object.values(data.errors).map(function(arr) {
                                        return arr.join(', ');
                                    }).join('\n');
                                }
                                alert(msg);
                                throw new Error('validation');
                            });
                        } else {
                            alert('Došlo je do greške prilikom slanja poruke.');
                            throw new Error('send error');
                        }
                    }).then(function(updatedHtml) {
                        // Replace chat content with the updated conversation
                        container.innerHTML = updatedHtml;
                        // Reattach the handler for the new form
                        attachChatFormHandler();
                    }).catch(function(err) {
                        // Errors are handled above
                    });
                }, { once: true });
            }
            // Bind the submit handler for the initial load
            attachChatFormHandler();
            // Show the modal after content has been loaded
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
        }).catch(function() {
            container.innerHTML = '<div class="p-3 text-danger">Greška pri učitavanju poruka.</div>';
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
        });
    });
</script>
@endsection