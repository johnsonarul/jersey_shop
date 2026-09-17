// assets/js/script.js
document.addEventListener("DOMContentLoaded", function() {
    // Quantity increment and decrement logic
    const minusBtns = document.querySelectorAll('.qty-minus');
    const plusBtns = document.querySelectorAll('.qty-plus');

    minusBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const input = this.nextElementSibling;
            let val = parseInt(input.value);
            if (val > 1) {
                input.value = val - 1;
            }
        });
    });

    plusBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const input = this.previousElementSibling;
            const max = parseInt(input.getAttribute('max')) || 10;
            let val = parseInt(input.value);
            if (val < max) {
                input.value = val + 1;
            }
        });
    });
});
