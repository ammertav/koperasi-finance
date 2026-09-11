<!-- ADD MODAL -->
<div id="addModal"
    class="hidden fixed inset-0 bg-gray-900/60 backdrop-blur-sm flex items-center justify-center z-50 overflow-y-auto px-4 py-6">
    <div class="bg-white rounded-2xl p-0 w-full max-w-2xl shadow-2xl relative my-5 flex flex-col max-h-[90vh]">
        <!-- Header -->
        <div
            class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50 rounded-t-2xl sticky top-0 z-10">
            <h2 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                <div class="w-8 h-8 bg-slate-100 rounded-full flex items-center justify-center text-slate-600">
                    <i class="fas fa-user-plus"></i>
                </div>
                Tambah
            </h2>
            <button id="closeAddModal" type="button"
                class="text-gray-400 hover:text-red-500 transition text-2xl leading-none">&times;</button>
        </div>

        <!-- Body (Scrollable) -->
        <div class="p-6 overflow-y-auto flex-grow">
            <form id="addForm" method="post" action="{{ route('postUser') }}" class="space-y-5">
                @csrf

                <!-- Basic Info -->
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Nama</label>
                        <input type="text" name="name"
                            class="w-full rounded-lg border-gray-300 shadow-sm p-2.5 border focus:ring-2 focus:ring-slate-500"
                            required>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Email</label>
                        <input type="email" name="email"
                            class="w-full rounded-lg border-gray-300 shadow-sm p-2.5 border focus:ring-2 focus:ring-slate-500"
                            required>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Kantor Penempatan</label>
                            <select name="office_id"
                                class="w-full rounded-lg border-gray-300 shadow-sm p-2.5 border focus:ring-2 focus:ring-slate-500"
                                required>
                                @foreach ($offices as $office)
                                    <option value="{{ $office->id }}">{{ $office->code }} — {{ $office->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Kata Sandi</label>
                            <input type="password" name="password" minlength="8"
                                class="w-full rounded-lg border-gray-300 shadow-sm p-2.5 border focus:ring-2 focus:ring-slate-500"
                                required>
                        </div>
                    </div>
                </div>

                <!-- Roles Section -->
                <div class="bg-indigo-50 p-5 rounded-xl border border-indigo-100">
                    <h3 class="text-sm font-bold text-indigo-700 uppercase tracking-wider mb-4 flex items-center gap-2">
                        <i class="fas fa-user-shield"></i> Peran
                    </h3>

                    @foreach ($roles->groupBy('location') as $location => $locationRoles)
                        <p class="text-xs font-bold text-gray-500 uppercase mb-2 {{ $loop->first ? '' : 'mt-4' }}">
                            Peran {{ \App\Models\Role::LOCATIONS[$location] }}
                        </p>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                            @foreach ($locationRoles as $role)
                                <label
                                    class="flex items-center gap-3 cursor-pointer bg-white p-2 rounded-lg border border-indigo-100">
                                    <input type="checkbox" name="role_ids[]" value="{{ $role->id }}"
                                        class="w-5 h-5 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500">
                                    <span class="text-sm text-gray-700"><span class="font-bold">{{ $role->code }}</span> —
                                        {{ $role->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    @endforeach
                </div>

                <div class="bg-indigo-50 p-3 rounded-lg border border-indigo-100">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" checked
                            class="w-5 h-5 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500">
                        <div>
                            <span class="block text-sm font-bold text-indigo-800">Pengguna aktif?</span>
                            <span class="block text-xs text-indigo-500">Pengguna nonaktif tidak dapat masuk.</span>
                        </div>
                    </label>
                </div>

                <x-button type="submit" variant="primary" icon="save"
                    class="w-full bg-slate-700 hover:bg-slate-800 justify-center">Simpan</x-button>
            </form>
        </div>
    </div>
</div>
