<!DOCTYPE html>
<html lang="id">

<head>
    <title>Bukti Transaksi {{ $transaction['number'] }}</title>
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
                $statusColor = match ($transaction['status']) {
                    'posted' => 'bg-green-100 text-green-700 border-green-200',
                    'pending_authorization' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
                    'rejected' => 'bg-red-100 text-red-700 border-red-200',
                    default => 'bg-gray-100 text-gray-700 border-gray-200',
                };
                $approverLabel = $transaction['authorization_role'] === 'MGR' ? 'manajer pusat' : 'kepala cabang';
            @endphp

            <!-- Header -->
            <div
                class="md:flex justify-between items-center bg-white p-5 rounded-xl shadow-sm border border-gray-100 space-y-2 md:space-y-0">
                <div>
                    <h1 class="font-bold text-2xl text-gray-800 flex items-center gap-2">
                        <i class="fas fa-receipt text-emerald-600"></i> Bukti Transaksi <span class="font-mono">{{ $transaction['number'] }}</span>
                    </h1>
                    <p class="text-sm text-gray-500 mt-1">{{ $transaction['description'] }}</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <x-button :href="route('savingsTransaction')" variant="secondary" icon="arrow-left">Kembali</x-button>
                    @if ($canAuthorize)
                        <x-button id="rejectBtn" variant="danger" icon="times">Tolak</x-button>
                        <x-button id="approveBtn" variant="success" icon="check" :disabled="! $authorityCheck['allowed']">Otorisasi</x-button>
                    @endif
                    @if ($transaction['status'] === 'posted')
                        <x-button :href="route('printSavingsTransaction', $transaction['id'])" variant="primary"
                            class="bg-emerald-600 hover:bg-emerald-700" icon="print">Cetak Bukti</x-button>
                    @endif
                </div>
            </div>

            <!-- Status Notice -->
            @if ($transaction['status'] === 'pending_authorization')
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-center">
                    <p class="text-yellow-800 font-semibold">Menunggu otorisasi {{ $approverLabel }}</p>
                    <p class="text-sm text-yellow-600 mt-1">
                        Penarikan melebihi batas teller Rp {{ number_format(config('demo.savings.teller_withdrawal_limit'), 0, ',', '.') }}. Kas belum dibayarkan dan jurnal belum terbentuk.
                        @if ($canAuthorize && ! $authorityCheck['allowed'])
                            <br><b>{{ $authorityCheck['message'] }}</b>
                        @endif
                    </p>
                </div>
            @elseif ($transaction['status'] === 'rejected')
                <div class="bg-red-50 border border-red-200 rounded-lg p-4 text-center">
                    <p class="text-red-800 font-semibold">Penarikan ditolak</p>
                    <p class="text-sm text-red-600 mt-1">{{ $transaction['rejection_reason'] }} — {{ $transaction['rejected_by_name'] }}</p>
                </div>
            @endif

            <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
                <!-- Receipt -->
                <div class="bg-white rounded-xl shadow-md border border-gray-100 p-6">
                    <div class="text-center border-b border-dashed border-gray-300 pb-4">
                        <p class="text-xs text-gray-500 uppercase tracking-wide">{{ $transaction['type_label'] }} {{ $transaction['product_name'] }}</p>
                        <h2 class="text-3xl font-bold mt-2 {{ $transaction['type'] === 'deposit' ? 'text-emerald-700' : 'text-red-600' }}">
                            {{ $rupiah($transaction['amount']) }}
                        </h2>
                        <span class="{{ $statusColor }} inline-block mt-3 text-xs px-3 py-1 rounded-full font-bold border">{{ $transaction['status_label'] }}</span>
                    </div>
                    <dl class="mt-4 space-y-3 text-sm">
                        <div class="flex justify-between gap-4"><dt class="text-gray-500">Anggota</dt><dd class="font-semibold text-gray-800 text-right">{{ $transaction['member_name'] }}</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-gray-500">No. Anggota</dt><dd class="font-mono text-gray-800">{{ $transaction['member_number'] }}</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-gray-500">Rekening</dt>
                            <dd><a href="{{ route('detailSavingsAccount', $transaction['account_number']) }}" class="font-mono font-semibold text-emerald-700 hover:underline">{{ $transaction['account_number'] }}</a></dd>
                        </div>
                        <div class="flex justify-between gap-4"><dt class="text-gray-500">Saldo Sebelum</dt><dd class="font-semibold text-gray-800">{{ $rupiah($transaction['balance_before']) }}</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-gray-500">Saldo Sesudah</dt><dd class="font-bold text-gray-900">{{ $transaction['status'] === 'posted' ? $rupiah($transaction['balance_after']) : '-' }}</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-gray-500">Kantor</dt><dd class="font-semibold text-gray-800 text-right">{{ $transaction['office_code'] }} — {{ $transaction['office_name'] }}</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-gray-500">Waktu</dt><dd class="text-gray-800">{{ $transaction['created_at']->format('d M Y, H:i') }}</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-gray-500">Teller</dt><dd class="text-gray-800 text-right">{{ $transaction['teller_name'] }}</dd></div>
                        @if ($transaction['authorized_by_name'])
                            <div class="flex justify-between gap-4"><dt class="text-gray-500">Diotorisasi</dt><dd class="text-gray-800 text-right">{{ $transaction['authorized_by_name'] }}<br><span class="text-xs text-gray-400">{{ $transaction['authorized_at']->format('d M Y, H:i') }}</span></dd></div>
                        @endif
                    </dl>
                </div>

                <!-- Journal -->
                <div class="xl:col-span-2 bg-white rounded-xl shadow-md border border-gray-100 p-6">
                    <h3 class="text-sm font-bold text-indigo-600 uppercase tracking-wider mb-4 border-b pb-2 flex justify-between items-center">
                        <span><i class="fas fa-book"></i> Jurnal Otomatis</span>
                        @if ($journal && auth()->user()->hasAccess('accounting'))
                            <a href="{{ route('detailJournalMockup', $journal['number']) }}" class="font-mono normal-case tracking-normal hover:underline">{{ $journal['number'] }}</a>
                        @elseif ($journal)
                            <span class="font-mono normal-case tracking-normal">{{ $journal['number'] }}</span>
                        @endif
                    </h3>
                    @if ($journal)
                        <div class="overflow-auto">
                            <table class="w-full text-left">
                                <thead class="bg-gray-100 text-gray-600 text-sm leading-normal">
                                    <tr>
                                        <th class="p-4 font-bold rounded-tl-lg">Akun</th>
                                        <th class="p-4 font-bold">Keterangan</th>
                                        <th class="p-4 font-bold text-right">Debit</th>
                                        <th class="p-4 font-bold text-right rounded-tr-lg">Kredit</th>
                                    </tr>
                                </thead>
                                <tbody class="text-gray-700 text-sm">
                                    @foreach ($journal['lines'] as $line)
                                        <tr class="hover:bg-gray-50 transition duration-150">
                                            <td class="p-4 {{ $line['credit'] > 0 ? 'pl-10' : '' }}">
                                                <span class="font-mono text-xs text-gray-400">{{ $line['account_code'] }}</span>
                                                <span class="font-semibold">{{ $line['account_name'] }}</span>
                                            </td>
                                            <td class="p-4 text-xs text-gray-500">{{ $line['description'] ?? '-' }}</td>
                                            <td class="p-4 text-right whitespace-nowrap">{{ $line['debit'] ? $rupiah($line['debit']) : '-' }}</td>
                                            <td class="p-4 text-right whitespace-nowrap">{{ $line['credit'] ? $rupiah($line['credit']) : '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="text-sm font-bold text-gray-800 bg-gray-50">
                                    <tr>
                                        <td class="p-4" colspan="2">Total <span class="text-xs text-emerald-600 font-semibold ml-2"><i class="fas fa-check-circle"></i> Debit = Kredit</span></td>
                                        <td class="p-4 text-right whitespace-nowrap">{{ $rupiah($journal['total_amount']) }}</td>
                                        <td class="p-4 text-right whitespace-nowrap">{{ $rupiah($journal['total_amount']) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <p class="text-xs text-gray-400 mt-4">Jurnal dibentuk dari template transaksi, tanpa input manual bagian akuntansi.</p>
                    @else
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-center">
                            <p class="text-yellow-800 font-semibold">Jurnal belum terbentuk</p>
                            <p class="text-sm text-yellow-600 mt-1">Jurnal diposting otomatis setelah transaksi diotorisasi.</p>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </main>

    <!-- SCRIPTS -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="{{ asset('modal/savingsTransactionShow.js') }}"></script>

    <!-- Modals -->
    @if ($canAuthorize)
        <form id="approveForm" action="{{ route('approveSavingsTransaction', $transaction['id']) }}" method="POST" class="hidden">
            @csrf
        </form>
        @include('savings.modal.savingsTransactionReject')
    @endif

    @include('sweetalert::alert')
    @include('layout.loading')
</body>

</html>
