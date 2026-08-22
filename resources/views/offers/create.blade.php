@extends('layouts.contentNavbarLayout')

@section('content')
<h1>Nova ponuda</h1>
<form method="POST" action="{{ route('offers.store') }}" class="realtime-validation">
    @csrf
    <div class="row mb-3">
        <div class="col-md-6">
            <label class="form-label">Tip</label>
            <select name="type" class="form-select" required>
                <option value="sell">Prodajem</option>
                <option value="buy">Kupujem</option>
            </select>
            <div class="invalid-feedback"></div>
        </div>
        <div class="col-md-6">
            <label class="form-label">Budžet (€)</label>
            <input type="number" step="0.01" name="amount_eur" class="form-control" data-min="0.01"
                data-max="{{ (int)$maxAmount }}" required>
            <div class="invalid-feedback"></div>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-md-6">
            <label class="form-label">Procenat (%)</label>
            <input type="number" step="0.01" name="percent" class="form-control" data-min="1"
                data-max="{{ (int)$maxPercent }}" required>
            <div class="invalid-feedback"></div>
        </div>
        <div class="col-md-6">
            <label class="form-label">Ponavljanje</label>
            <select name="repeat_type" class="form-select" id="repeat_type" required>
                <option value="once">Jednom</option>
                <option value="monthly">Svaki mesec</option>
            </select>
            <div class="invalid-feedback"></div>
        </div>
    </div>
    <div class="mb-3" id="repeat_until_container" style="display: none;">
        <label class="form-label">Ponavljaj do (ako mesečno)</label>
        <input type="date" name="repeat_until" class="form-control">
        <div class="invalid-feedback"></div>
    </div>
    <div class="mb-3">
        <label class="form-label">Gradovi</label>
        <input type="text" class="form-control mb-2" placeholder="Pretraži gradove..." oninput="filterOptions(this, 'create-city-options')">
        <div class="card border">
            <div class="card-body" style="max-height: 250px; overflow-y: auto;" id="create-city-options">
                @foreach($cities as $city)
                <div class="form-check mb-1">
                    <input class="form-check-input" type="checkbox" name="cities[]" id="create_city_{{ $city->id }}" value="{{ $city->id }}">
                    <label class="form-check-label" for="create_city_{{ $city->id }}">{{ $city->name }}</label>
                </div>
                @endforeach
            </div>
        </div>
        <div class="invalid-feedback"></div>
    </div>
    <div class="mb-3">
        <label class="form-label">Delatnosti</label>
        <input type="text" class="form-control mb-2" placeholder="Pretraži delatnosti..." oninput="filterOptions(this, 'create-industry-options')">
        <div class="card border">
            <div class="card-body" style="max-height: 250px; overflow-y: auto;" id="create-industry-options">
                @foreach($industries as $industry)
                <div class="form-check mb-1">
                    <input class="form-check-input" type="checkbox" name="industries[]" id="create_industry_{{ $industry->id }}" value="{{ $industry->id }}">
                    <label class="form-check-label" for="create_industry_{{ $industry->id }}">{{ $industry->name }}</label>
                </div>
                @endforeach
            </div>
        </div>
        <div class="invalid-feedback"></div>
    </div>
    <button type="submit" class="btn btn-primary">Kreiraj ponudu</button>
</form>
<script>
    // Show/Hide repeat-until field
    document.addEventListener('DOMContentLoaded', function() {
        const repeatSelect = document.getElementById('repeat_type');
        const repeatContainer = document.getElementById('repeat_until_container');
        if (repeatSelect) {
            repeatSelect.addEventListener('change', function() {
                repeatContainer.style.display = this.value === 'monthly' ? 'block' : 'none';
            });
        }
        // Move selected cities/industries to the top when checked
        document.addEventListener('change', function(e) {
            const input = e.target;
            if (!input.classList.contains('form-check-input')) return;
            const parent = input.closest('.form-check');
            if (!parent || !input.checked) return;
            const container = parent.parentElement;
            if (!container) return;
            if (container.id === 'create-city-options' || container.id === 'create-industry-options') {
                container.insertBefore(parent, container.firstChild);
            }
        });
    });
    /**
     * Filter city/industry checkbox lists in create offer page.  Hides items
     * whose label does not include the search query.
     */
    function filterOptions(input, containerId) {
        const filter = input.value.toLowerCase();
        const container = document.getElementById(containerId);
        if (!container) return;
        const items = container.querySelectorAll('.form-check');
        items.forEach(item => {
            const label = item.querySelector('label');
            if (!label) return;
            const text = label.textContent.toLowerCase();
            item.style.display = text.includes(filter) ? '' : 'none';
        });
    }
</script>
@endsection