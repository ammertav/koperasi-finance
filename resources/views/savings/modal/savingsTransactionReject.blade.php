<!-- REJECT MODAL -->
<div id="rejectModal"
    class="hidden fixed inset-0 bg-gray-900/60 backdrop-blur-sm flex items-center justify-center z-50 overflow-y-auto px-4 py-6">
    <div class="bg-white rounded-2xl p-8 w-full max-w-lg shadow-2xl relative">
        <button id="closeRejectModal" type="button" class="absolute top-5 right-5 text-gray-400 hover:text-gray-600 transition"><i
                class="fas fa-times text-xl"></i></button>
        <h2 class="text-2xl font-bold mb-6 text-gray-800 flex items-center gap-2">
            <i class="fas fa-ban text-red-600"></i> Tolak Penarikan
        </h2>

        <form action="{{ route('rejectSavingsTransaction', $transaction['id']) }}" method="POST" class="space-y-5">
            @csrf
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-sm text-yellow-800">
                Penarikan <span class="font-bold">Rp {{ number_format($transaction['amount'], 0, ',', '.') }}</span> atas nama
                <span class="font-bold">{{ $transaction['member_name'] }}</span> akan ditolak dan kas tidak dibayarkan.
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Alasan Penolakan</label>
                <textarea name="rejection_reason" rows="3" maxlength="200"
                    class="w-full rounded-lg border-gray-300 shadow-sm p-2.5 border focus:ring-2 focus:ring-red-500"
                    required placeholder="e.g. Tanda tangan slip penarikan tidak sesuai">{{ old('rejection_reason') }}</textarea>
            </div>

            <x-button type="submit" variant="primary" icon="save"
                class="w-full bg-slate-700 hover:bg-slate-800 justify-center">Tolak Penarikan</x-button>
        </form>
    </div>
</div>
