<!DOCTYPE html>
<html lang="id">

<head>
    <title>Produk Simpanan</title>
    @include('layout.head')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-gray-50 font-sans">
    @include('layout.sidebar')

    <main class="md:ml-64 xl:ml-72 2xl:ml-72">
        @include('layout.navbar')
        <div class="p-6 space-y-6">

            @php
                $rupiah = fn (int $amount) => 'Rp ' . number_format($amount, 0, ',', '.');
                $icons = ['SP' => 'landmark', 'SW' => 'calendar-check', 'SS' => 'wallet'];
            @endphp

            <!-- Header -->
            <div
                class="md:flex justify-between items-center bg-white p-5 rounded-xl shadow-sm border border-gray-100 space-y-2 md:space-y-0">
                <div>
                    <h1 class="font-bold text-2xl text-gray-800 flex items-center gap-2">
                        <i class="fas fa-piggy-bank text-emerald-600"></i> Produk Simpanan
                    </h1>
                    <p class="text-sm text-gray-500 mt-1">Konfigurasi produk berlaku sama untuk semua kantor</p>
                </div>
                <span class="bg-gray-100 text-gray-700 border-gray-200 text-sm px-4 py-2 rounded-full font-bold border">
                    <i class="fas fa-lock"></i> Hanya lihat
                </span>
            </div>

            <!-- Product Cards -->
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
                @foreach ($products as $product)
                    <div class="bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden flex flex-col">
                        <div class="bg-emerald-600 p-6 flex items-center justify-between">
                            <div>
                                <p class="text-emerald-100 text-xs uppercase tracking-wide font-semibold">{{ $product['code'] }} · {{ $product['classification'] }}</p>
                                <h2 class="text-2xl font-bold text-white mt-1">{{ $product['name'] }}</h2>
                            </div>
                            <i class="fas fa-{{ $icons[$product['code']] }} text-4xl text-emerald-300 opacity-50"></i>
                        </div>
                        <div class="grid grid-cols-2 border-b border-gray-100">
                            <div class="p-5 border-r border-gray-100">
                                <p class="text-xs text-gray-500 uppercase tracking-wide">Rekening</p>
                                <h3 class="text-2xl font-bold text-gray-800 mt-1">{{ number_format($product['account_count'], 0, ',', '.') }}</h3>
                            </div>
                            <div class="p-5">
                                <p class="text-xs text-gray-500 uppercase tracking-wide">Total Saldo</p>
                                <h3 class="text-lg font-bold text-gray-800 mt-1">{{ $rupiah($product['total_balance']) }}</h3>
                            </div>
                        </div>
                        <dl class="p-6 space-y-3 text-sm flex-grow">
                            <div class="flex justify-between gap-4"><dt class="text-gray-500">Setoran</dt><dd class="font-semibold text-gray-800 text-right">{{ $product['deposit_rule'] }}</dd></div>
                            <div class="flex justify-between gap-4"><dt class="text-gray-500">Saldo Minimal</dt><dd class="font-semibold text-gray-800 text-right">{{ $product['minimum_balance'] }}</dd></div>
                            <div class="flex justify-between gap-4"><dt class="text-gray-500">Biaya Administrasi</dt><dd class="font-semibold text-gray-800 text-right">{{ $product['admin_fee'] }}</dd></div>
                            <div class="flex justify-between gap-4"><dt class="text-gray-500">Jasa</dt><dd class="font-semibold text-gray-800 text-right">{{ $product['interest'] }}</dd></div>
                            <div class="flex justify-between gap-4"><dt class="text-gray-500">Pembukaan</dt><dd class="font-semibold text-gray-800 text-right">{{ $product['opening_rule'] }}</dd></div>
                            <div class="flex justify-between gap-4"><dt class="text-gray-500">Akun Jurnal</dt><dd class="font-mono font-semibold text-gray-800">{{ $product['account_code'] }}</dd></div>
                        </dl>
                        <div class="px-6 pb-6">
                            <div class="{{ $product['is_withdrawable'] ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-yellow-50 border-yellow-200 text-yellow-800' }} border rounded-lg p-3 text-sm flex items-start gap-2">
                                <i class="fas fa-{{ $product['is_withdrawable'] ? 'check-circle' : 'ban' }} mt-0.5"></i>
                                <span>{{ $product['withdrawal_rule'] }}</span>
                            </div>
                            <a href="{{ route('savingsAccount', ['product' => $product['code']]) }}"
                                class="mt-4 block text-center text-sm font-bold text-emerald-700 hover:underline">Lihat rekening <i class="fas fa-arrow-right"></i></a>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-center">
                <p class="text-yellow-800 font-semibold">Simpanan berjangka, perhitungan jasa, dan pajak belum ditampilkan di prototype</p>
                <p class="text-sm text-yellow-600 mt-1">Tarif, saldo minimal, dan batas penarikan di atas adalah asumsi baseline yang akan dikonfirmasi saat discovery.</p>
            </div>

        </div>
    </main>

    <!-- SCRIPTS -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    @include('sweetalert::alert')
    @include('layout.loading')
</body>

</html>
