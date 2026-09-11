<!DOCTYPE html>
<html lang="id">

<head>
    <title>Setor & Tarik Simpanan</title>
    @include('layout.head')
    <link href="//cdn.datatables.net/2.0.2/css/dataTables.dataTables.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .dt-container .dt-length select {
            padding-right: 2rem;
            border-radius: 0.5rem;
        }

        .dt-container .dt-search input {
            padding: 0.5rem;
            border-radius: 0.5rem;
            border: 1px solid #d1d5db;
        }

        table.dataTable.no-footer {
            border-bottom: 1px solid #e5e7eb;
        }
    </style>
</head>

<body class="bg-gray-50 font-sans">
    @include('layout.sidebar')

    <main class="md:ml-64 xl:ml-72 2xl:ml-72">
        @include('layout.navbar')
        <div class="p-6 space-y-6">

            @php
                $user = auth()->user();
                $rupiah = fn (int $amount) => 'Rp ' . number_format($amount, 0, ',', '.');
                $statusColor = fn (string $status) => match ($status) {
                    'posted' => 'bg-green-100 text-green-700 border-green-200',
                    'pending_authorization' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
                    'rejected' => 'bg-red-100 text-red-700 border-red-200',
                    default => 'bg-gray-100 text-gray-700 border-gray-200',
                };
            @endphp

            <!-- Header -->
            <div
                class="md:flex justify-between items-center bg-white p-5 rounded-xl shadow-sm border border-gray-100 space-y-2 md:space-y-0">
                <div>
                    <h1 class="font-bold text-2xl text-gray-800 flex items-center gap-2">
                        <i class="fas fa-exchange-alt text-emerald-600"></i> Setor & Tarik Simpanan
                    </h1>
                    <p class="text-sm text-gray-500 mt-1">
                        Transaksi tunai teller tanggal buku {{ $user->office->book_date->format('d M Y') }}
                    </p>
                </div>
                @if ($user->hasAccess('savings', 'operate'))
                    <x-button :href="route('createSavingsTransaction')" size="lg" variant="primary"
                        class="bg-emerald-600 hover:bg-emerald-700 shadow-md" icon="plus">Transaksi Baru</x-button>
                @endif
            </div>

            @if ($user->hasRole('TLR') && ! $cashSession)
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-center">
                    <p class="text-yellow-800 font-semibold">Sesi kas teller belum dibuka</p>
                    <p class="text-sm text-yellow-600 mt-1">Buka sesi kas di menu <a href="{{ route('cashSession') }}" class="font-bold underline">Sesi Kas Teller</a>
                        sebelum melayani setoran dan penarikan.</p>
                </div>
            @endif

            <!-- KPI Cards -->
            <div class="grid grid-cols-2 xl:grid-cols-3 gap-4">
                <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Setoran Diposting</p>
                    <h2 class="text-2xl font-bold text-gray-800 mt-1">{{ $rupiah($summary['deposit_total']) }}</h2>
                    <p class="text-xs text-emerald-600 mt-2">{{ $summary['deposit_count'] }} transaksi</p>
                </div>
                <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Penarikan Diposting</p>
                    <h2 class="text-2xl font-bold text-gray-800 mt-1">{{ $rupiah($summary['withdrawal_total']) }}</h2>
                    <p class="text-xs text-red-600 mt-2">{{ $summary['withdrawal_count'] }} transaksi</p>
                </div>
                <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Menunggu Otorisasi</p>
                    <h2 class="text-2xl font-bold text-gray-800 mt-1">{{ $summary['pending_count'] }}</h2>
                    <p class="text-xs text-yellow-600 mt-2">Penarikan di atas batas teller</p>
                </div>
            </div>

            <!-- Table -->
            <div class="w-full bg-white rounded-xl shadow-md border border-gray-100">
                <div class="p-5 overflow-auto">
                    <table id="myTable" class="w-full text-left">
                        <thead class="bg-gray-100 text-gray-600 text-sm leading-normal">
                            <tr>
                                <th class="p-4 font-bold rounded-tl-lg text-center" width="5%">No</th>
                                <th class="p-4 font-bold">Transaksi</th>
                                <th class="p-4 font-bold">Anggota</th>
                                @if ($showOffice)
                                    <th class="p-4 font-bold">Kantor</th>
                                @endif
                                <th class="p-4 font-bold text-right">Nominal</th>
                                <th class="p-4 font-bold">Teller</th>
                                <th class="p-4 font-bold text-center">Status</th>
                                <th class="p-4 font-bold text-center rounded-tr-lg" width="10%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-700 text-sm">
                            @php $no = 1; @endphp
                            @foreach ($transactions as $item)
                                <tr class="hover:bg-gray-50 transition duration-150">
                                    <td class="p-4 font-medium text-center">{{ $no++ }}</td>
                                    <td class="p-4 space-y-1">
                                        <div class="font-bold text-gray-900">{{ $item['type_label'] }} {{ $item['product_name'] }}</div>
                                        <div class="text-xs text-gray-400 font-mono">{{ $item['number'] }} · {{ $item['created_at']->format('H:i') }}</div>
                                    </td>
                                    <td class="p-4 space-y-1">
                                        <div class="font-semibold text-gray-900">{{ $item['member_name'] }}</div>
                                        <div class="text-xs text-gray-400 font-mono">{{ $item['account_number'] }}</div>
                                    </td>
                                    @if ($showOffice)
                                        <td class="p-4 whitespace-nowrap">{{ $item['office_code'] }} — {{ $item['office_name'] }}</td>
                                    @endif
                                    <td class="p-4 text-right whitespace-nowrap font-semibold {{ $item['type'] === 'deposit' ? 'text-emerald-700' : 'text-red-600' }}">
                                        {{ $item['type'] === 'deposit' ? '+' : '−' }} {{ $rupiah($item['amount']) }}
                                    </td>
                                    <td class="p-4">{{ $item['teller_name'] }}</td>
                                    <td class="p-4 text-center">
                                        <span class="{{ $statusColor($item['status']) }} text-xs px-3 py-1 rounded-full font-bold border whitespace-nowrap">{{ $item['status_label'] }}</span>
                                    </td>
                                    <td class="p-4">
                                        <div class="flex justify-center items-center gap-2">
                                            <a href="{{ route('detailSavingsTransaction', $item['id']) }}"
                                                class="w-10 h-10 flex items-center justify-center bg-blue-500 text-white rounded-lg shadow hover:bg-blue-600 hover:scale-105 transition"
                                                title="Lihat bukti transaksi">
                                                <i class="fas fa-receipt text-lg"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </main>

    <!-- SCRIPTS -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="//cdn.datatables.net/2.0.2/js/dataTables.min.js"></script>
    <script src="{{ asset('modal/savingsAccount.js') }}"></script>

    @include('sweetalert::alert')
    @include('layout.loading')
</body>

</html>
