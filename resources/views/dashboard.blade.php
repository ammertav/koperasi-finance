<!DOCTYPE html>
<html lang="id">

<head>
    <title>Dashboard</title>
    @include('layout.head')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
                    <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                        <i class="fa-solid fa-chart-line text-indigo-600"></i>
                        Dashboard
                    </h1>
                    <p class="text-sm text-gray-500 mt-1">
                        Ringkasan organisasi dan hak akses Anda
                    </p>
                </div>
                <div class="text-sm text-gray-500">
                    {{ now()->translatedFormat('l, d F Y') }}
                </div>
            </div>

            <!-- KPI Cards -->
            <div class="grid grid-cols-2 sm:grid-cols-2 xl:grid-cols-4 gap-4">

                <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
                    <p class="text-xs text-gray-500 uppercase tracking-wide">
                        Kantor Aktif
                    </p>
                    <h2 class="text-2xl font-bold text-gray-800 mt-1">
                        {{ $activeOffices }}
                    </h2>
                    <p class="text-xs text-emerald-600 mt-2 flex items-center gap-1">
                        <i class="fa-solid fa-building"></i>
                        dari {{ $totalOffices }} kantor
                    </p>
                </div>

                <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
                    <p class="text-xs text-gray-500 uppercase tracking-wide">
                        Pengguna Aktif
                    </p>
                    <h2 class="text-2xl font-bold text-gray-800 mt-1">
                        {{ $activeUsers }}
                    </h2>
                    <p class="text-xs text-emerald-600 mt-2 flex items-center gap-1">
                        <i class="fa-solid fa-users"></i>
                        dari {{ $totalUsers }} pengguna
                    </p>
                </div>

                <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
                    <p class="text-xs text-gray-500 uppercase tracking-wide">
                        Tanggal Buku
                    </p>
                    <h2 class="text-2xl font-bold text-gray-800 mt-1">
                        {{ $user->office->book_date->format('d M Y') }}
                    </h2>
                    <p class="text-xs text-emerald-600 mt-2 flex items-center gap-1">
                        <i class="fa-solid fa-calendar-day"></i>
                        {{ $user->office->name }}
                    </p>
                </div>

                <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
                    <p class="text-xs text-gray-500 uppercase tracking-wide">
                        Peran Anda
                    </p>
                    <h2 class="text-2xl font-bold text-gray-800 mt-1">
                        {{ $user->roles->pluck('code')->implode(' / ') }}
                    </h2>
                    <p class="text-xs text-emerald-600 mt-2 flex items-center gap-1">
                        <i class="fa-solid fa-eye"></i>
                        {{ $user->canAccessAllOffices() ? 'Melihat semua kantor' : 'Melihat kantor sendiri' }}
                    </p>
                </div>

            </div>

            <!-- Access Card -->
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="font-bold text-gray-800 text-lg">
                            Hak Akses per Modul
                        </h3>
                        <p class="text-xs text-gray-500 mt-1">
                            {{ $user->roles->pluck('name')->implode(', ') }} — P kelola, O operasional, A setujui, L lihat
                        </p>
                    </div>
                </div>

                @php
                    $accessColors = [
                        'manage' => 'bg-indigo-100 text-indigo-700 border-indigo-200',
                        'operate' => 'bg-blue-100 text-blue-700 border-blue-200',
                        'approve' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
                        'view' => 'bg-gray-100 text-gray-700 border-gray-200',
                    ];
                @endphp

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                    @foreach ($accessByModule as $module)
                        <div class="p-4 rounded-lg border border-gray-100 {{ $module['access'] ? 'bg-white' : 'bg-gray-50 opacity-80' }}">
                            <p class="text-xs text-gray-500 uppercase font-bold">{{ $module['label'] }}</p>
                            <div class="flex gap-1 mt-2">
                                @forelse ($module['access'] as $access)
                                    <span class="{{ $accessColors[$access] }} text-xs px-3 py-1 rounded-full font-bold border">
                                        {{ \App\Models\RolePermission::ACCESS[$access] }}
                                    </span>
                                @empty
                                    <span class="text-xs text-gray-400">Tidak ada akses</span>
                                @endforelse
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>
    </main>

    @include('sweetalert::alert')

</body>

</html>
