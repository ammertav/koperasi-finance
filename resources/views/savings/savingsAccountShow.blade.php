<!DOCTYPE html>
<html lang="id">

<head>
    <title>Mutasi Rekening {{ $account['number'] }}</title>
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
                $mutationRows = array_slice($mutations, 1);
            @endphp

            <!-- Header -->
            <div
                class="md:flex justify-between items-center bg-white p-5 rounded-xl shadow-sm border border-gray-100 space-y-2 md:space-y-0">
                <div>
                    <h1 class="font-bold text-2xl text-gray-800 flex items-center gap-2">
                        <i class="fas fa-book text-emerald-600"></i> Mutasi Rekening <span class="font-mono">{{ $account['number'] }}</span>
                    </h1>
                    <p class="text-sm text-gray-500 mt-1">{{ $account['product_name'] }} · {{ $account['office_code'] }} — {{ $account['office_name'] }}</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <x-button :href="route('savingsAccount')" variant="secondary" icon="arrow-left">Kembali</x-button>
                    <x-button :href="route('detailMember', $member['id'])" variant="secondary" icon="id-card">Profil Anggota</x-button>
                    <x-button :href="route('printSavingsAccount', $account['number'])" variant="primary"
                        class="bg-emerald-600 hover:bg-emerald-700" icon="print">Cetak Mutasi</x-button>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-2 xl:grid-cols-4 gap-4">
                <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Pemilik</p>
                    <h2 class="text-xl font-bold text-gray-800 mt-1">{{ $member['name'] }}</h2>
                    <p class="text-xs text-gray-400 mt-2 font-mono">{{ $member['number'] }}</p>
                </div>
                <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Saldo Awal Periode</p>
                    <h2 class="text-2xl font-bold text-gray-800 mt-1">{{ $rupiah($summary['opening_balance']) }}</h2>
                    <p class="text-xs text-gray-400 mt-2">Per {{ $summary['period_start']->format('d M Y') }}</p>
                </div>
                <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Setoran / Penarikan</p>
                    <h2 class="text-lg font-bold text-emerald-700 mt-1">+ {{ $rupiah($summary['total_credit']) }}</h2>
                    <p class="text-sm font-bold text-red-600">− {{ $rupiah($summary['total_debit']) }}</p>
                </div>
                <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Saldo Akhir</p>
                    <h2 class="text-2xl font-bold text-emerald-700 mt-1">{{ $rupiah($account['balance']) }}</h2>
                    <p class="text-xs text-gray-400 mt-2">
                        @if ($account['product_code'] === 'SS')
                            Dapat ditarik {{ $rupiah($summary['available_balance']) }}
                        @else
                            Tidak dapat ditarik selama keanggotaan aktif
                        @endif
                    </p>
                </div>
            </div>

            <!-- Table -->
            <div class="w-full bg-white rounded-xl shadow-md border border-gray-100">
                <div class="p-5 overflow-auto">
                    <h3 class="text-sm font-bold text-emerald-600 uppercase tracking-wider mb-4 border-b pb-2">
                        Mutasi {{ $summary['period_start']->format('d M Y') }} s.d. {{ \Illuminate\Support\Carbon::parse($account['office_book_date'])->format('d M Y') }}
                    </h3>
                    <table class="w-full text-left">
                        <thead class="bg-gray-100 text-gray-600 text-sm leading-normal">
                            <tr>
                                <th class="p-4 font-bold rounded-tl-lg text-center" width="5%">No</th>
                                <th class="p-4 font-bold">Tanggal</th>
                                <th class="p-4 font-bold">Referensi</th>
                                <th class="p-4 font-bold">Keterangan</th>
                                <th class="p-4 font-bold text-right">Penarikan</th>
                                <th class="p-4 font-bold text-right">Setoran</th>
                                <th class="p-4 font-bold text-right rounded-tr-lg">Saldo</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-700 text-sm">
                            <tr class="bg-gray-50">
                                <td class="p-4" colspan="6"><span class="font-semibold text-gray-600">Saldo awal periode</span></td>
                                <td class="p-4 text-right font-bold whitespace-nowrap">{{ $rupiah($summary['opening_balance']) }}</td>
                            </tr>
                            @php $no = 1; @endphp
                            @forelse ($mutationRows as $row)
                                <tr class="hover:bg-gray-50 transition duration-150">
                                    <td class="p-4 font-medium text-center">{{ $no++ }}</td>
                                    <td class="p-4 whitespace-nowrap">{{ $row['date']->format('d M Y') }}</td>
                                    <td class="p-4 whitespace-nowrap font-mono text-xs">
                                        @if ($row['transaction_id'])
                                            <a href="{{ route('detailSavingsTransaction', $row['transaction_id']) }}" class="font-semibold text-emerald-700 hover:underline">{{ $row['number'] }}</a>
                                        @else
                                            {{ $row['number'] }}
                                        @endif
                                    </td>
                                    <td class="p-4">{{ $row['description'] }}</td>
                                    <td class="p-4 text-right whitespace-nowrap text-red-600">{{ $row['debit'] ? $rupiah($row['debit']) : '-' }}</td>
                                    <td class="p-4 text-right whitespace-nowrap text-emerald-700">{{ $row['credit'] ? $rupiah($row['credit']) : '-' }}</td>
                                    <td class="p-4 text-right whitespace-nowrap font-semibold">{{ $rupiah($row['balance']) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="p-4 text-center text-gray-400" colspan="7">Tidak ada mutasi pada periode ini</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="text-sm font-bold text-gray-800 bg-gray-50">
                            <tr>
                                <td class="p-4" colspan="4">Total mutasi dan saldo akhir</td>
                                <td class="p-4 text-right whitespace-nowrap">{{ $rupiah($summary['total_debit']) }}</td>
                                <td class="p-4 text-right whitespace-nowrap">{{ $rupiah($summary['total_credit']) }}</td>
                                <td class="p-4 text-right whitespace-nowrap text-emerald-700">{{ $rupiah($account['balance']) }}</td>
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
