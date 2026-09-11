<!DOCTYPE html>
<html lang="id">

<head>
    <title>Brankas & Perpindahan Kas</title>
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
                $statusColor = fn (string $status) => match ($status) {
                    'confirmed' => 'bg-green-100 text-green-700 border-green-200',
                    'pending_confirmation' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
                    'rejected' => 'bg-red-100 text-red-700 border-red-200',
                    default => 'bg-gray-100 text-gray-700 border-gray-200',
                };
                $pendingCount = collect($transfers)->where('status', 'pending_confirmation')->count();
            @endphp

            <!-- Header -->
            <div
                class="md:flex justify-between items-center bg-white p-5 rounded-xl shadow-sm border border-gray-100 space-y-2 md:space-y-0">
                <div>
                    <h1 class="font-bold text-2xl text-gray-800 flex items-center gap-2">
                        <i class="fas fa-vault text-teal-600"></i> Brankas & Perpindahan Kas
                    </h1>
                    <p class="text-sm text-gray-500 mt-1">{{ $office->code }} — {{ $office->name }} · Tanggal buku {{ $office->book_date->format('d M Y') }}</p>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    @if ($offices->count() > 1)
                        <form method="GET" action="{{ route('cashTransfer') }}" data-page-loading>
                            <select name="office_id" onchange="this.form.submit()"
                                class="rounded-lg border-gray-300 shadow-sm p-2.5 border focus:ring-2 focus:ring-teal-500">
                                @foreach ($offices as $option)
                                    <option value="{{ $option->id }}" @selected($option->id === $office->id)>{{ $option->code }} — {{ $option->name }}</option>
                                @endforeach
                            </select>
                        </form>
                    @endif
                    @if ($cashSession)
                        <x-button id="addBtn" size="lg" variant="primary" class="bg-teal-600 hover:bg-teal-700 shadow-md" icon="plus">Ajukan Perpindahan</x-button>
                    @endif
                </div>
            </div>

            <!-- KPI Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-teal-50 p-5 rounded-xl shadow-sm border border-teal-100 flex justify-between items-center">
                    <div>
                        <p class="text-teal-600 uppercase font-semibold tracking-wide text-xs">Saldo Kas Brankas</p>
                        <h2 class="text-3xl font-bold text-teal-700 mt-2">{{ $rupiah($vaultBalance) }}</h2>
                    </div>
                    <i class="fas fa-vault text-4xl text-teal-300 opacity-50"></i>
                </div>
                <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Kas Teller Saya</p>
                    <h2 class="text-2xl font-bold text-gray-800 mt-1">{{ $tellerBalance !== null ? $rupiah($tellerBalance) : '-' }}</h2>
                    <p class="text-xs text-gray-400 mt-2">{{ $cashSession ? 'Sesi ' . $cashSession['number'] : 'Tidak ada sesi kas terbuka' }}</p>
                </div>
                <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Menunggu Konfirmasi</p>
                    <h2 class="text-2xl font-bold text-gray-800 mt-1">{{ $pendingCount }}</h2>
                    <p class="text-xs text-yellow-600 mt-2">Dikonfirmasi pemegang brankas, bukan pengaju</p>
                </div>
            </div>

            <!-- Table -->
            <div class="w-full bg-white rounded-xl shadow-md border border-gray-100">
                <div class="p-5 overflow-auto">
                    @if ($transfers === [])
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-center">
                            <p class="text-yellow-800 font-semibold">Belum ada perpindahan kas hari ini</p>
                        </div>
                    @else
                        <table class="w-full text-left">
                            <thead class="bg-gray-100 text-gray-600 text-sm leading-normal">
                                <tr>
                                    <th class="p-4 font-bold rounded-tl-lg text-center" width="5%">No</th>
                                    <th class="p-4 font-bold">Perpindahan</th>
                                    <th class="p-4 font-bold text-right">Nominal</th>
                                    <th class="p-4 font-bold">Diajukan</th>
                                    <th class="p-4 font-bold">Dikonfirmasi</th>
                                    <th class="p-4 font-bold text-center">Status</th>
                                    <th class="p-4 font-bold text-center rounded-tr-lg" width="15%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-700 text-sm">
                                @php $no = 1; @endphp
                                @foreach ($transfers as $item)
                                    <tr class="hover:bg-gray-50 transition duration-150">
                                        <td class="p-4 font-medium text-center">{{ $no++ }}</td>
                                        <td class="p-4 space-y-1">
                                            <div class="font-bold text-gray-900">
                                                <i class="fas fa-{{ $item['direction'] === 'vault_to_teller' ? 'arrow-right' : 'arrow-left' }} text-teal-500"></i>
                                                {{ $item['direction_label'] }}
                                            </div>
                                            <div class="text-xs text-gray-400 font-mono">{{ $item['number'] }}</div>
                                            <div class="text-xs text-gray-500">{{ $item['note'] ?? '' }}</div>
                                        </td>
                                        <td class="p-4 text-right whitespace-nowrap font-semibold">{{ $rupiah($item['amount']) }}</td>
                                        <td class="p-4 space-y-1">
                                            <div>{{ $item['requested_by_name'] }}</div>
                                            <div class="text-xs text-gray-400">{{ $item['created_at']->format('H:i') }}</div>
                                        </td>
                                        <td class="p-4 space-y-1">
                                            <div>{{ $item['confirmed_by_name'] ?? '-' }}</div>
                                            <div class="text-xs text-gray-400">{{ $item['confirmed_at']?->format('H:i') }}</div>
                                            @if ($item['rejection_reason'])
                                                <div class="text-xs text-red-500">{{ $item['rejection_reason'] }}</div>
                                            @endif
                                        </td>
                                        <td class="p-4 text-center">
                                            <span class="{{ $statusColor($item['status']) }} text-xs px-3 py-1 rounded-full font-bold border whitespace-nowrap">{{ $item['status_label'] }}</span>
                                        </td>
                                        <td class="p-4">
                                            @if ($item['can_confirm'])
                                                <div class="flex justify-center items-center gap-2">
                                                    <form action="{{ route('confirmCashTransfer', $item['id']) }}" method="POST" class="inline confirmForm">
                                                        @csrf
                                                        <button type="button" title="Konfirmasi" data-amount="{{ $rupiah($item['amount']) }}" data-direction="{{ $item['direction_label'] }}"
                                                            class="confirm-transfer w-10 h-10 flex items-center justify-center bg-green-500 text-white rounded-lg shadow hover:bg-green-600 hover:scale-105 transition">
                                                            <i class="fas fa-check text-lg"></i>
                                                        </button>
                                                    </form>
                                                    <button type="button" title="Tolak" data-id="{{ $item['id'] }}"
                                                        class="rejectBtn w-10 h-10 flex items-center justify-center bg-red-500 text-white rounded-lg shadow hover:bg-red-600 hover:scale-105 transition">
                                                        <i class="fas fa-times text-lg"></i>
                                                    </button>
                                                </div>
                                            @else
                                                <p class="text-center text-gray-300">-</p>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>

        </div>
    </main>

    <!-- SCRIPTS -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="{{ asset('modal/cashTransfer.js') }}"></script>

    <!-- Modals -->
    @if ($cashSession)
        @include('cash.modal.cashTransferAdd')
    @endif
    @include('cash.modal.cashTransferReject')

    @include('sweetalert::alert')
    @include('layout.loading')
</body>

</html>
