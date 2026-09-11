$(document).ready(function () {
    const escapeHtml = (value) => $('<div>').text(value ?? '-').html();

    // ========== Currency ==========
    const formatCurrency = (input) => {
        const digits = $(input).val().replace(/\D/g, '');
        $(input).val(digits ? Number(digits).toLocaleString('id-ID') : '');
    };

    $('.currency').each(function () {
        formatCurrency(this);
    });

    $(document).on('input', '.currency', function () {
        formatCurrency(this);
    });

    $('#memberForm').on('submit', function () {
        $(this).find('.currency').each(function () {
            $(this).val($(this).val().replace(/\./g, ''));
        });
    });

    // ========== NIK Check (AGT-02) ==========
    const nikResult = $('#nikCheckResult');

    const showNikResult = (html, classes) => {
        nikResult
            .removeClass('hidden bg-green-50 border-green-200 text-green-700 bg-red-50 border-red-200 text-red-700')
            .addClass(classes)
            .html(html);
    };

    $('#nik').on('input', function () {
        $(this).val($(this).val().replace(/\D/g, '').slice(0, 16));
        nikResult.addClass('hidden');
        $('#submitBtn').prop('disabled', false);
    });

    $('#nik').on('blur', function () {
        const nik = $(this).val();

        if (nik.length !== 16) {
            return;
        }

        $.ajax({
            url: '/member/check-nik',
            method: 'POST',
            dataType: 'json',
            data: { nik, _token: $('input[name="_token"]').val() },
        })
            .done(function (result) {
                $('#submitBtn').prop('disabled', !result.allowed);

                if (result.allowed) {
                    showNikResult(
                        '<i class="fas fa-check-circle"></i> NIK belum terdaftar di seluruh kantor koperasi.',
                        'bg-green-50 border-green-200 text-green-700'
                    );
                    return;
                }

                const match = result.match;
                const loanText = match.active_loan_count > 0
                    ? `${match.active_loan_count} pinjaman berjalan (${escapeHtml(match.collectibility_label)})`
                    : 'Tidak ada';

                showNikResult(
                    `<i class="fas fa-ban"></i> NIK sudah terdaftar di <b>${escapeHtml(match.office)}</b>. Pendaftaran ditahan.`,
                    'bg-red-50 border-red-200 text-red-700'
                );

                Swal.fire({
                    icon: 'error',
                    title: 'NIK sudah terdaftar',
                    html: `
                        <p class="mb-4">Calon anggota tidak dapat didaftarkan ulang. Data anggota bersifat tunggal di seluruh kantor.</p>
                        <table class="w-full text-left text-sm">
                            <tr><td class="py-1 text-gray-500">Nama</td><td class="py-1 font-bold">${escapeHtml(match.name)}</td></tr>
                            <tr><td class="py-1 text-gray-500">NIK</td><td class="py-1 font-mono">${escapeHtml(match.nik_masked)}</td></tr>
                            <tr><td class="py-1 text-gray-500">Kantor Asal</td><td class="py-1 font-bold">${escapeHtml(match.office)}</td></tr>
                            <tr><td class="py-1 text-gray-500">Status</td><td class="py-1">${escapeHtml(match.status_label)}</td></tr>
                            <tr><td class="py-1 text-gray-500">Pinjaman</td><td class="py-1">${loanText}</td></tr>
                        </table>`,
                    confirmButtonText: 'Mengerti',
                });
            })
            .fail(function () {
                Swal.fire({ icon: 'error', title: 'NIK tidak valid', text: 'NIK harus terdiri dari 16 digit angka.' });
            });
    });

    // ========== KTP Preview ==========
    $('#ktpPhoto').on('change', function () {
        const file = this.files[0];

        if (!file) {
            return;
        }

        $('#ktpPreview').attr('src', URL.createObjectURL(file)).removeClass('hidden');
        $('#ktpPlaceholder').addClass('hidden');
    });
});
