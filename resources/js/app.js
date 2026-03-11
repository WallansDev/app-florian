import './bootstrap';

// Auto-select the correct option in edit modals based on current value
document.addEventListener('alpine:init', () => {
    Alpine.magic('selectValue', () => (el, value) => {
        const options = el.querySelectorAll('option');
        options.forEach(opt => {
            opt.selected = opt.value === String(value);
        });
    });
});
