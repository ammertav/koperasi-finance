<script>
document.addEventListener('DOMContentLoaded', function () {

    const pageLoading = document.getElementById('page-loading');
    const forms = document.querySelectorAll('form');

    forms.forEach(form => {
        form.addEventListener('submit', function () {

            /* ===============================
               CASE 1: SEARCH / PAGE LOADING
            =============================== */
            if (form.hasAttribute('data-page-loading')) {
                if (pageLoading) {
                    pageLoading.classList.remove('hidden');
                }
                return;
            }

            /* ===============================
               CASE 2: NORMAL FORM (CRUD)
            =============================== */
            const btn = form.querySelector('button[type="submit"]');

            if (!btn || btn.disabled) return;

            const originalWidth = btn.offsetWidth;
            btn.style.width = `${originalWidth}px`;

            btn.disabled = true;
            btn.classList.add('opacity-75', 'cursor-not-allowed');

            btn.innerHTML = `
                <div class="flex items-center justify-center gap-2">
                    <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10"
                            stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    <span>Memproses...</span>
                </div>
            `;
        });
    });

});
</script>
