<!DOCTYPE html>
<html lang="id">

<head>
    <title>Detail Jurnal {{ $journal['number'] }}</title>
    @include('layout.head')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-gray-50 font-sans">
    @include('layout.sidebar')

    <main class="md:ml-64 xl:ml-72 2xl:ml-72">
        @include('layout.navbar')
        <div class="p-6 space-y-6">

            @php
                $rupiah = fn (int $amount) => 'Rp ' . number_format($amount, 0, ',', '.');
                $ledgerLink = fn (string $accountCode) => route('generalLedgerMockup', [
                    'account_code' => $accountCode,
                    'office_id' => $journal['office_id'],
                    'start_date' => $journal['book_date']->toDateString(),
                    'end_date' => $journal['book_date']->toDateString(),
                ]);
            @endphp

            <!-- Header -->
            <div
                class="md:flex justify-between items-center bg-white p-5 rounded-xl shadow-sm border border-gray-100 space-y-2 md:space-y-0">
                <div>
                    <h1 class="font-bold text-2xl text-gray-800 flex items-center gap-2">
                        <i class="fas fa-book text-indigo-600"></i> Jurnal <span class="font-mono">{{ $journal['number'] }}</span>
                    </h1>
                    <p class="text-sm text-gray-500 mt-1">{{ $journal['description'] }}</p>
                </div>
                <div class="flex gap-2">
                    <x-button :href="route('journalMockup')" variant="secondary" icon="arrow-left">Kembali</x-button>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-2 xl:grid-cols-4 gap-4">
                <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Tanggal Buku</p>
                    <h2 class="text-2xl font-bold text-gray-800 mt-1">{{ $journal['book_date']->format('d M Y') }}</h2>
                    <p class="text-xs text-gray-400 mt-2">Dicatat: {{ $journal['time']->format('d M Y, H:i') }}</p>
                </div>
                <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Kantor</p>
                    <h2 class="text-2xl font-bold text-gray-800 mt-1">{{ $journal['office_code'] }}</h2>
                    <p class="text-xs text-gray-400 mt-2">{{ $journal['office_name'] }}</p>
                </div>
                <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Transaksi</p>
                    <h2 class="text-2xl font-bold text-gray-800 mt-1">{{ $journal['transaction_type_label'] }}</h2>
                    <p class="text-xs text-gray-400 mt-2">Ref <span class="font-mono">{{ $journal['reference'] }}</span> · {{ $journal['created_by_name'] }}</p>
                </div>
                <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Total</p>
                    <h2 class="text-2xl font-bold text-gray-800 mt-1">{{ $rupiah($journal['total_amount']) }}</h2>
                    <p class="text-xs text-emerald-600 mt-2 flex items-center gap-1">
                        <i class="fas fa-check-circle"></i> Debit = Kredit · Diposting
                    </p>
                </div>
            </div>

            <!-- Table -->
            <div class="w-full bg-white rounded-xl shadow-md border border-gray-100">
                <div class="p-5 overflow-auto">
                    <table class="w-full text-left">
                        <thead class="bg-gray-100 text-gray-600 text-sm leading-normal">
                            <tr>
                                <th class="p-4 font-bold rounded-tl-lg text-center" width="5%">No</th>
                                <th class="p-4 font-bold">Akun</th>
                                <th class="p-4 font-bold">Keterangan</th>
                                <th class="p-4 font-bold text-right">Debit</th>
                                <th class="p-4 font-bold text-right rounded-tr-lg">Kredit</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-700 text-sm">
                            @php $no = 1; @endphp
                            @foreach ($journal['lines'] as $line)
                                <tr class="hover:bg-gray-50 transition duration-150">
                                    <td class="p-4 font-medium text-center">{{ $no++ }}</td>
                                    <td class="p-4 {{ $line['credit'] > 0 ? 'pl-10' : '' }}">
                                        <a href="{{ $ledgerLink($line['account_code']) }}" class="hover:text-indigo-600" title="Lihat buku besar">
                                            <span class="font-mono text-xs text-gray-400">{{ $line['account_code'] }}</span>
                                            <span class="font-semibold">{{ $line['account_name'] }}</span>
                                        </a>
                                    </td>
                                    <td class="p-4 text-xs text-gray-500">{{ $line['description'] ?? '-' }}</td>
                                    <td class="p-4 text-right whitespace-nowrap">{{ $line['debit'] ? $rupiah($line['debit']) : '-' }}</td>
                                    <td class="p-4 text-right whitespace-nowrap">{{ $line['credit'] ? $rupiah($line['credit']) : '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="text-sm font-bold text-gray-800 bg-gray-50">
                            <tr>
                                <td class="p-4" colspan="3">Total</td>
                                <td class="p-4 text-right whitespace-nowrap">{{ $rupiah($journal['total_amount']) }}</td>
                                <td class="p-4 text-right whitespace-nowrap">{{ $rupiah($journal['total_amount']) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

        </div>
    </main>

    <!-- SCRIPTS -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    @include('sweetalert::alert')
    @include('layout.loading')
</body>

</html>
