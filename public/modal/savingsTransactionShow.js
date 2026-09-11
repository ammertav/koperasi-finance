$(document).ready(function () {
    // ========== Approve Confirm ==========
    $('#approveBtn').click(function () {
        Swal.fire({
            title: 'Otorisasi penarikan?',
            text: 'Transaksi langsung diposting, kas dibayarkan teller, dan jurnal terbentuk otomatis.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, otorisasi!',
        }).then((result) => {
            if (result.isConfirmed) {
                $('#approveForm')[0].submit();
            }
        });
    });

    // ========== Modal Open/Close ==========
    $('#rejectBtn').click(() => $('#rejectModal').removeClass('hidden'));
    $('#closeRejectModal').click(() => $('#rejectModal').addClass('hidden'));

    $(window).click((e) => {
        if (e.target === $('#rejectModal')[0]) $('#rejectModal').addClass('hidden');
    });
});
