<!-- REVERSE MODAL -->
<div id="reverseModal"
    class="hidden fixed inset-0 bg-gray-900/60 backdrop-blur-sm flex items-center justify-center z-50 overflow-y-auto px-4 py-6">
    <div class="bg-white rounded-2xl p-8 w-full max-w-lg shadow-2xl relative transform transition-all scale-100">
        <button id="closeReverseModal" type="button" class="absolute top-5 right-5 text-gray-400 hover:text-gray-600 transition"><i
                class="fas fa-times text-xl"></i></button>
        <h2 class="text-2xl font-bold mb-6 text-gray-800 flex items-center gap-2">
            <i class="fas fa-undo text-red-600"></i> Balik Jurnal
        </h2>

        <form id="reverseForm" action="{{ route('reverseJournal', $journal->id) }}" method="POST" class="space-y-5">
            @csrf
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-sm text-yellow-800">
                Jurnal <span class="font-mono font-bold">{{ $journal->number }}</span> tidak diubah atau dihapus. Sistem
                membuat jurnal pembalik dengan debit dan kredit ditukar pada tanggal buku aktif kantor.
                @if ($journal->inter_office_group)
                    Semua jurnal di kantor lain dalam transaksi ini ikut dibalik.
                @endif
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Alasan Pembalikan</label>
                <textarea name="reason" rows="3" maxlength="200"
                    class="w-full rounded-lg border-gray-300 shadow-sm p-2.5 border focus:ring-2 focus:ring-red-500"
                    required placeholder="e.g. Salah input nominal setoran">{{ old('reason') }}</textarea>
            </div>

            <x-button type="button" variant="primary" icon="save" id="reverseSubmit"
                class="w-full bg-slate-700 hover:bg-slate-800 justify-center">Buat Jurnal Pembalik</x-button>
        </form>
    </div>
</div>
