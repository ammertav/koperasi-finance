<!DOCTYPE html>
<html lang="id">

<head>
    <title>Cetak Mutasi {{ $account['number'] }}</title>
    @include('layout.head')
</head>

<body class="bg-gray-50 font-sans print:bg-white">
    @php
        $rupiah = fn (int $amount) => 'Rp ' . number_format($amount, 0, ',', '.');
        $bookDate = \Illuminate\Support\Carbon::parse($account['office_book_date']);
    @endphp

    <div class="max-w-4xl mx-auto p-6 print:p-0">
        <!-- Toolbar -->
        <div class="flex justify-end gap-2 mb-4 print:hidden">
            <x-button :href="route('detailSavingsAccount', $account['number'])" variant="secondary" icon="arrow-left">Kembali</x-button>
            <x-button type="button" onclick="window.print()" variant="primary" class="bg-emerald-600 hover:bg-emerald-700" icon="print">Cetak</x-button>
        </div>

        <div class="bg-white rounded-xl shadow-md border border-gray-100 p-8 print:shadow-none print:border-0">
            <!-- Letterhead -->
            <div class="flex justify-between items-start border-b-2 border-gray-800 pb-4">
                <div class="flex items-center gap-4">
                    <img src="{{ asset('logo.svg') }}" alt="Logo" class="w-20 h-auto">
                    <div>
                        <h1 class="text-xl font-bold text-gray-900">Koperasi Simpan Pinjam</h1>
                        <p class="text-sm text-gray-600">{{ $account['office_name'] }} ({{ $account['office_code'] }})</p>
                    </div>
                </div>
                <div class="text-right">
                    <h2 class="text-lg font-bold text-gray-900 uppercase tracking-wide">Rekening Koran Simpanan</h2>
                    <p class="text-sm text-gray-600">Dicetak {{ $bookDate->format('d M Y') }}</p>
                </div>
            </div>

            <!-- Account Info -->
            <div class="grid grid-cols-2 gap-6 py-5 text-sm">
                <dl class="space-y-1">
                    <div class="flex gap-2"><dt class="w-32 text-gray-500">Nama Anggota</dt><dd class="font-semibold">{{ $member['name'] }}</dd></div>
                    <div class="flex gap-2"><dt class="w-32 text-gray-500">No. Anggota</dt><dd class="font-mono">{{ $member['number'] }}</dd></div>
                    <div class="flex gap-2"><dt class="w-32 text-gray-500">Alamat</dt><dd>{{ $member['address'] }}</dd></div>
                </dl>
                <dl class="space-y-1">
                    <div class="flex gap-2"><dt class="w-32 text-gray-500">No. Rekening</dt><dd class="font-mono font-semibold">{{ $account['number'] }}</dd></div>
                    <div class="flex gap-2"><dt class="w-32 text-gray-500">Produk</dt><dd>{{ $account['product_name'] }}</dd></div>
                    <div class="flex gap-2"><dt class="w-32 text-gray-500">Periode</dt><dd>{{ $summary['period_start']->format('d M Y') }} s.d. {{ $bookDate->format('d M Y') }}</dd></div>
                </dl>
            </div>

            <!-- Table -->
            <table class="w-full text-left text-sm border-collapse">
                <thead>
                    <tr class="border-y border-gray-800">
                        <th class="py-2 pr-2 font-bold">Tanggal</th>
                        <th class="py-2 px-2 font-bold">Referensi</th>
                        <th class="py-2 px-2 font-bold">Keterangan</th>
                        <th class="py-2 px-2 font-bold text-right">Penarikan</th>
                        <th class="py-2 px-2 font-bold text-right">Setoran</th>
                        <th class="py-2 pl-2 font-bold text-right">Saldo</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($mutations as $row)
                        <tr class="border-b border-gray-200">
                            <td class="py-2 pr-2 whitespace-nowrap">{{ $row['date']->format('d/m/Y') }}</td>
                            <td class="py-2 px-2 font-mono text-xs">{{ $row['number'] ?? '-' }}</td>
                            <td class="py-2 px-2">{{ $row['description'] }}</td>
                            <td class="py-2 px-2 text-right whitespace-nowrap">{{ $row['debit'] ? number_format($row['debit'], 0, ',', '.') : '' }}</td>
                            <td class="py-2 px-2 text-right whitespace-nowrap">{{ $row['credit'] ? number_format($row['credit'], 0, ',', '.') : '' }}</td>
                            <td class="py-2 pl-2 text-right whitespace-nowrap font-semibold">{{ number_format($row['balance'], 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="border-t-2 border-gray-800 font-bold">
                        <td class="py-2" colspan="3">Saldo akhir</td>
                        <td class="py-2 px-2 text-right">{{ number_format($summary['total_debit'], 0, ',', '.') }}</td>
                        <td class="py-2 px-2 text-right">{{ number_format($summary['total_credit'], 0, ',', '.') }}</td>
                        <td class="py-2 pl-2 text-right">{{ $rupiah($account['balance']) }}</td>
                    </tr>
                </tfoot>
            </table>

            <p class="text-xs text-gray-400 mt-6">Dokumen ini dicetak dari sistem dan sah tanpa tanda tangan.</p>
        </div>
    </div>
</body>

</html>
