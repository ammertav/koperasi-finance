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

            <!-- Header -->
            <div
                class="md:flex justify-between items-center bg-white p-5 rounded-xl shadow-sm border border-gray-100 space-y-2 md:space-y-0">
                <div>
                    <h1 class="font-bold text-2xl text-gray-800 flex items-center gap-2">
                        <i class="fas fa-book text-indigo-600"></i> Jurnal
                    </h1>
                    <p class="text-sm text-gray-500 mt-1">
                        Jurnal terbentuk otomatis dari transaksi. Koreksi dilakukan dengan jurnal pembalik, bukan diubah.
                    </p>
                </div>
            </div>

            <x-ledger-filter :action="route('journal')" :offices="$offices" :selected-office="$selectedOffice"
                :start-date="$startDate" :end-date="$endDate" />

            <!-- Table -->
            <div class="w-full bg-white rounded-xl shadow-md border border-gray-100">
                <div class="p-5 overflow-auto">
                    <table id="myTable" class="w-full text-left">
                        <thead class="bg-gray-100 text-gray-600 text-sm leading-normal">
                            <tr>
                                <th class="p-4 font-bold rounded-tl-lg text-center" width="5%">No</th>
                                <th class="p-4 font-bold">Nomor</th>
                                <th class="p-4 font-bold">Kantor</th>
                                <th class="p-4 font-bold">Transaksi</th>
                                <th class="p-4 font-bold text-right">Total</th>
                                <th class="p-4 font-bold text-center">Status</th>
                                <th class="p-4 font-bold text-center rounded-tr-lg" width="10%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-700 text-sm">
                            @php $no = 1; @endphp
                            @foreach ($journals as $item)
                                @php
                                    [$statusLabel, $statusColor] = match (true) {
                                        $item->reversal_of_id !== null => ['Pembalik', 'bg-yellow-100 text-yellow-700 border-yellow-200'],
                                        $item->reversal !== null => ['Dibalik', 'bg-red-100 text-red-700 border-red-200'],
                                        default => [$item->status_label, 'bg-green-100 text-green-700 border-green-200'],
                                    };
                                @endphp
                                <tr class="hover:bg-gray-50 transition duration-150">
                                    <td class="p-4 font-medium text-center">{{ $no++ }}</td>
                                    <td class="p-4 space-y-1 whitespace-nowrap">
                                        <div class="font-bold text-gray-900 text-base font-mono">{{ $item->number }}</div>
                                        <div class="text-xs text-gray-400">{{ $item->book_date->format('d M Y') }}</div>
                                    </td>
                                    <td class="p-4 whitespace-nowrap">{{ $item->office->code }} — {{ $item->office->name }}</td>
                                    <td class="p-4 space-y-1">
                                        <div class="flex items-center gap-2">
                                            <span class="font-semibold text-gray-800">{{ $item->transaction_type_label }}</span>
                                            @if ($item->inter_office_group)
                                                <span
                                                    class="bg-blue-100 text-blue-700 border-blue-200 text-xs px-2 py-0.5 rounded-full font-bold border">RAK</span>
                                            @endif
                                        </div>
                                        <div class="text-xs text-gray-400">{{ \Illuminate\Support\Str::limit($item->description, 60) }}</div>
                                    </td>
                                    <td class="p-4 text-right whitespace-nowrap font-medium">
                                        Rp {{ number_format($item->total_amount, 0, ',', '.') }}
                                    </td>
                                    <td class="p-4 text-center">
                                        <span class="{{ $statusColor }} text-xs px-3 py-1 rounded-full font-bold border">{{ $statusLabel }}</span>
                                    </td>
                                    <td class="p-4">
                                        <div class="flex justify-center items-center gap-2">
                                            <a href="{{ route('detailJournal', $item->id) }}"
                                                class="w-10 h-10 flex items-center justify-center bg-blue-500 text-white rounded-lg shadow hover:bg-blue-600 hover:scale-105 transition"
                                                title="Detail">
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
    <script src="{{ asset('modal/journal.js') }}"></script>

    @include('sweetalert::alert')
    @include('layout.loading')
</body>

</html>
