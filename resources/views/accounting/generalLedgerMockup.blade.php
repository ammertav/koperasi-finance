<!DOCTYPE html>
<html lang="id">

<head>
    <title>Buku Besar</title>
    @include('layout.head')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-gray-50 font-sans">
    @include('layout.sidebar')

    <main class="md:ml-64 xl:ml-72 2xl:ml-72">
        @include('layout.navbar')
        <div class="p-6 space-y-6">

            @php
                $money = fn (int $amount) => ($amount < 0 ? '(Rp ' : 'Rp ') . number_format(abs($amount), 0, ',', '.') . ($amount < 0 ? ')' : '');
            @endphp

            <!-- Header -->
            <div
                class="md:flex justify-between items-center bg-white p-5 rounded-xl shadow-sm border border-gray-100 space-y-2 md:space-y-0">
                <div>
                    <h1 class="font-bold text-2xl text-gray-800 flex items-center gap-2">
                        <i class="fas fa-book-open text-indigo-600"></i> Buku Besar
                    </h1>
                    <p class="text-sm text-gray-500 mt-1">
                        {{ $selectedOffice ? $selectedOffice->code.' — '.$selectedOffice->name : 'Konsolidasi semua kantor' }}
                        · {{ $startDate->format('d M Y') }} s.d. {{ $endDate->format('d M Y') }}
                    </p>
                </div>
            </div>

            <x-ledger-filter :action="route('generalLedgerMockup')" :offices="$offices" :selected-office="$selectedOffice"
                :start-date="$startDate" :end-date="$endDate">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Akun</label>
                    <select name="account_code"
                        class="w-full rounded-lg border-gray-300 shadow-sm p-2.5 border focus:ring-2 focus:ring-indigo-500">
                        @foreach ($accounts as $option)
                            <option value="{{ $option['code'] }}" @selected($account['code'] === $option['code'])>
                                {{ $option['code'] }} — {{ $option['name'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </x-ledger-filter>

            <!-- Summary Cards -->
            <div class="grid grid-cols-2 xl:grid-cols-4 gap-4">
                <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Saldo Awal</p>
                    <h2 class="text-2xl font-bold text-gray-800 mt-1">{{ $money($ledger['opening_balance']) }}</h2>
                    <p class="text-xs text-gray-400 mt-2">Sebelum {{ $startDate->format('d M Y') }}</p>
                </div>
                <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Mutasi Debit</p>
                    <h2 class="text-2xl font-bold text-gray-800 mt-1">{{ $money($ledger['total_debit']) }}</h2>
                    <p class="text-xs text-gray-400 mt-2">{{ count($ledger['rows']) }} baris jurnal</p>
                </div>
                <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Mutasi Kredit</p>
                    <h2 class="text-2xl font-bold text-gray-800 mt-1">{{ $money($ledger['total_credit']) }}</h2>
                    <p class="text-xs text-gray-400 mt-2">Saldo normal {{ \App\Models\Account::NORMAL_BALANCES[$account['normal_balance']] }}</p>
                </div>
                <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Saldo Akhir</p>
                    <h2 class="text-2xl font-bold text-indigo-700 mt-1">{{ $money($ledger['closing_balance']) }}</h2>
                    <p class="text-xs text-gray-400 mt-2">Per {{ $endDate->format('d M Y') }}</p>
                </div>
            </div>

            <!-- Table -->
            <div class="w-full bg-white rounded-xl shadow-md border border-gray-100">
                <div class="p-5 overflow-auto">
                    <h3 class="text-sm font-bold text-indigo-600 uppercase tracking-wider mb-4 border-b pb-2">
                        {{ $account['code'] }} — {{ $account['name'] }}
                    </h3>
                    <table class="w-full text-left">
                        <thead class="bg-gray-100 text-gray-600 text-sm leading-normal">
                            <tr>
                                <th class="p-4 font-bold rounded-tl-lg text-center" width="5%">No</th>
                                <th class="p-4 font-bold">Tanggal</th>
                                <th class="p-4 font-bold">Nomor Jurnal</th>
                                @unless ($selectedOffice)
                                    <th class="p-4 font-bold">Kantor</th>
                                @endunless
                                <th class="p-4 font-bold">Keterangan</th>
                                <th class="p-4 font-bold text-right">Debit</th>
                                <th class="p-4 font-bold text-right">Kredit</th>
                                <th class="p-4 font-bold text-right rounded-tr-lg">Saldo</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-700 text-sm">
                            <tr class="bg-gray-50">
                                <td class="p-4" colspan="{{ $selectedOffice ? 6 : 7 }}">
                                    <span class="font-semibold text-gray-600">Saldo awal</span>
                                </td>
                                <td class="p-4 text-right font-bold whitespace-nowrap">{{ $money($ledger['opening_balance']) }}</td>
                            </tr>
                            @php $no = 1; @endphp
                            @foreach ($ledger['rows'] as $row)
                                <tr class="hover:bg-gray-50 transition duration-150">
                                    <td class="p-4 font-medium text-center">{{ $no++ }}</td>
                                    <td class="p-4 whitespace-nowrap">{{ $row['journal']['book_date']->format('d M Y') }}</td>
                                    <td class="p-4 whitespace-nowrap">
                                        <a href="{{ route('detailJournalMockup', $row['journal']['number']) }}"
                                            class="font-mono font-semibold text-indigo-600 hover:underline">{{ $row['journal']['number'] }}</a>
                                    </td>
                                    @unless ($selectedOffice)
                                        <td class="p-4 whitespace-nowrap">{{ $row['journal']['office_code'] }}</td>
                                    @endunless
                                    <td class="p-4 text-xs text-gray-500">{{ \Illuminate\Support\Str::limit($row['journal']['description'], 60) }}</td>
                                    <td class="p-4 text-right whitespace-nowrap">{{ $row['debit'] ? $money($row['debit']) : '-' }}</td>
                                    <td class="p-4 text-right whitespace-nowrap">{{ $row['credit'] ? $money($row['credit']) : '-' }}</td>
                                    <td class="p-4 text-right whitespace-nowrap font-semibold">{{ $money($row['balance']) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="text-sm font-bold text-gray-800 bg-gray-50">
                            <tr>
                                <td class="p-4" colspan="{{ $selectedOffice ? 4 : 5 }}">Total mutasi dan saldo akhir</td>
                                <td class="p-4 text-right whitespace-nowrap">{{ $money($ledger['total_debit']) }}</td>
                                <td class="p-4 text-right whitespace-nowrap">{{ $money($ledger['total_credit']) }}</td>
                                <td class="p-4 text-right whitespace-nowrap text-indigo-700">{{ $money($ledger['closing_balance']) }}</td>
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
