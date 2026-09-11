<!DOCTYPE html>
<html lang="id">

<head>
    <title>Data Anggota</title>
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
                $statusColor = fn (string $status) => match ($status) {
                    'active' => 'bg-green-100 text-green-700 border-green-200',
                    'pending_approval' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
                    'approved' => 'bg-blue-100 text-blue-700 border-blue-200',
                    'rejected' => 'bg-red-100 text-red-700 border-red-200',
                    default => 'bg-gray-100 text-gray-700 border-gray-200',
                };
            @endphp

            <!-- Header -->
            <div
                class="md:flex justify-between items-center bg-white p-5 rounded-xl shadow-sm border border-gray-100 space-y-2 md:space-y-0">
                <div>
                    <h1 class="font-bold text-2xl text-gray-800 flex items-center gap-2">
                        <i class="fas fa-id-card text-indigo-600"></i> Data Anggota
                    </h1>
                    <p class="text-sm text-gray-500 mt-1">
                        {{ $showOffice ? 'Data anggota tunggal seluruh kantor' : 'Anggota ' . auth()->user()->office->name }}
                    </p>
                </div>
                @if (auth()->user()->hasAccess('member', 'operate'))
                    <x-button :href="route('createMember')" size="lg" variant="primary" class="shadow-md" icon="user-plus">
                        Registrasi Anggota
                    </x-button>
                @endif
            </div>

            <!-- KPI Cards -->
            <div class="grid grid-cols-2 xl:grid-cols-4 gap-4">
                <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Anggota Aktif</p>
                    <h2 class="text-2xl font-bold text-gray-800 mt-1">{{ number_format($summary['active'], 0, ',', '.') }}</h2>
                    <p class="text-xs text-emerald-600 mt-2">Nomor anggota sudah terbit</p>
                </div>
                <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Menunggu Persetujuan</p>
                    <h2 class="text-2xl font-bold text-gray-800 mt-1">{{ $summary['pending_approval'] }}</h2>
                    <p class="text-xs text-yellow-600 mt-2">Perlu persetujuan kepala cabang</p>
                </div>
                <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Menunggu Setoran Pokok</p>
                    <h2 class="text-2xl font-bold text-gray-800 mt-1">{{ $summary['approved'] }}</h2>
                    <p class="text-xs text-blue-600 mt-2">Disetujui, belum setor di teller</p>
                </div>
                <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Punya Pinjaman Aktif</p>
                    <h2 class="text-2xl font-bold text-gray-800 mt-1">{{ number_format($summary['with_loan'], 0, ',', '.') }}</h2>
                    <p class="text-xs text-purple-600 mt-2">
                        {{ $summary['active'] > 0 ? number_format($summary['with_loan'] / $summary['active'] * 100, 1, ',', '.') : 0 }}% dari anggota aktif
                    </p>
                </div>
            </div>

            <!-- Table -->
            <div class="w-full bg-white rounded-xl shadow-md border border-gray-100">
                <div class="p-5 border-b border-gray-100">
                    <form method="GET" action="{{ route('member') }}" data-page-loading class="flex flex-wrap items-end gap-3">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Status</label>
                            <select name="status" onchange="this.form.submit()"
                                class="rounded-lg border-gray-300 shadow-sm p-2.5 border focus:ring-2 focus:ring-indigo-500">
                                <option value="">Semua status</option>
                                @foreach ($statuses as $code => $label)
                                    <option value="{{ $code }}" @selected($status === $code)>{{ $label }}</option>
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
                                <th class="p-4 font-bold">Anggota</th>
                                <th class="p-4 font-bold">No. Anggota</th>
                                @if ($showOffice)
                                    <th class="p-4 font-bold">Kantor</th>
                                @endif
                                <th class="p-4 font-bold">Pekerjaan</th>
                                <th class="p-4 font-bold">Tgl Daftar</th>
                                <th class="p-4 font-bold text-center">Status</th>
                                <th class="p-4 font-bold text-center rounded-tr-lg" width="10%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-700 text-sm">
                            @php $no = 1; @endphp
                            @foreach ($members as $item)
                                <tr class="hover:bg-gray-50 transition duration-150">
                                    <td class="p-4 font-medium text-center">{{ $no++ }}</td>
                                    <td class="p-4 space-y-1">
                                        <div class="font-bold text-gray-900 text-base">{{ $item['name'] }}</div>
                                        <div class="text-xs text-gray-400 font-mono">NIK {{ $item['nik_masked'] }}</div>
                                    </td>
                                    <td class="p-4 font-mono">{{ $item['number'] ?? '-' }}</td>
                                    @if ($showOffice)
                                        <td class="p-4 space-y-1">
                                            <div class="font-medium text-gray-800">{{ $item['office_name'] }}</div>
                                            <div class="text-xs text-gray-400">Kode {{ $item['office_code'] }}</div>
                                        </td>
                                    @endif
                                    <td class="p-4">{{ $item['occupation'] }}</td>
                                    <td class="p-4 whitespace-nowrap" data-order="{{ $item['registration_date']->toDateString() }}">
                                        {{ $item['registration_date']->format('d M Y') }}
                                    </td>
                                    <td class="p-4 text-center">
                                        <span class="{{ $statusColor($item['status']) }} text-xs px-3 py-1 rounded-full font-bold border whitespace-nowrap">
                                            {{ $item['status_label'] }}
                                        </span>
                                    </td>
                                    <td class="p-4">
                                        <div class="flex justify-center items-center gap-2">
                                            <a href="{{ route('detailMember', $item['id']) }}"
                                                class="w-10 h-10 flex items-center justify-center bg-blue-500 text-white rounded-lg shadow hover:bg-blue-600 hover:scale-105 transition"
                                                title="Lihat profil">
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
    <script src="{{ asset('modal/member.js') }}"></script>

    @include('sweetalert::alert')
    @include('layout.loading')
</body>

</html>
