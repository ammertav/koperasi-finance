$(document).ready(function () {
    // ========== Activate Confirm ==========
    $(document).on('click', '.activate-confirm', function () {
        const form = $(this).closest('form');

        Swal.fire({
            title: 'Terima setoran simpanan pokok?',
            text: `Setoran atas nama ${form.data('name')} diterima tunai. Nomor anggota terbit dan rekening simpanan pokok serta wajib dibuka.`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, terima setoran!',
        }).then((result) => {
            if (result.isConfirmed) {
                form[0].submit();
            }
        });
    });
});
