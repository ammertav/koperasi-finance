<!DOCTYPE html>
<html lang="id">

<head>
    <title>Neraca Saldo</title>
    @include('layout.head')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-gray-50 font-sans">
    @include('layout.sidebar')

    <main class="md:ml-64 xl:ml-72 2xl:ml-72">
        @include('layout.navbar')
        <div class="p-6 space-y-6">

            @php
                $money = fn (int $amount) => $amount === 0 ? '-' : 'Rp ' . number_format($amount, 0, ',', '.');
                $ledgerLink = fn (string $accountCode) => route('generalLedgerMockup', [
                    'account_code' => $accountCode,
                    'office_id' => $selectedOffice?->id,
                    'start_date' => $startDate->toDateString(),
                    'end_date' => $endDate->toDateString(),
                ]);
            @endphp

            <!-- Header -->
            <div
                class="md:flex justify-between items-center bg-white p-5 rounded-xl shadow-sm border border-gray-100 space-y-2 md:space-y-0">
                <div>
                    <h1 class="font-bold text-2xl text-gray-800 flex items-center gap-2">
                        <i class="fas fa-balance-scale text-indigo-600"></i> Neraca Saldo
                    </h1>
                    <p class="text-sm text-gray-500 mt-1">
                        {{ $selectedOffice ? $selectedOffice->code.' — '.$selectedOffice->name : 'Konsolidasi semua kantor' }}
                        · {{ $startDate->format('d M Y') }} s.d. {{ $endDate->format('d M Y') }}
                    </p>
                </div>
                @if ($isBalanced)
                    <span class="bg-green-100 text-green-700 border-green-200 text-sm px-4 py-2 rounded-full font-bold border">
                        <i class="fas fa-check-circle"></i> Seimbang
                    </span>
                @else
                    <span class="bg-red-100 text-red-700 border-red-200 text-sm px-4 py-2 rounded-full font-bold border">
                        <i class="fas fa-exclamation-triangle"></i> Tidak seimbang
                    </span>
                @endif
            </div>

            <x-ledger-filter :action="route('trialBalanceMockup')" :offices="$offices" :selected-office="$selectedOffice"
                :start-date="$startDate" :end-date="$endDate" />

            @unless ($selectedOffice)
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-center">
                    <p class="text-yellow-800 font-semibold">Saldo RAK saling menghapus pada konsolidasi</p>
                    <p class="text-sm text-yellow-600 mt-1">
                        Jumlah seluruh akun RAK bernilai nol saat semua kantor digabung. Akun "RAK Kantor Pusat" menampung buku semua cabang.
                    </p>
                </div>
            @endunless

            @if ($reconciliation !== [])
                <!-- Subledger Reconciliation -->
                <div class="bg-white p-6 rounded-xl shadow-md border border-gray-100">
                    <h3 class="text-sm font-bold text-indigo-600 uppercase tracking-wider mb-4 border-b pb-2">
                        <i class="fas fa-link"></i> Pencocokan Buku Pembantu dengan Buku Besar
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-4">
                        @foreach ($reconciliation as $item)
                            <div class="p-4 rounded-lg border {{ $item['is_matched'] ? 'border-green-200 bg-green-50' : 'border-red-200 bg-red-50' }}">
                                <p class="text-xs text-gray-500"><span class="font-mono">{{ $item['account_code'] }}</span> {{ $item['label'] }}</p>
                                <p class="font-bold text-gray-800 mt-1">Rp {{ number_format($item['ledger'], 0, ',', '.') }}</p>
                                <p class="text-xs mt-1 {{ $item['is_matched'] ? 'text-green-700' : 'text-red-700' }}">
                                    <i class="fas fa-{{ $item['is_matched'] ? 'check-circle' : 'exclamation-triangle' }}"></i>
                                    {{ $item['is_matched'] ? 'Cocok dengan rincian rekening' : 'Selisih Rp ' . number_format($item['subledger'] - $item['ledger'], 0, ',', '.') }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Table -->
            <div class="w-full bg-white rounded-xl shadow-md border border-gray-100">
                <div class="p-5 overflow-auto">
                    <table class="w-full text-left">
                        <thead class="bg-gray-100 text-gray-600 text-sm leading-normal">
                            <tr>
                                <th class="p-4 font-bold rounded-tl-lg" rowspan="2">Kode</th>
                                <th class="p-4 font-bold" rowspan="2">Nama Akun</th>
                                <th class="px-4 pt-4 pb-1 font-bold text-center" colspan="2">Saldo Awal</th>
                                <th class="px-4 pt-4 pb-1 font-bold text-center" colspan="2">Mutasi</th>
                                <th class="px-4 pt-4 pb-1 font-bold text-center rounded-tr-lg" colspan="2">Saldo Akhir</th>
                            </tr>
                            <tr class="text-xs">
                                @foreach (range(1, 3) as $group)
                                    <th class="px-4 pb-3 pt-1 font-bold text-right">Debit</th>
                                    <th class="px-4 pb-3 pt-1 font-bold text-right">Kredit</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="text-gray-700 text-sm">
                            @foreach ($rows as $row)
                                <tr class="hover:bg-gray-50 transition duration-150">
                                    <td class="p-4 font-mono whitespace-nowrap">{{ $row['account']['code'] }}</td>
                                    <td class="p-4">
                                        <a href="{{ $ledgerLink($row['account']['code']) }}" class="font-semibold hover:text-indigo-600"
                                            title="Lihat buku besar">{{ $row['account']['name'] }}</a>
                                        @if ($row['account']['is_inter_office'])
                                            <span class="bg-blue-100 text-blue-700 border-blue-200 text-xs px-2 py-0.5 rounded-full font-bold border">RAK</span>
                                        @endif
                                    </td>
                                    <td class="p-4 text-right whitespace-nowrap">{{ $money($row['opening_debit']) }}</td>
                                    <td class="p-4 text-right whitespace-nowrap">{{ $money($row['opening_credit']) }}</td>
                                    <td class="p-4 text-right whitespace-nowrap">{{ $money($row['debit']) }}</td>
                                    <td class="p-4 text-right whitespace-nowrap">{{ $money($row['credit']) }}</td>
                                    <td class="p-4 text-right whitespace-nowrap font-semibold">{{ $money($row['closing_debit']) }}</td>
                                    <td class="p-4 text-right whitespace-nowrap font-semibold">{{ $money($row['closing_credit']) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="text-sm font-bold text-gray-800 bg-gray-50">
                            <tr>
                                <td class="p-4" colspan="2">Total</td>
                                <td class="p-4 text-right whitespace-nowrap">{{ $money($totals['opening_debit']) }}</td>
                                <td class="p-4 text-right whitespace-nowrap">{{ $money($totals['opening_credit']) }}</td>
                                <td class="p-4 text-right whitespace-nowrap">{{ $money($totals['debit']) }}</td>
                                <td class="p-4 text-right whitespace-nowrap">{{ $money($totals['credit']) }}</td>
                                <td class="p-4 text-right whitespace-nowrap text-indigo-700">{{ $money($totals['closing_debit']) }}</td>
                                <td class="p-4 text-right whitespace-nowrap text-indigo-700">{{ $money($totals['closing_credit']) }}</td>
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
