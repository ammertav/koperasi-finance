$(document).ready(function () {
    // ========== Modal Open/Close ==========
    $('#reverseBtn').click(() => $('#reverseModal').removeClass('hidden'));
    $('#closeReverseModal').click(() => $('#reverseModal').addClass('hidden'));

    $(window).click((e) => {
        if (e.target === $('#reverseModal')[0]) $('#reverseModal').addClass('hidden');
    });

    // ========== Reverse Confirm ==========
    $('#reverseSubmit').click(function () {
        const form = $('#reverseForm')[0];

        if (!form.reportValidity()) return;

        Swal.fire({
            title: 'Balik jurnal?',
            text: 'Jurnal pembalik tidak dapat dibatalkan.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, balik!',
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
});
