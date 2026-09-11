<!DOCTYPE html>
<html lang="id">

<head>
    <title>Jurnal</title>
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
                $typeColor = fn (string $type) => match ($type) {
                    'savings_deposit' => 'bg-green-100 text-green-700 border-green-200',
                    'savings_withdrawal' => 'bg-red-100 text-red-700 border-red-200',
                    default => 'bg-teal-100 text-teal-700 border-teal-200',
                };
            @endphp

            <!-- Header -->
            <div
                class="md:flex justify-between items-center bg-white p-5 rounded-xl shadow-sm border border-gray-100 space-y-2 md:space-y-0">
                <div>
                    <h1 class="font-bold text-2xl text-gray-800 flex items-center gap-2">
                        <i class="fas fa-book text-indigo-600"></i> Jurnal
                    </h1>
                    <p class="text-sm text-gray-500 mt-1">
                        {{ $selectedOffice ? $selectedOffice->code.' — '.$selectedOffice->name : 'Semua kantor' }}
                        · {{ $startDate->format('d M Y') }} s.d. {{ $endDate->format('d M Y') }}
                    </p>
                </div>
                <span class="bg-green-100 text-green-700 border-green-200 text-sm px-4 py-2 rounded-full font-bold border">
                    <i class="fas fa-magic"></i> {{ number_format(count($journals), 0, ',', '.') }} jurnal otomatis dari transaksi
                </span>
            </div>

            <x-ledger-filter :action="route('journalMockup')" :offices="$offices" :selected-office="$selectedOffice"
                :start-date="$startDate" :end-date="$endDate" />

            <!-- Table -->
            <div class="w-full bg-white rounded-xl shadow-md border border-gray-100">
                <div class="p-5 overflow-auto">
                    <table id="myTable" class="w-full text-left">
                        <thead class="bg-gray-100 text-gray-600 text-sm leading-normal">
                            <tr>
                                <th class="p-4 font-bold rounded-tl-lg text-center" width="5%">No</th>
                                <th class="p-4 font-bold">Nomor Jurnal</th>
                                <th class="p-4 font-bold">Tanggal</th>
                                <th class="p-4 font-bold">Kantor</th>
                                <th class="p-4 font-bold">Keterangan</th>
                                <th class="p-4 font-bold text-center">Transaksi</th>
                                <th class="p-4 font-bold text-right">Total</th>
                                <th class="p-4 font-bold text-center rounded-tr-lg" width="8%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-700 text-sm">
                            @php $no = 1; @endphp
                            @foreach ($journals as $item)
                                <tr class="hover:bg-gray-50 transition duration-150">
                                    <td class="p-4 font-medium text-center">{{ $no++ }}</td>
                                    <td class="p-4 font-mono font-semibold text-gray-900 whitespace-nowrap">{{ $item['number'] }}</td>
                                    <td class="p-4 whitespace-nowrap" data-order="{{ $item['time']->format('YmdHis') }}">{{ $item['book_date']->format('d M Y') }}</td>
                                    <td class="p-4 whitespace-nowrap">{{ $item['office_code'] }}</td>
                                    <td class="p-4 space-y-1">
                                        <div>{{ \Illuminate\Support\Str::limit($item['description'], 70) }}</div>
                                        <div class="text-xs text-gray-400 font-mono">Ref {{ $item['reference'] }}</div>
                                    </td>
                                    <td class="p-4 text-center">
                                        <span class="{{ $typeColor($item['transaction_type']) }} text-xs px-3 py-1 rounded-full font-bold border whitespace-nowrap">{{ $item['transaction_type_label'] }}</span>
                                    </td>
                                    <td class="p-4 text-right whitespace-nowrap font-semibold" data-order="{{ $item['total_amount'] }}">Rp {{ number_format($item['total_amount'], 0, ',', '.') }}</td>
                                    <td class="p-4">
                                        <div class="flex justify-center items-center gap-2">
                                            <a href="{{ route('detailJournalMockup', $item['number']) }}"
                                                class="w-10 h-10 flex items-center justify-center bg-blue-500 text-white rounded-lg shadow hover:bg-blue-600 hover:scale-105 transition"
                                                title="Lihat jurnal">
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
    <script src="{{ asset('modal/journalMockup.js') }}"></script>

    @include('sweetalert::alert')
    @include('layout.loading')
</body>

</html>
