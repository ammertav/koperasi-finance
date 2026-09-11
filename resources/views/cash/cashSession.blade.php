<!DOCTYPE html>
<html lang="id">

<head>
    <title>Sesi Kas Teller</title>
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
            @endphp

            <!-- Header -->
            <div
                class="md:flex justify-between items-center bg-white p-5 rounded-xl shadow-sm border border-gray-100 space-y-2 md:space-y-0">
                <div>
                    <h1 class="font-bold text-2xl text-gray-800 flex items-center gap-2">
                        <i class="fas fa-cash-register text-teal-600"></i> Sesi Kas Teller
                    </h1>
                    <p class="text-sm text-gray-500 mt-1">{{ $office->code }} — {{ $office->name }} · Tanggal buku {{ $office->book_date->format('d M Y') }}</p>
                </div>
                <div class="flex flex-wrap items-end gap-3">
                    @if ($offices->count() > 1)
                        <form method="GET" action="{{ route('cashSession') }}" data-page-loading>
                            <select name="office_id" onchange="this.form.submit()"
                                class="rounded-lg border-gray-300 shadow-sm p-2.5 border focus:ring-2 focus:ring-teal-500">
                                @foreach ($offices as $option)
                                    <option value="{{ $option->id }}" @selected($option->id === $office->id)>{{ $option->code }} — {{ $option->name }}</option>
                                @endforeach
                            </select>
                        </form>
                    @endif
                    <div class="bg-teal-50 border border-teal-100 rounded-lg px-4 py-2 text-right">
                        <p class="text-xs text-teal-600 uppercase font-semibold tracking-wide">Saldo Brankas</p>
                        <p class="text-lg font-bold text-teal-700">{{ $rupiah($vaultBalance) }}</p>
                    </div>
                </div>
            </div>

            @if ($canOpen)
                <!-- Open Session Form -->
                <form id="openSessionForm" method="POST" action="{{ route('postCashSession') }}"
                    class="bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden">
                    @csrf
                    <div class="bg-teal-600 p-6 md:flex justify-between items-center space-y-3 md:space-y-0">
                        <div>
                            <h2 class="text-2xl font-bold text-white flex items-center gap-2"><i class="fas fa-door-open"></i> Buka Sesi Kas</h2>
                            <p class="text-teal-100 text-sm mt-1">Hitung uang yang diterima dari brankas per pecahan. Saldo awal otomatis dikonfirmasi pemegang brankas.</p>
                        </div>
                        <div class="bg-teal-700/50 rounded-lg px-4 py-2 text-right">
                            <p class="text-teal-100 text-xs uppercase tracking-wide">Total Saldo Awal</p>
                            <p class="text-white text-2xl font-bold" id="denominationTotal">Rp 0</p>
                        </div>
                    </div>
                    <div class="p-8">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                            @foreach (['paper' => 'Uang Kertas', 'coin' => 'Uang Logam'] as $type => $label)
                                <div>
                                    <h3 class="text-sm font-bold text-teal-600 uppercase tracking-wider mb-4 border-b pb-2">
                                        <i class="fas fa-{{ $type === 'paper' ? 'money-bill' : 'coins' }}"></i> {{ $label }}
                                    </h3>
                                    <table class="w-full text-sm">
                                        <thead class="text-gray-500 text-xs uppercase">
                                            <tr>
                                                <th class="text-left pb-2">Pecahan</th>
                                                <th class="text-center pb-2">Lembar / Keping</th>
                                                <th class="text-right pb-2">Jumlah</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($denominations as $key => $denomination)
                                                @continue($denomination['type'] !== $type)
                                                <tr class="border-t border-gray-100">
                                                    <td class="py-2 font-semibold text-gray-800">{{ $rupiah($denomination['value']) }}</td>
                                                    <td class="py-2 text-center">
                                                        <input type="number" min="0" name="denominations[{{ $key }}]"
                                                            value="{{ old('denominations.' . $key, $suggestedDenominations[$key] ?? 0) }}"
                                                            data-value="{{ $denomination['value'] }}"
                                                            class="denomination w-28 rounded-lg border-gray-300 shadow-sm p-2 border text-center focus:ring-2 focus:ring-teal-500">
                                                    </td>
                                                    <td class="py-2 text-right font-semibold text-gray-700 subtotal">Rp 0</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endforeach
                        </div>

                        <div class="flex items-center justify-end gap-4 pt-6 mt-6 border-t border-gray-100">
                            <button type="submit"
                                class="px-8 py-3 bg-teal-600 text-white rounded-lg shadow-lg font-bold hover:bg-teal-700 transition flex items-center gap-2">
                                <i class="fas fa-door-open"></i> Buka Sesi Kas
                            </button>
                        </div>
                    </div>
                </form>
            @endif

            @forelse ($sessions as $cashSession)
                <!-- Session -->
                <div class="bg-white rounded-xl shadow-md border border-gray-100">
                    <div class="p-5 border-b border-gray-100 md:flex justify-between items-center space-y-2 md:space-y-0">
                        <div>
                            <h2 class="font-bold text-lg text-gray-800 flex items-center gap-2">
                                <i class="fas fa-user-tie text-teal-600"></i> {{ $cashSession['teller_name'] }}
                                <span class="bg-green-100 text-green-700 border-green-200 text-xs px-3 py-1 rounded-full font-bold border">Sesi Terbuka</span>
                            </h2>
                            <p class="text-xs text-gray-500 mt-1 font-mono">{{ $cashSession['number'] }} · dibuka {{ $cashSession['opened_at']->format('H:i') }} · saldo awal {{ $cashSession['transfer_number'] }} dikonfirmasi {{ $cashSession['vault_custodian_name'] }}</p>
                        </div>
                        @if ($cashSession['user_id'] === auth()->id())
                            <div class="flex flex-wrap gap-2">
                                <x-button :href="route('cashTransfer')" variant="secondary" icon="exchange-alt">Perpindahan Kas</x-button>
                                <x-button :href="route('createSavingsTransaction')" variant="primary" class="bg-emerald-600 hover:bg-emerald-700" icon="plus">Setor / Tarik</x-button>
                            </div>
                        @endif
                    </div>

                    <!-- KPI Cards -->
                    <div class="grid grid-cols-2 xl:grid-cols-4 gap-4 p-5">
                        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
                            <p class="text-xs text-gray-500 uppercase tracking-wide">Saldo Awal</p>
                            <h3 class="text-2xl font-bold text-gray-800 mt-1">{{ $rupiah($cashSession['summary']['opening_balance']) }}</h3>
                            <p class="text-xs text-gray-400 mt-2">Dari brankas</p>
                        </div>
                        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
                            <p class="text-xs text-gray-500 uppercase tracking-wide">Kas Masuk</p>
                            <h3 class="text-2xl font-bold text-emerald-700 mt-1">{{ $rupiah($cashSession['summary']['cash_in']) }}</h3>
                            <p class="text-xs text-gray-400 mt-2">Setoran dan tambahan brankas</p>
                        </div>
                        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
                            <p class="text-xs text-gray-500 uppercase tracking-wide">Kas Keluar</p>
                            <h3 class="text-2xl font-bold text-red-600 mt-1">{{ $rupiah($cashSession['summary']['cash_out']) }}</h3>
                            <p class="text-xs text-gray-400 mt-2">Penarikan dan setor ke brankas</p>
                        </div>
                        <div class="bg-teal-50 p-5 rounded-xl shadow-sm border border-teal-100">
                            <p class="text-xs text-teal-600 uppercase font-semibold tracking-wide">Saldo Sistem</p>
                            <h3 class="text-3xl font-bold text-teal-700 mt-2">{{ $rupiah($cashSession['summary']['balance']) }}</h3>
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="px-5 pb-5 overflow-auto">
                        <table class="w-full text-left">
                            <thead class="bg-gray-100 text-gray-600 text-sm leading-normal">
                                <tr>
                                    <th class="p-4 font-bold rounded-tl-lg text-center" width="5%">No</th>
                                    <th class="p-4 font-bold">Jam</th>
                                    <th class="p-4 font-bold">Referensi</th>
                                    <th class="p-4 font-bold">Keterangan</th>
                                    <th class="p-4 font-bold text-right">Masuk</th>
                                    <th class="p-4 font-bold text-right">Keluar</th>
                                    <th class="p-4 font-bold text-right rounded-tr-lg">Saldo</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-700 text-sm">
                                <tr class="bg-gray-50">
                                    <td class="p-4" colspan="6"><span class="font-semibold text-gray-600">Saldo awal sesi</span></td>
                                    <td class="p-4 text-right font-bold whitespace-nowrap">{{ $rupiah($cashSession['summary']['opening_balance']) }}</td>
                                </tr>
                                @php $no = 1; @endphp
                                @forelse ($cashSession['rows'] as $row)
                                    <tr class="hover:bg-gray-50 transition duration-150">
                                        <td class="p-4 font-medium text-center">{{ $no++ }}</td>
                                        <td class="p-4 whitespace-nowrap">{{ $row['time']->format('H:i') }}</td>
                                        <td class="p-4 whitespace-nowrap font-mono text-xs">
                                            @if ($row['transaction_id'])
                                                <a href="{{ route('detailSavingsTransaction', $row['transaction_id']) }}" class="font-semibold text-teal-700 hover:underline">{{ $row['reference'] }}</a>
                                            @else
                                                {{ $row['reference'] }}
                                            @endif
                                        </td>
                                        <td class="p-4">{{ $row['description'] }}</td>
                                        <td class="p-4 text-right whitespace-nowrap text-emerald-700">{{ $row['cash_in'] ? $rupiah($row['cash_in']) : '-' }}</td>
                                        <td class="p-4 text-right whitespace-nowrap text-red-600">{{ $row['cash_out'] ? $rupiah($row['cash_out']) : '-' }}</td>
                                        <td class="p-4 text-right whitespace-nowrap font-semibold">{{ $rupiah($row['balance']) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="p-4 text-center text-gray-400" colspan="7">Belum ada transaksi di sesi ini</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @empty
                @unless ($canOpen)
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-center">
                        <p class="text-yellow-800 font-semibold">Belum ada sesi kas teller yang dibuka hari ini</p>
                        <p class="text-sm text-yellow-600 mt-1">Sesi kas dibuka teller setiap pagi dengan saldo awal dari brankas cabang.</p>
                    </div>
                @endunless
            @endforelse

        </div>
    </main>

    <!-- SCRIPTS -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="{{ asset('modal/cashSession.js') }}"></script>

    @include('sweetalert::alert')
    @include('layout.loading')
</body>

</html>
