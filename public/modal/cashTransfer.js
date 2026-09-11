$(document).ready(function () {
    // ========== Currency ==========
    $(document).on('input', '.currency', function () {
        const digits = $(this).val().replace(/\D/g, '');
        $(this).val(digits ? Number(digits).toLocaleString('id-ID') : '');
    });

    $('#transferForm').on('submit', function () {
        $(this).find('.currency').each(function () {
            $(this).val($(this).val().replace(/\./g, ''));
        });
    });

    // ========== Confirm Transfer ==========
    $(document).on('click', '.confirm-transfer', function () {
        const button = $(this);

        Swal.fire({
            title: 'Konfirmasi perpindahan kas?',
            text: `${button.data('direction')} sebesar ${button.data('amount')}. Pastikan uang fisik sudah dihitung bersama.`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, konfirmasi!',
        }).then((result) => {
            if (result.isConfirmed) {
                button.closest('form')[0].submit();
            }
        });
    });

    // ========== Modal Open/Close ==========
    $('#addBtn').click(() => $('#addModal').removeClass('hidden'));
    $('#closeAddModal').click(() => $('#addModal').addClass('hidden'));

    $(document).on('click', '.rejectBtn', function () {
        $('#rejectForm').attr('action', `/cash-transfer/${$(this).data('id')}/reject`);
        $('#rejectModal').removeClass('hidden');
    });
    $('#closeRejectModal').click(() => $('#rejectModal').addClass('hidden'));

    $(window).click((e) => {
        if (e.target === $('#addModal')[0]) $('#addModal').addClass('hidden');
        if (e.target === $('#rejectModal')[0]) $('#rejectModal').addClass('hidden');
    });
});
