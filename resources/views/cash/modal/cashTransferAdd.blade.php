<!-- ADD MODAL -->
<div id="addModal"
    class="hidden fixed inset-0 bg-gray-900/60 backdrop-blur-sm flex items-center justify-center z-50 overflow-y-auto px-4 py-6">
    <div class="bg-white rounded-2xl p-8 w-full max-w-lg shadow-2xl relative">
        <button id="closeAddModal" type="button" class="absolute top-5 right-5 text-gray-400 hover:text-gray-600 transition"><i
                class="fas fa-times text-xl"></i></button>
        <h2 class="text-2xl font-bold mb-6 text-gray-800 flex items-center gap-2">
            <i class="fas fa-exchange-alt text-teal-600"></i> Ajukan Perpindahan Kas
        </h2>

        <form id="transferForm" action="{{ route('postCashTransfer') }}" method="POST" class="space-y-5">
            @csrf
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Arah Perpindahan</label>
                <select name="direction" class="w-full rounded-lg border-gray-300 shadow-sm p-2.5 border focus:ring-2 focus:ring-teal-500" required>
                    @foreach ($directions as $code => $label)
                        <option value="{{ $code }}" @selected(old('direction') === $code)>{{ $label }}</option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-400 mt-1">Brankas ke teller untuk tambahan kas; teller ke brankas untuk menyetor kelebihan kas.</p>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Nominal</label>
                <div class="flex items-center gap-2">
                    <span class="text-gray-500 font-semibold">Rp</span>
                    <input type="text" name="amount" value="{{ old('amount') }}" inputmode="numeric"
                        class="currency w-full rounded-lg border-gray-300 shadow-sm p-2.5 border focus:ring-2 focus:ring-teal-500" required>
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Catatan</label>
                <input type="text" name="note" value="{{ old('note') }}" maxlength="200"
                    class="w-full rounded-lg border-gray-300 shadow-sm p-2.5 border focus:ring-2 focus:ring-teal-500"
                    placeholder="e.g. Tambahan kas untuk penarikan besar">
            </div>

            <x-button type="submit" variant="primary" icon="save"
                class="w-full bg-slate-700 hover:bg-slate-800 justify-center">Simpan</x-button>
        </form>
    </div>
</div>
