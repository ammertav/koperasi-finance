<!-- EDIT MODAL -->
<div id="editModal"
    class="hidden fixed inset-0 bg-gray-900/60 backdrop-blur-sm flex items-center justify-center z-50 overflow-y-auto px-4 py-6">
    <div class="bg-white rounded-2xl p-8 w-full max-w-lg shadow-2xl relative transform transition-all scale-100">
        <button id="closeModal" type="button" class="absolute top-5 right-5 text-gray-400 hover:text-gray-600 transition"><i
                class="fas fa-times text-xl"></i></button>
        <h2 class="text-2xl font-bold mb-6 text-gray-800 flex items-center gap-2">
            <i class="fas fa-edit text-blue-600"></i> Edit
        </h2>

        <form id="editForm" method="POST" class="space-y-5">
            @csrf @method('PUT')

            <div class="grid grid-cols-3 gap-5">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Kode</label>
                    <input type="text" id="editCode" disabled
                        class="w-full rounded-lg border-gray-300 shadow-sm p-2.5 border bg-gray-100 text-gray-500">
                </div>
                <div class="col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Nama</label>
                    <input type="text" id="editName" name="name"
                        class="w-full rounded-lg border-gray-300 shadow-sm p-2.5 border focus:ring-2 focus:ring-blue-500"
                        required>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Tipe</label>
                    <select id="editType" name="type"
                        class="w-full rounded-lg border-gray-300 shadow-sm p-2.5 border focus:ring-2 focus:ring-blue-500"
                        required>
                        @foreach (\App\Models\Office::TYPES as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Kantor Induk</label>
                    <select id="editParentId" name="parent_id"
                        class="w-full rounded-lg border-gray-300 shadow-sm p-2.5 border focus:ring-2 focus:ring-blue-500">
                        <option value="">-- Tanpa induk --</option>
                        @foreach ($offices as $office)
                            <option value="{{ $office->id }}">{{ $office->code }} — {{ $office->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Alamat</label>
                <textarea id="editAddress" name="address" rows="2"
                    class="w-full rounded-lg border-gray-300 shadow-sm p-2.5 border focus:ring-2 focus:ring-blue-500"></textarea>
            </div>

            <div class="bg-indigo-50 p-3 rounded-lg border border-indigo-100">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" id="editIsActive" name="is_active" value="1"
                        class="w-5 h-5 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500">
                    <div>
                        <span class="block text-sm font-bold text-indigo-800">Kantor aktif?</span>
                        <span class="block text-xs text-indigo-500">Kode dan tanggal buku tidak dapat diubah dari sini.</span>
                    </div>
                </label>
            </div>

            <x-button type="submit" variant="primary" icon="save"
                class="w-full bg-blue-600 hover:bg-blue-700 justify-center">Perbarui</x-button>
        </form>
    </div>
</div>
