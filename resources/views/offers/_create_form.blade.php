{{--
Partial view for creating a new offer inside a modal. This form reuses
the validation rules and fields from the dedicated offers.create page but
switches from multiselects to searchable checkbox lists for cities and
industries. The containing modal is responsible for providing
appropriate bootstrap dialog markup and toggling visibility.
--}}

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
            <select name="amount_eur" class="form-select" required>
                @for($i = 500; $i <= 10000; $i +=500) <option value="{{ $i }}">{{ $i }}</option>
                    @endfor
            </select>
            <div class="invalid-feedback"></div>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-md-6">
            <label class="form-label">Procenat (%)</label>
            <select name="percent" class="form-select" required>
                @for($p = 1; $p <= 10; $p++) <option value="{{ $p }}">{{ $p }}</option>
                    @endfor
            </select>
            <div class="invalid-feedback"></div>
        </div>
        <div class="col-md-6">
            <label class="form-label">Ponavljanje</label>
            <select name="repeat_type" class="form-select" id="modal_repeat_type" required>
                <option value="once">Jednom</option>
                <option value="monthly">Svaki mesec</option>
            </select>
            <div class="invalid-feedback"></div>
        </div>
    </div>
    <div class="mb-3" id="modal_repeat_until_container" style="display: none;">
        <label class="form-label">Ponavljaj do (ako mesečno)</label>
        <input type="date" name="repeat_until" class="form-control">
        <div class="invalid-feedback"></div>
    </div>
    <div class="mb-3">
        <label class="form-label">Gradovi</label>
        <input type="text" class="form-control mb-2" placeholder="Pretraži gradove..."
            oninput="filterOptions(this, 'modal-city-options')">
        <div class="card border">
            <div class="card-body" style="max-height: 250px; overflow-y: auto;" id="modal-city-options">
                @foreach($cities as $city)
                <div class="form-check mb-1">
                    <input class="form-check-input" type="checkbox" name="cities[]" id="modal_city_{{ $city->id }}"
                        value="{{ $city->id }}">
                    <label class="form-check-label" for="modal_city_{{ $city->id }}">
                        {{ $city->name }}
                    </label>
                </div>
                @endforeach
            </div>
        </div>
        <div class="invalid-feedback"></div>
    </div>
    <div class="mb-3">
        <label class="form-label">Delatnosti</label>
        <input type="text" class="form-control mb-2" placeholder="Pretraži delatnosti..."
            oninput="filterOptions(this, 'modal-industry-options')">
        <div class="card border">
            <div class="card-body" style="max-height: 250px; overflow-y: auto;" id="modal-industry-options">
                @foreach($industries as $industry)
                <div class="form-check mb-1">
                    <input class="form-check-input" type="checkbox" name="industries[]"
                        id="modal_industry_{{ $industry->id }}" value="{{ $industry->id }}">
                    <label class="form-check-label" for="modal_industry_{{ $industry->id }}">
                        {{ $industry->name }}
                    </label>
                </div>
                @endforeach
            </div>
        </div>
        <div class="invalid-feedback"></div>
    </div>
    <div class="d-flex justify-content-end">
        <button type="submit" class="btn btn-primary">Kreiraj ponudu</button>
    </div>
</form>

<script>
    // Toggle the repeat-until field when the repeat type changes
    document.addEventListener('DOMContentLoaded', function() {
        const selectRepeat = document.getElementById('modal_repeat_type');
        const repeatContainer = document.getElementById('modal_repeat_until_container');
        if (selectRepeat) {
            selectRepeat.addEventListener('change', function() {
                repeatContainer.style.display = this.value === 'monthly' ? 'block' : 'none';
            });
        }
    });

    /**
     * Filter the list of checkboxes by the search input.  This helper hides
     * items whose label does not include the filter string (case‑insensitive).
     * @param {HTMLInputElement} input
     * @param {string} containerId
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

    // When a city or industry checkbox is toggled, move selected items to the top of their list.
    document.addEventListener('change', function(e) {
        const input = e.target;
        if (!input.classList.contains('form-check-input')) return;
        const parent = input.closest('.form-check');
        if (!parent) return;
        const container = parent.parentElement;
        if (!container || !input.checked) return;
        if (container.id === 'modal-city-options' || container.id === 'modal-industry-options') {
            container.insertBefore(parent, container.firstChild);
        }
    });

    // AJAX submit with real‑time validation.  Prevents full page reload and
    // retains form values on validation errors.  On success the modal is
    // closed and a toast (or alert) is shown.
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form.realtime-validation');
        if (!form) return;
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            // Clear previous validation states
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
                            // Normalize array fields (e.g. cities.0 -> cities)
                            const baseField = fieldKey.replace(/\.\d+$/, '');
                            let input = form.querySelector(`[name="${baseField}"]`) || form.querySelector(`[name="${baseField}[]"]`);
                            if (input) {
                                input.classList.add('is-invalid');
                                // Find feedback element within the closest form-group (mb-3 or col-md-6)
                                let containerEl = input.parentElement;
                                while (containerEl && !containerEl.classList.contains('mb-3') && !containerEl.classList.contains('col-md-6')) {
                                    containerEl = containerEl.parentElement;
                                }
                                let feedback = null;
                                if (containerEl) {
                                    feedback = containerEl.querySelector('.invalid-feedback');
                                }
                                if (!feedback) {
                                    // fallback to next sibling invalid-feedback
                                    feedback = input.parentElement.querySelector('.invalid-feedback');
                                }
                                if (feedback) {
                                    feedback.textContent = data.errors[fieldKey][0];
                                }
                            }
                        });
                    }
                } else if (response.ok) {
                    // Success: close modal and optionally reload list via page reload or fetch
                    // We keep the form values so they are not cleared by default.
                    const modalEl = document.getElementById('createOfferModal');
                    const modal = bootstrap.Modal.getInstance(modalEl);
                    if (modal) modal.hide();
                    // Display a simple alert.  In a real app this could use toast notifications.
                    location.reload();
                } else {
                    alert('Došlo je do greške prilikom kreiranja ponude.');
                }
            }).catch(() => {
                alert('Greška pri slanju forme.');
            });
        });
    });
</script>