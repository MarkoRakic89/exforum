{{--
  Partial view for creating a new sell offer inside a modal.  This form
  is similar to the generic create form but removes the type selector and
  repeat fields, pre‑sets the offer type to `sell`, and labels the amount
  field as “Budžet”.  Sellers must select at least one industry in
  addition to one or more cities.
--}}

<form method="POST" action="{{ route('offers.store') }}" class="realtime-validation">
    @csrf
    <!-- Hidden inputs to set the type and repeat behaviour -->
    <input type="hidden" name="type" value="sell">
    <input type="hidden" name="repeat_type" value="once">
    <div class="row mb-3">
        <div class="col-12">Admin je ograničio Budžet na {{ (int)$maxAmount; }} i procenat na {{ (int)$maxPercent; }} . Kontaktirajte nas za druge uslove</div>
        <div class="col-md-6">
            <label class="form-label">Budžet (€)</label>
            <select name="amount_eur" class="form-select" required>
                @for($i = 500; $i <= (int)$maxAmount; $i +=500)
                    <option value="{{ $i }}">{{ $i }}</option>
                    @endfor
            </select>
            <div class="invalid-feedback"></div>
        </div>
        <div class="col-md-6">
            <label class="form-label">Procenat (%)</label>
            <select name="percent" class="form-select" required>
                @for($p = 1; $p <= (int)$maxPercent; $p++)
                    <option value="{{ $p }}">{{ $p }}</option>
                    @endfor
            </select>
            <div class="invalid-feedback"></div>
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">Gradovi u kojima se nalaze firme koje mogu tražiti vaše usluge (favorizuje ali ne isključuje ostale)
            </label>

            <div class="card border position-relative">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <input type="text" class="form-control mb-2" placeholder="Pretraži gradove..."
                        oninput="filterSellOptions(this, 'modal-city-options')">
                </div>
                <!--
                    The list of cities should be hidden by default.  We set display: none
                    here so that the container only becomes visible once the user has
                    begun typing a search term or has selected one or more cities.  The
                    filterOptions() function will toggle this style based on search
                    matches and current selections.
                -->
                <div class="card-body" style="display:none; max-height: 25vh; overflow-y: auto;position:absolute;top:120px;width:100%;background:white;border-radius:5px" id="modal-city-options">
                    @foreach($cities as $city)
                    <div class="form-check mb-1">
                        <input class="form-check-input" type="checkbox" name="cities[]" id="modal_city_{{ $city->id }}"
                            value="{{ $city->id }}">
                        <label class="form-check-label" for="modal_city_{{ $city->id }}">
                            {{ $city->name }}
                        </label>
                    </div>
                    @endforeach
                    <div class="invalid-feedback"></div>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">Delatnosti agencija koje vam odgovaraju (favorizuje ali ne isključuje ostale)
            </label>

            <div class="card border position-relative">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <input type="text" class="form-control mb-2" placeholder="Pretraži delatnosti..."
                        oninput="filterSellOptions(this, 'modal-industry-options')">
                </div>
                <!--
                    The list of industries is also hidden by default.  It will be
                    displayed only when the user enters a search term or when some
                    industries have already been selected.  See filterOptions() below.
                -->
                <div class="card-body" style="display:none; max-height: 25vh; overflow-y: auto;position:absolute;top:120px;width:100%;background:white;border-radius:5px" id="modal-industry-options">
                    @foreach($industries as $industry)
                    <div class="form-check mb-1">
                        <input class="form-check-input" type="checkbox" name="industries[]"
                            id="modal_industry_{{ $industry->id }}" value="{{ $industry->id }}">
                        <label class="form-check-label" for="modal_industry_{{ $industry->id }}">
                            {{-- Display the code and name to allow searching by either --}}
                            @if(!empty($industry->code))
                            {{ $industry->code }} - {{ $industry->name }}
                            @else
                            {{ $industry->name }}
                            @endif
                        </label>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    <div class="d-flex justify-content-end">
        <button type="submit" class="btn btn-primary">Kreiraj ponudu</button>
    </div>
</form>

<script>
    /**
     * Functions specific to the sell offer modal are prefixed with `sell` to avoid
     * collisions with other global functions on the page.  These helpers
     * normalize strings, filter checkbox lists and toggle the visibility of
     * checkbox containers based on search results or selections.
     */

    // Normalize a string for search by lowercasing and stripping diacritics.
    function normalizeSell(str) {
        return str
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '');
    }

    /**
     * Filter a list of checkbox items based on the search input.  When the
     * filter is empty, hide all unchecked items so that the list only
     * appears when the user begins typing.  Always show checked items so
     * users can see their selections even without a search term.
     *
     * @param {HTMLInputElement} input
     * @param {string} containerId
     */
    function filterSellOptions(input, containerId) {
        console.log(input.value)
        const filter = normalizeSell(input.value);
        const container = document.getElementById(containerId);
        if (!container) return;
        const items = container.querySelectorAll('.form-check');
        items.forEach(item => {
            const label = item.querySelector('label');
            const checkbox = item.querySelector('input[type="checkbox"]');
            if (!label || !checkbox) return;
            const text = normalizeSell(label.textContent);
            if (!filter) {
                // With no search term, only show checked items
                item.style.display = checkbox.checked ? '' : 'none';
            } else {
                // With a search term, show items that include the search text
                item.style.display = text.includes(filter) ? '' : 'none';
            }
        });
        updateSellContainerVisibility(containerId);
    }

    /**
     * Show or hide the checkbox list container based on whether there are any
     * visible items or selected items.  When there are none, hide the
     * container entirely so it only appears once the user types something
     * or selects at least one item.
     *
     * @param {string} containerId
     */
    function updateSellContainerVisibility(containerId) {
        const container = document.getElementById(containerId);
        if (!container) return;
        const items = container.querySelectorAll('.form-check');
        let hasVisible = false;
        items.forEach(item => {
            if (item.style.display !== 'none') {
                hasVisible = true;
            }
        });
        const hasChecked = !!container.querySelector('input[type="checkbox"]:checked');
        container.style.display = (hasVisible || hasChecked) ? '' : 'none';
    }

    // Move selected checkboxes to the top of their list for better visibility
    document.addEventListener('change', function(e) {
        const input = e.target;
        if (!input.classList.contains('form-check-input')) return;
        const parent = input.closest('.form-check');
        if (!parent || !input.checked) return;
        const container = parent.parentElement;
        if (!container) return;
        if (container.id === 'modal-city-options' || container.id === 'modal-industry-options') {
            container.insertBefore(parent, container.firstChild);
        }
    });

    // AJAX submit with real-time validation (same as generic create form)
    document.addEventListener('DOMContentLoaded', function() {
        // Initially hide the city and industry lists until there is a search term or selected items
        const cityInput = document.querySelector('#modal-city-options')?.parentElement.querySelector('input');
        if (cityInput) {
            // Initialize the visibility of the city list: hide all unchecked items and hide container when empty
            filterSellOptions(cityInput, 'modal-city-options');
        } else {
            updateSellContainerVisibility('modal-city-options');
        }
        const industryInput = document.querySelector('#modal-industry-options')?.parentElement.querySelector('input');
        if (industryInput) {
            // Initialize the visibility of the industry list: hide all unchecked items and hide container when empty
            filterSellOptions(industryInput, 'modal-industry-options');
        } else {
            updateSellContainerVisibility('modal-industry-options');
        }
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
                                    feedback = inputEl.closest('[class*="form"], div')?.querySelector('.invalid-feedback');
                                }
                                if (feedback) {
                                    feedback.textContent = data.errors[fieldKey][0];
                                }
                            }
                        });
                    }
                } else if (response.ok) {
                    const modalEl = document.getElementById('createSellOfferModal');
                    const modal = bootstrap.Modal.getInstance(modalEl);
                    if (modal) modal.hide();
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