{{--
  Partial view for creating a new buy offer inside a modal.  This form
  is similar to the generic create form but removes the type selector
  and industries section, pre‑sets the offer type to `buy`, and labels
  the amount field as “Budžet”.  Buyers do not need to select
  industries.  A repeat option is provided so that buyers can choose
  whether the offer recurs monthly or is a one‑off.  Cities are
  selected via a searchable list, with chosen cities floated to the
  top of the list.
--}}

<form method="POST" action="{{ route('offers.store') }}" class="realtime-buy-validation">
    @csrf
    <!-- Hidden input to set the type -->
    <input type="hidden" name="type" value="buy">
    <div class="row mb-2">
        <div class="col-12">Admin je ograničio Budžet na {{ (int)$maxAmount; }} i procenat na {{ (int)$maxPercent; }}% . Kontaktirajte nas za druge uslove</div>
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
    </div>
    <div class="row mb-2">
        <div class="col-md-6">
            <label class="form-label">Ponavljanje</label>
            <select name="repeat_type" class="form-select" id="modal_buy_repeat_type" required>
                <option value="once">Jednom</option>
                <option value="monthly">Svaki mesec</option>
            </select>
            <div class="invalid-feedback"></div>
        </div>
        <div class="col-md-6">
            <div class="form-group" id="modal_buy_repeat_until_container" style="display: none;">
                <label class="form-label">Ponavljaj do (ako mesečno)</label>
                <input type="date" name="repeat_until" class="form-control">
                <div class="invalid-feedback"></div>
            </div>
        </div>

        {{--
        City and industry selections are not displayed for buy offers.  Buyers do not
        need to select cities or industries; they simply specify budget, percent
        and repetition.  The following sections are intentionally removed from
        the buy offer form to simplify the interface.
        --}}
    </div>
    <div class="d-flex justify-content-end">
        <button type="submit" class="btn btn-primary">Kreiraj ponudu</button>
    </div>
</form>

<script>
    /**
     * Normalize a string for search by lowercasing and stripping diacritics.
     */
    function normalize(str) {
        return str
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '');
    }

    /**
     * Filter a list of checkbox items based on the search input.  When the filter
     * is empty, hide all unchecked items so that the list only appears when
     * the user begins typing.  Always show checked items so users can see
     * their selections even without a search term.
     *
     * @param {HTMLInputElement} input
     * @param {string} containerId
     */
    function filterOptions(input, containerId) {
        const filter = normalize(input.value);
        const container = document.getElementById(containerId);
        if (!container) return;
        const items = container.querySelectorAll('.form-check');
        items.forEach(item => {
            const label = item.querySelector('label');
            const checkbox = item.querySelector('input[type="checkbox"]');
            if (!label || !checkbox) return;
            const text = normalize(label.textContent);
            if (!filter) {
                item.style.display = checkbox.checked ? '' : 'none';
            } else {
                item.style.display = text.includes(filter) ? '' : 'none';
            }
        });
    }

    // Show/hide repeat-until based on selection and handle moving selected items to top
    document.addEventListener('DOMContentLoaded', function() {
        const repeatSelect = document.getElementById('modal_buy_repeat_type');
        const untilContainer = document.getElementById('modal_buy_repeat_until_container');
        if (repeatSelect) {
            repeatSelect.addEventListener('change', function() {
                untilContainer.style.display = this.value === 'monthly' ? 'block' : 'none';
            });
        }
        // There are no city or industry selections for buy offers.  Nothing to hide/show here.
    });

    // Move selected city and industry checkboxes to the top of their list
    document.addEventListener('change', function(e) {
        const input = e.target;
        if (!input.classList.contains('form-check-input')) return;
        const parent = input.closest('.form-check');
        if (!parent || !input.checked) return;
        const container = parent.parentElement;
        if (!container) return;
        // Only apply reordering in lists that exist; buy offers do not have city/industry lists
        if (container.id === 'modal-buy-city-options' || container.id === 'modal-buy-industry-options') {
            container.insertBefore(parent, container.firstChild);
        }
    });

    // AJAX submit with real-time validation (same as generic create form)
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form.realtime-buy-validation');
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
                                    feedback = inputEl.parentElement.querySelector('.invalid-feedback');
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