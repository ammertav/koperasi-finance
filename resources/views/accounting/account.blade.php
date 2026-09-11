<!DOCTYPE html>
<html lang="id">

<head>
    <title>Bagan Akun</title>
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
                        <i class="fas fa-sitemap text-indigo-600"></i> Bagan Akun
                    </h1>
                    <p class="text-sm text-gray-500 mt-1">COA berjenjang yang sama untuk semua kantor (hanya lihat)</p>
                </div>
            </div>

            <!-- Info -->
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-center">
                <p class="text-yellow-800 font-semibold">COA baseline prototype (OQ-38)</p>
                <p class="text-sm text-yellow-600 mt-1">
                    Diisi dari seeder dan akan disusun bersama akuntan koperasi. Akun Rekening Antar Kantor (RAK) dibentuk
                    otomatis untuk setiap pasangan kantor pusat dan cabang.
                </p>
            </div>

            <!-- Table -->
            <div class="w-full bg-white rounded-xl shadow-md border border-gray-100">
                <div class="p-5 overflow-auto">
                    <table id="myTable" class="w-full text-left">
                        <thead class="bg-gray-100 text-gray-600 text-sm leading-normal">
                            <tr>
                                <th class="p-4 font-bold rounded-tl-lg text-center" width="5%">No</th>
                                <th class="p-4 font-bold">Kode</th>
                                <th class="p-4 font-bold">Nama Akun</th>
                                <th class="p-4 font-bold">Tipe</th>
                                <th class="p-4 font-bold">Saldo Normal</th>
                                <th class="p-4 font-bold">Keterangan</th>
                                <th class="p-4 font-bold text-center rounded-tr-lg">Status</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-700 text-sm">
                            @php $no = 1; @endphp
                            @foreach ($accounts as $item)
                                @php
                                    $typeColor = match ($item->type) {
                                        'asset' => 'bg-blue-100 text-blue-700 border-blue-200',
                                        'liability' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
                                        'equity' => 'bg-indigo-100 text-indigo-700 border-indigo-200',
                                        'revenue' => 'bg-green-100 text-green-700 border-green-200',
                                        default => 'bg-red-100 text-red-700 border-red-200',
                                    };
                                    $indent = match ($item->level) {
                                        1 => '',
                                        2 => 'pl-6',
                                        default => 'pl-12',
                                    };
                                @endphp
                                <tr class="hover:bg-gray-50 transition duration-150">
                                    <td class="p-4 font-medium text-center">{{ $no++ }}</td>
                                    <td class="p-4 font-mono whitespace-nowrap">{{ $item->code }}</td>
                                    <td class="p-4">
                                        <div class="{{ $indent }} {{ $item->is_postable ? 'text-gray-700' : 'font-bold text-gray-900 text-base' }}">
                                            {{ $item->name }}
                                        </div>
                                    </td>
                                    <td class="p-4">
                                        <span class="{{ $typeColor }} text-xs px-3 py-1 rounded-full font-bold border">
                                            {{ $item->type_label }}
                                        </span>
                                    </td>
                                    <td class="p-4">{{ \App\Models\Account::NORMAL_BALANCES[$item->normal_balance] }}</td>
                                    <td class="p-4 text-xs space-x-1 whitespace-nowrap">
                                        @if (! $item->is_postable)
                                            <span class="text-gray-400">Akun induk</span>
                                        @endif
                                        @if ($item->isInterOffice())
                                            <span
                                                class="bg-blue-100 text-blue-700 border-blue-200 text-xs px-3 py-1 rounded-full font-bold border">RAK</span>
                                        @endif
                                        @if ($item->is_head_office_only)
                                            <span
                                                class="bg-cyan-100 text-cyan-800 border-cyan-200 text-xs px-3 py-1 rounded-full font-bold border">Hanya pusat</span>
                                        @endif
                                    </td>
                                    <td class="p-4 text-center">
                                        @if ($item->is_active)
                                            <span
                                                class="text-xs font-bold text-green-700 bg-green-50 px-2 py-1 rounded border border-green-200">
                                                <i class="fas fa-check-circle text-green-600"></i> Aktif
                                            </span>
                                        @else
                                            <span
                                                class="text-xs font-bold text-red-600 bg-red-50 px-2 py-1 rounded border border-red-200">
                                                <i class="fas fa-times-circle"></i> Nonaktif
                                            </span>
                                        @endif
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
    <script src="{{ asset('modal/account.js') }}"></script>

    @include('sweetalert::alert')
    @include('layout.loading')
</body>

</html>
