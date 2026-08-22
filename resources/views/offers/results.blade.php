@extends('layouts.contentNavbarLayout')

@section('content')
<h1>Rezultati pretrage kupaca</h1>

@if($matches->isEmpty())
<p>Nažalost, nema kupaca koji odgovaraju zadatim kriterijumima.</p>
@else
{{-- Prikaži kupce koji u potpunosti zadovoljavaju kriterijum iznosa --}}
<div class="card mb-4">
    <div class="card-header bg-tertiary text-white">
        <h4 class="mb-0">Kupci koji odgovaraju kriterijumu</h4>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Naziv kupca</th>
                        <th>Budžet (€)</th>
                        <th>Procenat (%)</th>
                        <th>Ocena</th>
                        <th>Akcija</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($matches as $offer)
                    @php
                    $user = $offer->user;
                    @endphp
                    @if($offer->amount_eur <= $amount) <tr>
                        <td>
                            <strong>{{ $user->naziv }}</strong>
                            @if($user->is_featured)
                            <span class="badge bg-warning text-dark ms-1">Featured</span>
                            @endif
                        </td>
                        <td>{{ number_format($offer->amount_eur, 2) }}</td>
                        <td>{{ $offer->percent }}</td>
                        <td>{{ number_format($user->avg_rating, 2) }} ({{ $user->ratings_count }})</td>
                        <td>
                            <!-- Use a button to launch a contact modal instead of submitting directly -->
                            <button type="button" class="btn btn-sm btn-success js-contact-btn"
                                data-offer-id="{{ $offer->id }}"
                                data-buyer-id="{{ $offer->user_id }}"
                                data-reserve-amount="{{ number_format($offer->amount_eur, 2, '.', '') }}"
                                data-max-amount="{{ number_format($offer->amount_eur, 2, '.', '') }}">
                                Kontaktiraj
                            </button>
                        </td>
                        </tr>
                        @endif
                        @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Kupci koji skoro zadovoljavaju kriterijum (do 10% više od traženog iznosa) --}}
@php
$near = $matches->filter(function($offer) use($amount){
return $offer->amount_eur > $amount && $offer->amount_eur <= $amount * 1.1; }); @endphp @if($near->isNotEmpty())
    <div class="card">
        <div class="card-header bg-info text-dark">
            <h4 class="mb-0">Kupci čiji iznos premašuje kriterijum za do 10%</h4>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Naziv kupca</th>
                            <th>Budžet (€)</th>
                            <th>Procenat (%)</th>
                            <th>Ocena</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($near as $offer)
                        @php $user = $offer->user; @endphp
                        <tr class="bg-light">
                            <td>
                                {{ $user->naziv }}
                                @if($user->is_featured)
                                <span class="badge bg-warning text-dark ms-1">Featured</span>
                                @endif
                            </td>
                            <td>{{ number_format($offer->amount_eur, 2) }}</td>
                            <td>{{ $offer->percent }}</td>
                            <td>{{ number_format($user->avg_rating, 2) }} ({{ $user->ratings_count }})</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
    @endif
    @endsection

{{-- Contact modal markup and script --}}

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

<script>
// Contact modal behaviour for the search results page
document.addEventListener('click', function(e) {
    const btn = e.target.closest('.js-contact-btn');
    if (!btn) return;
    const offerId = btn.getAttribute('data-offer-id');
    const buyerId = btn.getAttribute('data-buyer-id');
    let amount = btn.getAttribute('data-reserve-amount');
    if (!amount) {
        amount = btn.getAttribute('data-amount');
    }
    const form = document.getElementById('contactModalForm');
    form.querySelector('input[name="offer_id"]').value = offerId;
    form.querySelector('input[name="buyer_id"]').value = buyerId;
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
    const msgInput = form.querySelector('textarea[name="message"]');
    if (msgInput) {
        msgInput.value = '';
    }
    const modal = new bootstrap.Modal(document.getElementById('contactModal'));
    modal.show();
});

// Handle sending the reservation/message from the modal
document.addEventListener('DOMContentLoaded', function() {
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
</script>