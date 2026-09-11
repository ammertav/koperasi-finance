<!DOCTYPE html>
<html lang="id">

<head>
    <title>Cetak Bukti {{ $transaction['number'] }}</title>
    @include('layout.head')
</head>

<body class="bg-gray-50 font-sans print:bg-white">
    @php
        $rupiah = fn (int $amount) => 'Rp ' . number_format($amount, 0, ',', '.');
    @endphp

    <div class="max-w-md mx-auto p-6 print:p-0">
        <!-- Toolbar -->
        <div class="flex justify-end gap-2 mb-4 print:hidden">
            <x-button :href="route('detailSavingsTransaction', $transaction['id'])" variant="secondary" icon="arrow-left">Kembali</x-button>
            <x-button type="button" onclick="window.print()" variant="primary" class="bg-emerald-600 hover:bg-emerald-700" icon="print">Cetak</x-button>
        </div>

        <div class="bg-white rounded-xl shadow-md border border-gray-100 p-6 print:shadow-none print:border-0 text-sm">
            <!-- Letterhead -->
            <div class="text-center border-b-2 border-dashed border-gray-400 pb-4">
                <img src="{{ asset('logo.svg') }}" alt="Logo" class="w-16 h-auto mx-auto">
                <h1 class="font-bold text-gray-900 mt-2">Koperasi Simpan Pinjam</h1>
                <p class="text-gray-600">{{ $transaction['office_name'] }} ({{ $transaction['office_code'] }})</p>
                <h2 class="font-bold uppercase tracking-wide mt-3">Bukti {{ $transaction['type_label'] }} Simpanan</h2>
                <p class="font-mono text-xs text-gray-500">{{ $transaction['number'] }}</p>
            </div>

            <dl class="py-4 space-y-2 border-b-2 border-dashed border-gray-400">
                <div class="flex justify-between gap-4"><dt class="text-gray-500">Tanggal</dt><dd>{{ $transaction['posted_at']->format('d/m/Y H:i') }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-gray-500">Nama</dt><dd class="font-semibold text-right">{{ $transaction['member_name'] }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-gray-500">No. Anggota</dt><dd class="font-mono">{{ $transaction['member_number'] }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-gray-500">No. Rekening</dt><dd class="font-mono">{{ $transaction['account_number'] }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-gray-500">Produk</dt><dd>{{ $transaction['product_name'] }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-gray-500">Keterangan</dt><dd class="text-right">{{ $transaction['description'] }}</dd></div>
            </dl>

            <div class="py-4 border-b-2 border-dashed border-gray-400">
                <div class="flex justify-between items-center">
                    <span class="font-bold uppercase">Nominal</span>
                    <span class="text-2xl font-bold">{{ $rupiah($transaction['amount']) }}</span>
                </div>
                <div class="flex justify-between mt-2 text-gray-600"><span>Saldo akhir</span><span>{{ $rupiah($transaction['balance_after']) }}</span></div>
                <p class="text-xs text-gray-400 mt-1">Jurnal {{ $journal['number'] ?? '-' }}</p>
            </div>

            <div class="grid grid-cols-2 gap-6 pt-6 text-center">
                <div>
                    <p class="text-gray-500">Anggota</p>
                    <div class="h-16"></div>
                    <p class="border-t border-gray-400 pt-1 font-semibold">{{ $transaction['member_name'] }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Teller</p>
                    <div class="h-16"></div>
                    <p class="border-t border-gray-400 pt-1 font-semibold">{{ $transaction['teller_name'] }}</p>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
