<!DOCTYPE html>
<html lang="id">

<head>
    <title>Detail Jurnal {{ $journal->number }}</title>
    @include('layout.head')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-gray-50 font-sans">
    @include('layout.sidebar')

    <main class="md:ml-64 xl:ml-72 2xl:ml-72">
        @include('layout.navbar')
        <div class="p-6 space-y-6">

            @php
                $user = auth()->user();
                $canReverse = $user->hasAccess('accounting', 'manage') && $reversalCheck['allowed'];
                $ledgerLink = fn (int $accountId) => route('generalLedger', [
                    'account_id' => $accountId,
                    'office_id' => $journal->office_id,
                    'start_date' => $journal->book_date->toDateString(),
                    'end_date' => $journal->book_date->toDateString(),
                ]);
                $canOpen = fn ($otherJournal) => $user->canAccessAllOffices() || $otherJournal->office_id === $user->office_id;
            @endphp

            <!-- Header -->
            <div
                class="md:flex justify-between items-center bg-white p-5 rounded-xl shadow-sm border border-gray-100 space-y-2 md:space-y-0">
                <div>
                    <h1 class="font-bold text-2xl text-gray-800 flex items-center gap-2">
                        <i class="fas fa-book text-indigo-600"></i> Jurnal <span class="font-mono">{{ $journal->number }}</span>
                    </h1>
                    <p class="text-sm text-gray-500 mt-1">{{ $journal->description }}</p>
                </div>
                <div class="flex gap-2">
                    <x-button :href="route('journal')" variant="secondary" icon="arrow-left">Kembali</x-button>
                    @if ($canReverse)
                        <x-button id="reverseBtn" variant="danger" icon="undo">Balik Jurnal</x-button>
                    @endif
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-2 xl:grid-cols-4 gap-4">
                <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Tanggal Buku</p>
                    <h2 class="text-2xl font-bold text-gray-800 mt-1">{{ $journal->book_date->format('d M Y') }}</h2>
                    <p class="text-xs text-gray-400 mt-2">Dicatat: {{ $journal->created_at->format('d M Y, H:i') }}</p>
                </div>
                <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Kantor</p>
                    <h2 class="text-2xl font-bold text-gray-800 mt-1">{{ $journal->office->code }}</h2>
                    <p class="text-xs text-gray-400 mt-2">{{ $journal->office->name }}</p>
                </div>
                <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Transaksi</p>
                    <h2 class="text-2xl font-bold text-gray-800 mt-1">{{ $journal->transaction_type_label }}</h2>
                    <p class="text-xs text-gray-400 mt-2">Oleh: {{ $journal->creator->name ?? 'Sistem' }}</p>
                </div>
                <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Total</p>
                    <h2 class="text-2xl font-bold text-gray-800 mt-1">Rp {{ number_format($journal->total_amount, 0, ',', '.') }}</h2>
                    <p class="text-xs text-emerald-600 mt-2 flex items-center gap-1">
                        <i class="fas fa-check-circle"></i> Debit = Kredit · {{ $journal->status_label }}
                    </p>
                </div>
            </div>

            <!-- Reversal Info -->
            @if ($journal->reversal)
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-center">
                    <p class="text-yellow-800 font-semibold">Jurnal ini sudah dibalik</p>
                    <p class="text-sm text-yellow-600 mt-1">
                        Jurnal pembalik:
                        <a href="{{ route('detailJournal', $journal->reversal->id) }}" class="font-mono font-bold underline">{{ $journal->reversal->number }}</a>
                        pada {{ $journal->reversal->book_date->format('d M Y') }}
                    </p>
                </div>
            @endif

            @if ($journal->reversalOf)
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-center">
                    <p class="text-yellow-800 font-semibold">Jurnal pembalik</p>
                    <p class="text-sm text-yellow-600 mt-1">
                        Membalik jurnal
                        <a href="{{ route('detailJournal', $journal->reversalOf->id) }}" class="font-mono font-bold underline">{{ $journal->reversalOf->number }}</a>
                        tanggal {{ $journal->reversalOf->book_date->format('d M Y') }}
                    </p>
                </div>
            @endif

            <!-- Inter Office -->
            @if ($journal->inter_office_group)
                <div class="bg-white p-6 rounded-xl shadow-md border border-gray-100">
                    <h3 class="text-sm font-bold text-blue-600 uppercase tracking-wider mb-4 border-b pb-2">
                        <i class="fas fa-exchange-alt"></i> Transaksi antar kantor
                    </h3>
                    <p class="text-sm text-gray-500 mb-4">
                        Jurnal Rekening Antar Kantor dibentuk bersamaan di setiap kantor dalam satu proses (RAK-02).
                    </p>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        @foreach ($journal->interOfficeJournals as $groupJournal)
                            <div class="p-4 rounded-lg border {{ $groupJournal->is($journal) ? 'border-blue-300 bg-blue-50' : 'border-gray-200' }}">
                                <p class="text-xs text-gray-500">{{ $groupJournal->office->code }} — {{ $groupJournal->office->name }}</p>
                                @if ($canOpen($groupJournal) && ! $groupJournal->is($journal))
                                    <a href="{{ route('detailJournal', $groupJournal->id) }}"
                                        class="font-mono font-bold text-blue-700 underline">{{ $groupJournal->number }}</a>
                                @else
                                    <p class="font-mono font-bold text-gray-800">{{ $groupJournal->number }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Table -->
            <div class="w-full bg-white rounded-xl shadow-md border border-gray-100">
                <div class="p-5 overflow-auto">
                    <table class="w-full text-left">
                        <thead class="bg-gray-100 text-gray-600 text-sm leading-normal">
                            <tr>
                                <th class="p-4 font-bold rounded-tl-lg text-center" width="5%">No</th>
                                <th class="p-4 font-bold">Akun</th>
                                <th class="p-4 font-bold">Keterangan</th>
                                <th class="p-4 font-bold text-right">Debit</th>
                                <th class="p-4 font-bold text-right rounded-tr-lg">Kredit</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-700 text-sm">
                            @php $no = 1; @endphp
                            @foreach ($journal->details as $detail)
                                <tr class="hover:bg-gray-50 transition duration-150">
                                    <td class="p-4 font-medium text-center">{{ $no++ }}</td>
                                    <td class="p-4 {{ bccomp($detail->credit, '0', 2) > 0 ? 'pl-10' : '' }}">
                                        <a href="{{ $ledgerLink($detail->account_id) }}" class="hover:text-indigo-600" title="Lihat buku besar">
                                            <span class="font-mono text-xs text-gray-400">{{ $detail->account->code }}</span>
                                            <span class="font-semibold">{{ $detail->account->name }}</span>
                                        </a>
                                    </td>
                                    <td class="p-4 text-xs text-gray-500">{{ $detail->description ?? '-' }}</td>
                                    <td class="p-4 text-right whitespace-nowrap">
                                        {{ bccomp($detail->debit, '0', 2) > 0 ? 'Rp '.number_format($detail->debit, 0, ',', '.') : '-' }}
                                    </td>
                                    <td class="p-4 text-right whitespace-nowrap">
                                        {{ bccomp($detail->credit, '0', 2) > 0 ? 'Rp '.number_format($detail->credit, 0, ',', '.') : '-' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="text-sm font-bold text-gray-800 bg-gray-50">
                            <tr>
                                <td class="p-4" colspan="3">Total</td>
                                <td class="p-4 text-right whitespace-nowrap">Rp {{ number_format($journal->total_amount, 0, ',', '.') }}</td>
                                <td class="p-4 text-right whitespace-nowrap">Rp {{ number_format($journal->total_amount, 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

        </div>
    </main>

    <!-- SCRIPTS -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="{{ asset('modal/journalShow.js') }}"></script>

    <!-- Modals -->
    @if ($canReverse)
        @include('accounting.modal.journalReverse')
    @endif

    @include('sweetalert::alert')
    @include('layout.loading')
</body>

</html>
