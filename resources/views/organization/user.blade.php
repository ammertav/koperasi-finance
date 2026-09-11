<!DOCTYPE html>
<html lang="id">

<head>
    <title>Manajemen Pengguna</title>
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
                        <i class="fas fa-users-cog text-slate-600"></i> Manajemen Pengguna
                    </h1>
                    <p class="text-sm text-gray-500 mt-1">Kelola pengguna, kantor penempatan, dan peran</p>
                </div>
                @if (auth()->user()->hasAccess('organization', 'manage'))
                    <x-button id="addBtn" size="lg" variant="primary" class="bg-slate-700 hover:bg-slate-800 shadow-md"
                        icon="plus">Tambah</x-button>
                @endif
            </div>

            <!-- Table -->
            <div class="w-full bg-white rounded-xl shadow-md border border-gray-100">
                <div class="p-5 overflow-auto">
                    <table id="myTable" class="w-full text-left">
                        <thead class="bg-gray-100 text-gray-600 text-sm leading-normal">
                            <tr>
                                <th class="p-4 font-bold rounded-tl-lg text-center" width="5%">No</th>
                                <th class="p-4 font-bold">Nama</th>
                                <th class="p-4 font-bold">Kantor</th>
                                <th class="p-4 font-bold">Peran</th>
                                <th class="p-4 font-bold text-center">Status</th>
                                <th class="p-4 font-bold text-center rounded-tr-lg" width="15%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-700 text-sm">
                            @php $no = 1; @endphp
                            @foreach ($users as $item)
                                <tr class="hover:bg-gray-50 transition duration-150">
                                    <td class="p-4 font-medium text-center">{{ $no++ }}</td>
                                    <td class="p-4 space-y-1">
                                        <div class="font-bold text-gray-900 text-base">{{ $item->name }}</div>
                                        <div class="text-xs text-gray-400">{{ $item->email }}</div>
                                    </td>
                                    <td class="p-4 space-y-1">
                                        <div class="font-medium text-gray-800">{{ $item->office->name ?? '-' }}</div>
                                        <div class="text-xs text-gray-400">Kode {{ $item->office->code ?? '-' }}</div>
                                    </td>
                                    <td class="p-4">
                                        <div class="flex flex-wrap gap-1">
                                            @foreach ($item->roles as $role)
                                                <span title="{{ $role->name }}"
                                                    class="bg-cyan-100 text-cyan-800 text-xs px-3 py-1 rounded-full font-bold border border-cyan-200 uppercase">
                                                    {{ $role->code }}
                                                </span>
                                            @endforeach
                                        </div>
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
                                    <td class="p-4">
                                        <div class="flex justify-center items-center gap-2">
                                            @if (auth()->user()->hasAccess('organization', 'manage'))
                                                <button
                                                    class="editBtn w-10 h-10 flex items-center justify-center bg-blue-500 text-white rounded-lg shadow hover:bg-blue-600 hover:scale-105 transition"
                                                    data-id="{{ $item->id }}" data-name="{{ $item->name }}"
                                                    data-email="{{ $item->email }}"
                                                    data-office-id="{{ $item->office_id }}"
                                                    data-roles="{{ $item->roles->pluck('id')->toJson() }}"
                                                    data-is-active="{{ $item->is_active ? 1 : 0 }}" title="Edit">
                                                    <i class="fas fa-edit text-lg"></i>
                                                </button>
                                            @else
                                                <span class="text-gray-300">-</span>
                                            @endif
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
    <script src="{{ asset('modal/user.js') }}"></script>

    <!-- Modals -->
    @if (auth()->user()->hasAccess('organization', 'manage'))
        @include('organization.modal.userAdd')
        @include('organization.modal.userEdit')
    @endif

    @include('sweetalert::alert')
    @include('layout.loading')
</body>

</html>
