<!DOCTYPE html>
<html lang="id">

<head>
    <title>Peran & Hak Akses</title>
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
                        <i class="fas fa-user-shield text-slate-600"></i> Peran & Hak Akses
                    </h1>
                    <p class="text-sm text-gray-500 mt-1">Matriks akses baseline PRD per modul (hanya lihat)</p>
                </div>
            </div>

            @php
                $accessColors = [
                    'manage' => 'bg-indigo-100 text-indigo-700 border-indigo-200',
                    'operate' => 'bg-blue-100 text-blue-700 border-blue-200',
                    'approve' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
                    'view' => 'bg-gray-100 text-gray-700 border-gray-200',
                ];
                $accessLabels = [
                    'manage' => 'Kelola penuh',
                    'operate' => 'Input/operasional',
                    'approve' => 'Menyetujui',
                    'view' => 'Lihat',
                ];
            @endphp

            <!-- Legend -->
            <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 flex flex-wrap gap-3 items-center">
                @foreach (\App\Models\RolePermission::ACCESS as $access => $code)
                    <span class="flex items-center gap-2 text-sm text-gray-600">
                        <span class="{{ $accessColors[$access] }} text-xs px-3 py-1 rounded-full font-bold border">{{ $code }}</span>
                        {{ $accessLabels[$access] }}
                    </span>
                @endforeach
                <span class="flex items-center gap-2 text-sm text-gray-600">
                    <span class="text-gray-300 font-bold px-2">-</span> Tidak ada akses
                </span>
            </div>

            <!-- Table -->
            <div class="w-full bg-white rounded-xl shadow-md border border-gray-100">
                <div class="p-5 overflow-auto">
                    <table id="myTable" class="w-full text-left">
                        <thead class="bg-gray-100 text-gray-600 text-sm leading-normal">
                            <tr>
                                <th class="p-4 font-bold rounded-tl-lg text-center" width="5%">No</th>
                                <th class="p-4 font-bold">Peran</th>
                                <th class="p-4 font-bold">Lingkup</th>
                                @foreach ($modules as $moduleLabel)
                                    <th class="p-4 font-bold text-center whitespace-nowrap">{{ \Illuminate\Support\Str::before($moduleLabel, ' ') }}</th>
                                @endforeach
                                <th class="p-4 font-bold text-center rounded-tr-lg">Pengguna</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-700 text-sm">
                            @php $no = 1; @endphp
                            @foreach ($roles as $item)
                                <tr class="hover:bg-gray-50 transition duration-150">
                                    <td class="p-4 font-medium text-center">{{ $no++ }}</td>
                                    <td class="p-4 space-y-1">
                                        <div class="font-bold text-gray-900 text-base">{{ $item->name }}</div>
                                        <div class="text-xs text-gray-400">Kode {{ $item->code }}</div>
                                    </td>
                                    <td class="p-4 space-y-1 whitespace-nowrap">
                                        <span
                                            class="bg-cyan-100 text-cyan-800 text-xs px-3 py-1 rounded-full font-bold border border-cyan-200 uppercase">
                                            {{ \App\Models\Role::LOCATIONS[$item->location] }}
                                        </span>
                                        <div class="text-xs text-gray-400 mt-1">{{ \App\Models\Role::DATA_SCOPES[$item->data_scope] }}</div>
                                    </td>
                                    @foreach ($modules as $module => $moduleLabel)
                                        @php $permission = $item->permissions->firstWhere('module', $module); @endphp
                                        <td class="p-4 text-center" title="{{ $moduleLabel }}">
                                            @if ($permission)
                                                <span class="{{ $accessColors[$permission->access] }} text-xs px-3 py-1 rounded-full font-bold border">
                                                    {{ \App\Models\RolePermission::ACCESS[$permission->access] }}
                                                </span>
                                            @else
                                                <span class="text-gray-300 font-bold">-</span>
                                            @endif
                                        </td>
                                    @endforeach
                                    <td class="p-4 text-center font-medium">{{ $item->users_count }}</td>
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
    <script src="{{ asset('modal/role.js') }}"></script>

    @include('sweetalert::alert')
    @include('layout.loading')
</body>

</html>
