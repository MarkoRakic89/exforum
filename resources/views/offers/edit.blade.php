@extends('layouts.contentNavbarLayout')

@section('content')
<h1>Izmeni ponudu</h1>

<div class="card">
    <div class="card-body">

        @if($offer->type === 'sell')
        @include('offers._edit_sell_form', [
        'cities' => $cities,
        'industries' => $industries,
        'maxPercent' => $maxPercent,
        'maxAmount' => $maxAmount,
        ])
        @else
        @include('offers._edit_buy_form', [
        'cities' => $cities,
        'industries' => $industries,
        'maxPercent' => $maxPercent,
        'maxAmount' => $maxAmount,
        ])
        @endif
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Refresh matches for a specific offer
        document.querySelectorAll('.js-refresh-matches').forEach(btn => {
            btn.addEventListener('click', function() {
                const offerId = this.getAttribute('data-offer-id');
                const container = document.getElementById('matches-container-' + offerId);
                if (!container) return;
                container.innerHTML = '<div class="text-center py-2"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Učitavanje...</div>';
                fetch(`{{ url('/offers') }}/` + offerId + '/matches')
                    .then(response => response.json())
                    .then(data => {
                        container.innerHTML = data.html;
                    })
                    .catch(() => {
                        container.innerHTML = '<em>Greška pri osvežavanju kupaca.</em>';
                    });
            });
        });
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
        // Show history modal when clicking the history button
        document.querySelectorAll('.js-show-history').forEach(btn => {
            btn.addEventListener('click', function() {
                const offerId = this.getAttribute('data-offer-id');
                const dataEl = document.getElementById('history-data-' + offerId);
                if (!dataEl) return;
                const encoded = dataEl.getAttribute('data-history');
                if (!encoded) return;
                const history = JSON.parse(atob(encoded));
                let html = '';
                if (!history || history.length === 0) {
                    html = '<p>Nema rezervacija za ovu ponudu.</p>';
                } else {
                    html += '<div class="table-responsive"><table class="table table-bordered table-sm"><thead><tr><th>ID</th><th>Budžet (€)</th><th>Kupac</th><th>Prodavac</th><th>Status</th><th>Rezervisano</th><th>Potvrđeno</th><th>Završeno</th><th>Otkazano</th></tr></thead><tbody>';
                    history.forEach(item => {
                        html += `<tr><td>${item.id}</td><td>${parseFloat(item.amount).toFixed(2)}</td><td>${item.buyer || ''}</td><td>${item.seller || ''}</td><td>${item.state}</td><td>${item.reserved_at || ''}</td><td>${item.confirmed_at || ''}</td><td>${item.completed_at || ''}</td><td>${item.canceled_at || ''}</td></tr>`;
                    });
                    html += '</tbody></table></div>';
                }
                document.getElementById('historyModalBody').innerHTML = html;
                const historyModal = new bootstrap.Modal(document.getElementById('historyModal'));
                historyModal.show();
            });
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
    });
</script>
@endsection