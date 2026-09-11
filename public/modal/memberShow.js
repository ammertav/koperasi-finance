$(document).ready(function () {
    // ========== Tabs ==========
    $('.tab-btn').click(function () {
        const tab = $(this).data('tab');

        $('.tab-btn').removeClass('active');
        $(this).addClass('active');
        $('.tab-content').addClass('hidden');
        $(`[data-tab-content="${tab}"]`).removeClass('hidden');
    });

    // ========== Approve Confirm ==========
    $('#approveBtn').click(function () {
        Swal.fire({
            title: 'Setujui pendaftaran?',
            text: 'Calon anggota dapat menyetor simpanan pokok di teller setelah disetujui.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, setujui!',
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
