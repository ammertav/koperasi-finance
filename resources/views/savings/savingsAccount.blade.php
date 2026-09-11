<!DOCTYPE html>
<html lang="id">

<head>
    <title>Rekening Simpanan</title>
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
                $rupiah = fn (int $amount) => 'Rp ' . number_format($amount, 0, ',', '.');
                $productColor = fn (string $code) => match ($code) {
                    'SP' => 'bg-blue-100 text-blue-700 border-blue-200',
                    'SW' => 'bg-purple-100 text-purple-700 border-purple-200',
                    default => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                };
            @endphp

            <!-- Header -->
            <div
                class="md:flex justify-between items-center bg-white p-5 rounded-xl shadow-sm border border-gray-100 space-y-2 md:space-y-0">
                <div>
                    <h1 class="font-bold text-2xl text-gray-800 flex items-center gap-2">
                        <i class="fas fa-book text-emerald-600"></i> Rekening Simpanan
                    </h1>
                    <p class="text-sm text-gray-500 mt-1">
                        {{ $showOffice ? 'Rekening simpanan seluruh kantor' : 'Rekening simpanan ' . auth()->user()->office->name }}
                    </p>
                </div>
                @if (auth()->user()->hasAccess('savings', 'operate'))
                    <x-button :href="route('createSavingsTransaction')" size="lg" variant="primary"
                        class="bg-emerald-600 hover:bg-emerald-700 shadow-md" icon="exchange-alt">Setor / Tarik</x-button>
                @endif
            </div>

            <!-- KPI Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @foreach ($summary as $code => $item)
                    <a href="{{ route('savingsAccount', ['product' => $code]) }}"
                        class="bg-white p-5 rounded-xl shadow-sm border {{ $productCode === $code ? 'border-emerald-400' : 'border-gray-100' }} hover:shadow-md transition">
                        <p class="text-xs text-gray-500 uppercase tracking-wide">{{ $item['name'] }}</p>
                        <h2 class="text-2xl font-bold text-gray-800 mt-1">{{ $rupiah($item['total_balance']) }}</h2>
                        <p class="text-xs text-emerald-600 mt-2">{{ number_format($item['account_count'], 0, ',', '.') }} rekening aktif</p>
                    </a>
                @endforeach
            </div>

            <!-- Table -->
            <div class="w-full bg-white rounded-xl shadow-md border border-gray-100">
                <div class="p-5 border-b border-gray-100">
                    <form method="GET" action="{{ route('savingsAccount') }}" data-page-loading class="flex flex-wrap items-end gap-3">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Produk</label>
                            <select name="product" onchange="this.form.submit()"
                                class="rounded-lg border-gray-300 shadow-sm p-2.5 border focus:ring-2 focus:ring-emerald-500">
                                <option value="">Semua produk</option>
                                @foreach ($products as $code => $name)
                                    <option value="{{ $code }}" @selected($productCode === $code)>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </form>
                </div>
                <div class="p-5 overflow-auto">
                    <table id="myTable" class="w-full text-left">
                        <thead class="bg-gray-100 text-gray-600 text-sm leading-normal">
                            <tr>
                                <th class="p-4 font-bold rounded-tl-lg text-center" width="5%">No</th>
                                <th class="p-4 font-bold">Rekening</th>
                                <th class="p-4 font-bold">Anggota</th>
                                @if ($showOffice)
                                    <th class="p-4 font-bold">Kantor</th>
                                @endif
                                <th class="p-4 font-bold text-center">Produk</th>
                                <th class="p-4 font-bold">Transaksi Terakhir</th>
                                <th class="p-4 font-bold text-right">Saldo</th>
                                <th class="p-4 font-bold text-center rounded-tr-lg" width="10%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-700 text-sm">
                            @php $no = 1; @endphp
                            @foreach ($accounts as $item)
                                <tr class="hover:bg-gray-50 transition duration-150">
                                    <td class="p-4 font-medium text-center">{{ $no++ }}</td>
                                    <td class="p-4 font-mono font-semibold text-gray-900">{{ $item['number'] }}</td>
                                    <td class="p-4 space-y-1">
                                        <div class="font-bold text-gray-900 text-base">{{ $item['member_name'] }}</div>
                                        <div class="text-xs text-gray-400 font-mono">{{ $item['member_number'] }}</div>
                                    </td>
                                    @if ($showOffice)
                                        <td class="p-4 whitespace-nowrap">{{ $item['office_code'] }} — {{ $item['office_name'] }}</td>
                                    @endif
                                    <td class="p-4 text-center">
                                        <span class="{{ $productColor($item['product_code']) }} text-xs px-3 py-1 rounded-full font-bold border whitespace-nowrap">{{ $item['product_name'] }}</span>
                                    </td>
                                    <td class="p-4 whitespace-nowrap" data-order="{{ $item['last_transaction_date']->toDateString() }}">
                                        {{ $item['last_transaction_date']->format('d M Y') }}
                                    </td>
                                    <td class="p-4 text-right whitespace-nowrap font-semibold" data-order="{{ $item['balance'] }}">{{ $rupiah($item['balance']) }}</td>
                                    <td class="p-4">
                                        <div class="flex justify-center items-center gap-2">
                                            <a href="{{ route('detailSavingsAccount', $item['number']) }}"
                                                class="w-10 h-10 flex items-center justify-center bg-blue-500 text-white rounded-lg shadow hover:bg-blue-600 hover:scale-105 transition"
                                                title="Lihat mutasi">
                                                <i class="fas fa-eye text-lg"></i>
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
