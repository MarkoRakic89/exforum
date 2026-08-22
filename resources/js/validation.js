// Realtime validation logic for forms with the class `realtime-validation`.
// Adds client‑side checks for required fields, numeric ranges and email format.

document.addEventListener('DOMContentLoaded', () => {
    const forms = document.querySelectorAll('form.realtime-validation');
    forms.forEach(form => {
        const fields = form.querySelectorAll('input, select, textarea');
        fields.forEach(field => {
            field.addEventListener('input', () => validateField(field));
            field.addEventListener('change', () => validateField(field));
        });
        form.addEventListener('submit', event => {
            let valid = true;
            fields.forEach(f => {
                const ok = validateField(f);
                if (!ok) valid = false;
            });
            if (!valid) {
                event.preventDefault();
                event.stopPropagation();
            }
        });
    });
});

function validateField(field) {
    const feedback = field.parentElement.querySelector('.invalid-feedback');
    let message = '';
    // Required fields
    if (field.hasAttribute('required') && !field.value) {
        message = 'Ovo polje je obavezno.';
    }
    // Numeric min
    else if (field.dataset.min && field.value) {
        const min = parseFloat(field.dataset.min);
        const val = parseFloat(field.value);
        if (!isNaN(val) && val < min) {
            message = 'Minimalna vrednost je ' + min + '.';
        }
    }
    // Numeric max
    if (!message && field.dataset.max && field.value) {
        const max = parseFloat(field.dataset.max);
        const val = parseFloat(field.value);
        if (!isNaN(val) && val > max) {
            message = 'Maksimalna vrednost je ' + max + '.';
        }
    }
    // Email format
    if (!message && field.type === 'email' && field.value) {
        const pattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!pattern.test(field.value)) {
            message = 'Unesite validnu email adresu.';
        }
    }
    // Display message
    if (message) {
        field.classList.add('is-invalid');
        if (feedback) feedback.textContent = message;
        return false;
    } else {
        field.classList.remove('is-invalid');
        if (feedback) feedback.textContent = '';
        return true;
    }
}