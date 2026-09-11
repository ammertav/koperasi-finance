<!DOCTYPE html>
<html lang="id">

<head>
    <title>Posisi Kas</title>
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
                $branchPositions = array_values(array_filter($positions, fn (array $position) => $position['office_code'] !== '00'));
            @endphp

            <!-- Header -->
            <div
                class="md:flex justify-between items-center bg-white p-5 rounded-xl shadow-sm border border-gray-100 space-y-2 md:space-y-0">
                <div>
                    <h1 class="font-bold text-2xl text-gray-800 flex items-center gap-2">
                        <i class="fas fa-coins text-teal-600"></i> Posisi Kas dan Bank
                    </h1>
                    <p class="text-sm text-gray-500 mt-1">{{ now()->translatedFormat('l, d F Y') }} · dihitung dari jurnal terposting, tanpa rekap manual</p>
                </div>
            </div>

            <!-- KPI Cards -->
            <div class="grid grid-cols-2 xl:grid-cols-4 gap-4">
                <div class="bg-teal-50 p-5 rounded-xl shadow-sm border border-teal-100 flex justify-between items-center">
                    <div>
                        <p class="text-teal-600 uppercase font-semibold tracking-wide text-xs">Total Kas dan Bank</p>
                        <h2 class="text-2xl font-bold text-teal-700 mt-2">{{ $rupiah($totals['total']) }}</h2>
                    </div>
                    <i class="fas fa-coins text-4xl text-teal-300 opacity-50"></i>
                </div>
                <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Kas Brankas</p>
                    <h2 class="text-2xl font-bold text-gray-800 mt-1">{{ $rupiah($totals['vault_cash']) }}</h2>
                    <p class="text-xs text-gray-400 mt-2">Seluruh cabang</p>
                </div>
                <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Kas Teller</p>
                    <h2 class="text-2xl font-bold text-gray-800 mt-1">{{ $rupiah($totals['teller_cash']) }}</h2>
                    <p class="text-xs text-emerald-600 mt-2">{{ $totals['open_session_count'] }} sesi kas terbuka</p>
                </div>
                <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Bank</p>
                    <h2 class="text-2xl font-bold text-gray-800 mt-1">{{ $rupiah($totals['bank']) }}</h2>
                    <p class="text-xs text-gray-400 mt-2">Rekening kantor pusat</p>
                </div>
            </div>

            @if (count($branchPositions) > 1)
                <!-- Chart -->
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition">
                    <h3 class="font-bold text-gray-800 text-lg">Kas per Cabang</h3>
                    <p class="text-xs text-gray-500 mt-1">Kas brankas dan kas teller setiap cabang per tanggal buku</p>
                    <canvas id="cashChart" height="100"
                        data-labels='@json(array_column($branchPositions, 'office_name'))'
                        data-vault='@json(array_column($branchPositions, 'vault_cash'))'
                        data-teller='@json(array_column($branchPositions, 'teller_cash'))'></canvas>
                </div>
            @endif

            <!-- Table -->
            <div class="w-full bg-white rounded-xl shadow-md border border-gray-100">
                <div class="p-5 overflow-auto">
                    <table class="w-full text-left">
                        <thead class="bg-gray-100 text-gray-600 text-sm leading-normal">
                            <tr>
                                <th class="p-4 font-bold rounded-tl-lg text-center" width="5%">No</th>
                                <th class="p-4 font-bold">Kantor</th>
                                <th class="p-4 font-bold text-right">Kas Teller</th>
                                <th class="p-4 font-bold text-right">Kas Brankas</th>
                                <th class="p-4 font-bold text-right">Bank</th>
                                <th class="p-4 font-bold text-right">Total</th>
                                <th class="p-4 font-bold text-center rounded-tr-lg">Aktivitas Hari Ini</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-700 text-sm">
                            @php $no = 1; @endphp
                            @foreach ($positions as $item)
                                <tr class="hover:bg-gray-50 transition duration-150">
                                    <td class="p-4 font-medium text-center">{{ $no++ }}</td>
                                    <td class="p-4 space-y-1">
                                        <div class="font-bold text-gray-900 text-base">{{ $item['office_name'] }}</div>
                                        <div class="text-xs text-gray-400">Kode {{ $item['office_code'] }} · Tanggal buku {{ $item['book_date']->format('d M Y') }}</div>
                                    </td>
                                    <td class="p-4 text-right whitespace-nowrap">{{ $item['teller_cash'] ? $rupiah($item['teller_cash']) : '-' }}</td>
                                    <td class="p-4 text-right whitespace-nowrap">{{ $item['vault_cash'] ? $rupiah($item['vault_cash']) : '-' }}</td>
                                    <td class="p-4 text-right whitespace-nowrap">{{ $item['bank'] ? $rupiah($item['bank']) : '-' }}</td>
                                    <td class="p-4 text-right whitespace-nowrap font-bold">{{ $rupiah($item['total']) }}</td>
                                    <td class="p-4 text-center space-x-1">
                                        @if ($item['open_session_count'])
                                            <span class="bg-green-100 text-green-700 border-green-200 text-xs px-3 py-1 rounded-full font-bold border whitespace-nowrap">{{ $item['open_session_count'] }} sesi terbuka</span>
                                        @endif
                                        @if ($item['pending_transfer_count'])
                                            <span class="bg-yellow-100 text-yellow-700 border-yellow-200 text-xs px-3 py-1 rounded-full font-bold border whitespace-nowrap">{{ $item['pending_transfer_count'] }} menunggu konfirmasi</span>
                                        @endif
                                        @if (! $item['open_session_count'] && ! $item['pending_transfer_count'])
                                            <span class="text-gray-300">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="text-sm font-bold text-gray-800 bg-gray-50">
                            <tr>
                                <td class="p-4" colspan="2">Total</td>
                                <td class="p-4 text-right whitespace-nowrap">{{ $rupiah($totals['teller_cash']) }}</td>
                                <td class="p-4 text-right whitespace-nowrap">{{ $rupiah($totals['vault_cash']) }}</td>
                                <td class="p-4 text-right whitespace-nowrap">{{ $rupiah($totals['bank']) }}</td>
                                <td class="p-4 text-right whitespace-nowrap text-teal-700">{{ $rupiah($totals['total']) }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

        </div>
    </main>

    <!-- SCRIPTS -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="{{ asset('modal/cashPosition.js') }}"></script>

    @include('sweetalert::alert')
    @include('layout.loading')
</body>

</html>
