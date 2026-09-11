$(document).ready(function () {
    const rupiah = (value) => 'Rp ' + Number(value).toLocaleString('id-ID');

    // ========== Denomination Count ==========
    const recount = () => {
        let total = 0;

        $('.denomination').each(function () {
            const subtotal = (Number($(this).val()) || 0) * Number($(this).data('value'));
            total += subtotal;
            $(this).closest('tr').find('.subtotal').text(rupiah(subtotal));
        });

        $('#denominationTotal').text(rupiah(total));

        return total;
    };

    $(document).on('input', '.denomination', recount);
    recount();

    $('#openSessionForm').on('submit', function (event) {
        event.preventDefault();
        const form = this;

        Swal.fire({
            title: 'Buka sesi kas?',
            text: `Saldo awal ${rupiah(recount())} diambil dari brankas dan dicatat sebagai kas teller.`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, buka sesi!',
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
});
