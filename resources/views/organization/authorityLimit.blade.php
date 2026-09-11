<!DOCTYPE html>
<html lang="id">

<head>
    <title>Matriks Wewenang</title>
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
                        <i class="fas fa-balance-scale text-slate-600"></i> Matriks Wewenang
                    </h1>
                    <p class="text-sm text-gray-500 mt-1">Batas nominal persetujuan per peran per jenis transaksi (hanya lihat)</p>
                </div>
            </div>

            <!-- Info -->
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-center">
                <p class="text-yellow-800 font-semibold">Nilai berasal dari asumsi prototype (OQ-14)</p>
                <p class="text-sm text-yellow-600 mt-1">
                    Diisi dari seeder. Transaksi di atas batas peran harus disetujui peran yang lebih tinggi, dan pembuat tidak boleh sama dengan penyetuju.
                </p>
            </div>

            <!-- Table -->
            <div class="w-full bg-white rounded-xl shadow-md border border-gray-100">
                <div class="p-5 overflow-auto">
                    <table id="myTable" class="w-full text-left">
                        <thead class="bg-gray-100 text-gray-600 text-sm leading-normal">
                            <tr>
                                <th class="p-4 font-bold rounded-tl-lg text-center" width="5%">No</th>
                                <th class="p-4 font-bold">Jenis Transaksi</th>
                                @foreach ($roles as $role)
                                    <th class="p-4 font-bold text-right whitespace-nowrap {{ $loop->last ? 'rounded-tr-lg' : '' }}"
                                        title="{{ $role->name }}">{{ $role->code }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="text-gray-700 text-sm">
                            @php $no = 1; @endphp
                            @foreach ($transactionTypes as $type => $typeLabel)
                                <tr class="hover:bg-gray-50 transition duration-150">
                                    <td class="p-4 font-medium text-center">{{ $no++ }}</td>
                                    <td class="p-4 font-bold text-gray-900">{{ $typeLabel }}</td>
                                    @foreach ($roles as $role)
                                        @php $limit = $role->authorityLimits->firstWhere('transaction_type', $type); @endphp
                                        <td class="p-4 text-right whitespace-nowrap">
                                            @if (! $limit)
                                                <span class="text-gray-300 font-bold">-</span>
                                            @elseif ($limit->is_unlimited)
                                                <span
                                                    class="bg-indigo-100 text-indigo-700 text-xs px-3 py-1 rounded-full font-bold border border-indigo-200">
                                                    Tanpa batas
                                                </span>
                                            @else
                                                <span class="font-mono text-slate-600">
                                                    Rp {{ number_format($limit->max_amount, 0, ',', '.') }}
                                                </span>
                                            @endif
                                        </td>
                                    @endforeach
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
    <script src="{{ asset('modal/authorityLimit.js') }}"></script>

    @include('sweetalert::alert')
    @include('layout.loading')
</body>

</html>
