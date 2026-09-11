<!-- ADD MODAL -->
<div id="addModal"
    class="hidden fixed inset-0 bg-gray-900/60 backdrop-blur-sm flex items-center justify-center z-50 overflow-y-auto px-4 py-6">
    <div class="bg-white rounded-2xl p-8 w-full max-w-lg shadow-2xl relative transform transition-all scale-100">
        <button id="closeAddModal" type="button" class="absolute top-5 right-5 text-gray-400 hover:text-gray-600 transition"><i
                class="fas fa-times text-xl"></i></button>
        <h2 class="text-2xl font-bold mb-6 text-gray-800 flex items-center gap-2">
            <i class="fas fa-building text-cyan-600"></i> Tambah
        </h2>

        <form action="{{ route('postOffice') }}" method="POST" class="space-y-5">
            @csrf
            <div class="grid grid-cols-3 gap-5">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Kode</label>
                    <input type="text" name="code" maxlength="10"
                        class="w-full rounded-lg border-gray-300 shadow-sm p-2.5 border focus:ring-2 focus:ring-cyan-500"
                        required placeholder="10">
                </div>
                <div class="col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Nama</label>
                    <input type="text" name="name"
                        class="w-full rounded-lg border-gray-300 shadow-sm p-2.5 border focus:ring-2 focus:ring-cyan-500"
                        required placeholder="e.g. Cabang Bekasi">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Tipe</label>
                    <select name="type"
                        class="w-full rounded-lg border-gray-300 shadow-sm p-2.5 border focus:ring-2 focus:ring-cyan-500"
                        required>
                        @foreach (\App\Models\Office::TYPES as $value => $label)
                            <option value="{{ $value }}" @selected($value === 'branch')>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Kantor Induk</label>
                    <select name="parent_id"
                        class="w-full rounded-lg border-gray-300 shadow-sm p-2.5 border focus:ring-2 focus:ring-cyan-500">
                        <option value="">-- Tanpa induk --</option>
                        @foreach ($offices as $office)
                            <option value="{{ $office->id }}">{{ $office->code }} — {{ $office->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Alamat</label>
                <textarea name="address" rows="2"
                    class="w-full rounded-lg border-gray-300 shadow-sm p-2.5 border focus:ring-2 focus:ring-cyan-500"></textarea>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Tanggal Buku Awal</label>
                <input type="date" name="book_date" value="{{ now()->toDateString() }}"
                    class="w-full rounded-lg border-gray-300 shadow-sm p-2.5 border focus:ring-2 focus:ring-cyan-500"
                    required>
                <p class="text-xs text-gray-400 mt-1">Tanggal buku selanjutnya maju melalui proses tutup hari.</p>
            </div>

            <div class="bg-indigo-50 p-3 rounded-lg border border-indigo-100">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" checked
                        class="w-5 h-5 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500">
                    <div>
                        <span class="block text-sm font-bold text-indigo-800">Kantor aktif?</span>
                        <span class="block text-xs text-indigo-500">Kantor nonaktif tidak dapat dipilih untuk pengguna baru.</span>
                    </div>
                </label>
            </div>

            <x-button type="submit" variant="primary" icon="save"
                class="w-full bg-slate-700 hover:bg-slate-800 justify-center">Simpan</x-button>
        </form>
    </div>
</div>
