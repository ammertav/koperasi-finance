$(document).ready(function () {
    const form = $('#transactionForm');
    const tellerBalance = Number(form.data('teller-balance'));
    const tellerLimit = Number(form.data('teller-limit'));
    const branchHeadLimit = Number(form.data('branch-head-limit'));
    const mandatoryMonthly = Number(form.data('mandatory'));
    const voluntaryMinimum = Number(form.data('voluntary-minimum'));
    const accountCodes = form.data('account-codes');
    const accountNames = { SP: 'Simpanan Pokok', SW: 'Simpanan Wajib', SS: 'Simpanan Sukarela' };
    let selected = null;

    const escapeHtml = (value) => $('<div>').text(value ?? '-').html();
    const rupiah = (value) => 'Rp ' + Number(value).toLocaleString('id-ID');
    const amountValue = () => Number($('#amount').val().replace(/\D/g, '')) || 0;

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
        refresh();
    });

    form.on('submit', function () {
        $(this).find('.currency').each(function () {
            $(this).val($(this).val().replace(/\./g, ''));
        });
    });

    // ========== Account Search ==========
    const search = () => {
        const keyword = $('#accountKeyword').val().trim();

        if (keyword.length < 3) {
            Swal.fire({ icon: 'info', title: 'Kata kunci terlalu pendek', text: 'Masukkan minimal 3 karakter.' });
            return;
        }

        $.ajax({
            url: '/savings-transaction/search-account',
            method: 'POST',
            dataType: 'json',
            data: { keyword, _token: $('input[name="_token"]').val() },
        }).done(function (result) {
            const box = $('#searchResult').removeClass('hidden');

            if (result.accounts.length === 0) {
                box.html('<p class="p-3 text-sm text-gray-500 text-center">Rekening tidak ditemukan di kantor ini</p>');
                return;
            }

            box.html(result.accounts.map((account, index) => `
                <button type="button" class="account-option w-full text-left p-3 hover:bg-emerald-50 flex justify-between gap-4" data-index="${index}">
                    <span>
                        <span class="block font-semibold text-gray-900">${escapeHtml(account.member_name)}</span>
                        <span class="block text-xs text-gray-500 font-mono">${escapeHtml(account.number)} · ${escapeHtml(account.product_name)}</span>
                    </span>
                    <span class="font-semibold text-gray-700 whitespace-nowrap">${rupiah(account.balance)}</span>
                </button>`).join(''));

            box.data('accounts', result.accounts);
        });
    };

    $('#searchBtn').on('click', search);
    $('#accountKeyword').on('keydown', function (event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            search();
        }
    });

    $(document).on('click', '.account-option', function () {
        selected = $('#searchResult').data('accounts')[$(this).data('index')];

        $('#accountNumber').val(selected.number);
        $('#selectedMember').text(`${selected.member_name} (${selected.member_number})`);
        $('#selectedNumber').text(`${selected.number} · ${selected.product_name}`);
        $('#selectedBalance').text(rupiah(selected.balance));
        $('#selectedAvailable').text(selected.product_code === 'SS' ? `Dapat ditarik ${rupiah(selected.available_balance)}` : 'Tidak dapat ditarik');
        $('#selectedAccount').removeClass('hidden');
        $('#searchResult').addClass('hidden');
        refresh();
    });

    $('input[name="type"]').on('change', refresh);

    // ========== Rules and Journal Preview ==========
    function notice(html, classes) {
        $('#ruleNotice')
            .removeClass('hidden bg-red-50 border-red-200 text-red-700 bg-yellow-50 border-yellow-200 text-yellow-800 bg-green-50 border-green-200 text-green-700')
            .addClass(classes)
            .html(html);
    }

    function refresh() {
        const type = $('input[name="type"]:checked').val();
        const amount = amountValue();
        let allowed = selected !== null && amount > 0;

        $('#ruleNotice').addClass('hidden');

        if (selected && amount > 0) {
            const product = selected.product_code;

            if (type === 'deposit' && product === 'SP') {
                allowed = false;
                notice('<i class="fas fa-ban"></i> Simpanan pokok hanya disetor sekali saat aktivasi anggota.', 'bg-red-50 border-red-200 text-red-700');
            } else if (type === 'deposit' && product === 'SW' && amount % mandatoryMonthly !== 0) {
                allowed = false;
                notice(`<i class="fas fa-ban"></i> Setoran simpanan wajib harus kelipatan ${rupiah(mandatoryMonthly)}.`, 'bg-red-50 border-red-200 text-red-700');
            } else if (type === 'deposit' && product === 'SS' && amount < voluntaryMinimum) {
                allowed = false;
                notice(`<i class="fas fa-ban"></i> Setoran simpanan sukarela minimal ${rupiah(voluntaryMinimum)}.`, 'bg-red-50 border-red-200 text-red-700');
            } else if (type === 'withdrawal' && product !== 'SS') {
                allowed = false;
                notice(`<i class="fas fa-ban"></i> ${accountNames[product]} tidak dapat ditarik selama keanggotaan aktif.`, 'bg-red-50 border-red-200 text-red-700');
            } else if (type === 'withdrawal' && amount > selected.available_balance) {
                allowed = false;
                notice(`<i class="fas fa-ban"></i> Saldo yang dapat ditarik hanya ${rupiah(selected.available_balance)}.`, 'bg-red-50 border-red-200 text-red-700');
            } else if (type === 'withdrawal' && amount > tellerLimit) {
                const approver = amount <= branchHeadLimit ? 'kepala cabang' : 'manajer pusat';
                notice(`<i class="fas fa-user-shield"></i> Penarikan di atas batas teller ${rupiah(tellerLimit)}. Transaksi akan menunggu otorisasi <b>${approver}</b> sebelum kas dibayarkan.`, 'bg-yellow-50 border-yellow-200 text-yellow-800');
            } else if (type === 'withdrawal' && amount > tellerBalance) {
                allowed = false;
                notice('<i class="fas fa-ban"></i> Kas teller tidak mencukupi. Ambil tambahan kas dari brankas terlebih dahulu.', 'bg-red-50 border-red-200 text-red-700');
            } else {
                notice('<i class="fas fa-check-circle"></i> Transaksi dapat diproses dan langsung diposting.', 'bg-green-50 border-green-200 text-green-700');
            }
        }

        $('#submitBtn').prop('disabled', !allowed);

        if (!selected || amount <= 0) {
            $('#journalPreview').html('<tr><td class="p-3 text-gray-400 text-center" colspan="3">Pilih rekening dan isi nominal</td></tr>');
            return;
        }

        const savingsLine = `<span class="font-mono text-xs text-gray-400">${accountCodes[selected.product_code]}</span> <span class="font-semibold">${accountNames[selected.product_code]}</span>`;
        const cashLine = '<span class="font-mono text-xs text-gray-400">1.1.01</span> <span class="font-semibold">Kas Teller</span>';
        const lines = type === 'deposit' ? [[cashLine, amount, 0], [savingsLine, 0, amount]] : [[savingsLine, amount, 0], [cashLine, 0, amount]];

        $('#journalPreview').html(lines.map(([account, debit, credit]) => `
            <tr class="border-b border-gray-100">
                <td class="p-3 ${credit > 0 ? 'pl-10' : ''}">${account}</td>
                <td class="p-3 text-right whitespace-nowrap">${debit ? rupiah(debit) : '-'}</td>
                <td class="p-3 text-right whitespace-nowrap">${credit ? rupiah(credit) : '-'}</td>
            </tr>`).join(''));
    }
});
