<form method="POST" action="{{ route('offers.update', $offer->id) }}" class="realtime-validation">
    @csrf
    @method('PUT')
    <div class="row mb-3">
        <div class="col-md-6">
            <label class="form-label">Budžet (€)</label>
            <select name="amount_eur" class="form-select" required>
                @for($i = 500; $i <= (int)$maxAmount; $i +=500) <option {{ old('amount_eur', $offer->amount_eur) == $i ? 'selected' : '' }} value="{{ $i }}">{{ $i }}</option>
                    @endfor
            </select>
            <div class="invalid-feedback"></div>
        </div>
        <div class="col-md-6">
            <label class="form-label">Procenat (%)</label>
            <select name="percent" class="form-select" required>
                @for($p = 1; $p <= (int)$maxPercent; $p++) <option {{ old('percent', $offer->percent) == $p ? 'selected' : '' }} value="{{ $p }}">{{ $p }}</option>
                    @endfor
            </select>
            <div class="invalid-feedback"></div>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-md-6">
            @if($offer->type === 'buy')
            <label class="form-label">Ponovljanje</label>
            <select name="repeat_type" class="form-select" id="modal_buy_repeat_type" required>
                <option value="once" {{ old('repeat_type', $offer->repeat_type) === 'once' ? 'selected' : '' }}>Jednom</option>
                <option value="monthly" {{ old('repeat_type', $offer->repeat_type) === 'monthly' ? 'selected' : '' }}>Svaki mesec</option>
            </select>
            <div class="invalid-feedback"></div>
            @else
            <input type="hidden" name="repeat_type" value="once">
            @endif
        </div>
        <div class="col-md-6">
            <div class="mb-3" id="modal_buy_repeat_until_container" style="{{ $offer->repeat_type === 'monthly' ? '' : 'display:none;' }}">
                <label class="form-label">Ponavljaj do (ako mesečno)</label>
                <input type="date" name="repeat_until" class="form-control" value="{{ old('repeat_until', optional($offer->repeat_until)->format('Y-m-d')) }}">
                <div class="invalid-feedback"></div>
            </div>
        </div>
    </div>
    <div class="mb-3">
        <label class="form-label">Gradovi u kojima se nalaze firme koje mogu tražiti vaše usluge (favorizuje ali ne isključuje ostale)</label>
        <input type="text" class="form-control mb-2" placeholder="Pretraži gradove..."
            oninput="filterOptions(this, 'edit-city-options')">
        <div class="card border">
            <div class="card-body" style="max-height: 250px; overflow-y: auto;" id="edit-city-options">
                @foreach($cities as $city)
                <div class="form-check mb-1">
                    <input class="form-check-input" type="checkbox" name="cities[]" id="edit_city_{{ $city->id }}" value="{{ $city->id }}"
                        {{ in_array($city->id, $offer->cities->pluck('id')->toArray()) ? 'checked' : '' }}>
                    <label class="form-check-label" for="edit_city_{{ $city->id }}">
                        {{ $city->name }}
                    </label>
                </div>
                @endforeach
            </div>
        </div>
        <div class="invalid-feedback"></div>
    </div>
    <div class="d-flex justify-content-end">
        <button type="submit" class="btn btn-primary">Sačuvaj izmene</button>
        <a href="{{ route('profile') }}#my-offers" class="btn btn-secondary ms-2">Otkaži</a>
    </div>
</form>
<script>
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
    // Show/hide repeat-until based on selection
    document.addEventListener('DOMContentLoaded', function() {
        const repeatSelect = document.getElementById('modal_buy_repeat_type');
        const untilContainer = document.getElementById('modal_buy_repeat_until_container');
        if (repeatSelect) {
            repeatSelect.addEventListener('change', function() {
                untilContainer.style.display = this.value === 'monthly' ? 'block' : 'none';
            });
        }
        // Move selected cities to top
        document.addEventListener('change', function(e) {
            const input = e.target;
            if (!input.classList.contains('form-check-input')) return;
            const parent = input.closest('.form-check');
            if (!parent || !input.checked) return;
            const container = parent.parentElement;
            if (!container) return;
            if (container.id === 'modal-buy-city-options') {
                container.insertBefore(parent, container.firstChild);
            }
        });
    });
    // AJAX submit with realtime validation for buy offers
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form.realtime-validation');
        if (!form) return;
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            // Clear previous errors
            form.querySelectorAll('.is-invalid').forEach(el => {
                el.classList.remove('is-invalid');
            });
            form.querySelectorAll('.invalid-feedback').forEach(el => {
                el.textContent = '';
            });
            const formData = new FormData(form);
            const url = form.getAttribute('action');
            fetch(url, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value
                },
                body: formData
            }).then(async response => {
                if (response.status === 422) {
                    const data = await response.json();
                    if (data.errors) {
                        Object.keys(data.errors).forEach(fieldKey => {
                            const baseField = fieldKey.replace(/\.\d+$/, '');
                            let inputEl = form.querySelector(`[name="${baseField}"]`) || form.querySelector(`[name="${baseField}[]"]`);
                            if (inputEl) {
                                inputEl.classList.add('is-invalid');
                                let containerEl = inputEl.parentElement;
                                while (containerEl && !containerEl.classList.contains('mb-3') && !containerEl.classList.contains('col-md-6')) {
                                    containerEl = containerEl.parentElement;
                                }
                                let feedback = null;
                                if (containerEl) {
                                    feedback = containerEl.querySelector('.invalid-feedback');
                                }
                                if (!feedback) {
                                    feedback = inputEl.parentElement.querySelector('.invalid-feedback');
                                }
                                if (feedback) {
                                    feedback.textContent = data.errors[fieldKey][0];
                                }
                            }
                        });
                    }
                } else if (response.ok) {
                    const modalEl = document.getElementById('createBuyOfferModal');
                    const modal = bootstrap.Modal.getInstance(modalEl);
                    if (modal) modal.hide();
                    location.href = '/profile#my-offers';
                } else {
                    alert('Došlo je do greške prilikom kreiranja ponude.');
                }
            }).catch(() => {
                alert('Greška pri slanju forme.');
            });
        });
    });
</script>