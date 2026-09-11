<!DOCTYPE html>
<html lang="id">

<head>
    <title>Registrasi Anggota</title>
    @include('layout.head')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-gray-50 font-sans">
    @include('layout.sidebar')

    <main class="md:ml-64 xl:ml-72 2xl:ml-72">
        @include('layout.navbar')

        @php
            $inputClass = 'w-full rounded-lg border-gray-300 shadow-sm p-2.5 border focus:ring-2 focus:ring-indigo-500';
        @endphp

        <div class="p-6 flex justify-center">
            <div class="w-full max-w-3xl bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden">
                <!-- Header -->
                <div class="bg-indigo-600 p-6">
                    <h1 class="text-2xl font-bold text-white flex items-center gap-2">
                        <i class="fas fa-user-plus"></i> Registrasi Calon Anggota
                    </h1>
                    <p class="text-indigo-100 text-sm mt-1">
                        {{ $office->name }} · Tanggal buku {{ $office->book_date->format('d M Y') }}
                    </p>
                </div>

                <!-- Body -->
                <form id="memberForm" method="POST" action="{{ route('postMember') }}" enctype="multipart/form-data"
                    class="p-8 space-y-8">
                    @csrf

                    <!-- Identity -->
                    <div>
                        <h3 class="text-sm font-bold text-indigo-600 uppercase tracking-wider mb-4 border-b pb-2">
                            <i class="fas fa-id-badge"></i> Identitas
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-semibold text-gray-700 mb-1">NIK</label>
                                <input type="text" id="nik" name="nik" value="{{ old('nik') }}" inputmode="numeric"
                                    maxlength="16" class="{{ $inputClass }} font-mono tracking-wider"
                                    placeholder="16 digit sesuai KTP" required>
                                <p class="text-xs text-gray-400 mt-1">NIK dicek otomatis ke seluruh kantor koperasi.</p>
                                <div id="nikCheckResult" class="hidden mt-2 rounded-lg p-3 text-sm border"></div>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Lengkap</label>
                                <input type="text" name="name" value="{{ old('name') }}" class="{{ $inputClass }}"
                                    placeholder="Sesuai KTP" required>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Tempat Lahir</label>
                                <input type="text" name="birth_place" value="{{ old('birth_place') }}"
                                    class="{{ $inputClass }}" required>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Tanggal Lahir</label>
                                <input type="date" name="birth_date" value="{{ old('birth_date') }}"
                                    class="{{ $inputClass }}" required>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Jenis Kelamin</label>
                                <select name="gender" class="{{ $inputClass }}" required>
                                    @foreach ($genders as $code => $label)
                                        <option value="{{ $code }}" @selected(old('gender') === $code)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">No. Telepon</label>
                                <input type="text" name="phone" value="{{ old('phone') }}" class="{{ $inputClass }}"
                                    placeholder="e.g. 081234567890" required>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Alamat</label>
                                <textarea name="address" rows="2" class="{{ $inputClass }}" required>{{ old('address') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Occupation -->
                    <div>
                        <h3 class="text-sm font-bold text-indigo-600 uppercase tracking-wider mb-4 border-b pb-2">
                            <i class="fas fa-briefcase"></i> Pekerjaan dan Penghasilan
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Pekerjaan</label>
                                <select name="occupation" class="{{ $inputClass }}" required>
                                    @foreach ($occupations as $occupation)
                                        <option value="{{ $occupation }}" @selected(old('occupation') === $occupation)>{{ $occupation }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Penghasilan per Bulan</label>
                                <div class="flex items-center gap-2">
                                    <span class="text-gray-500 font-semibold">Rp</span>
                                    <input type="text" name="monthly_income" value="{{ old('monthly_income') }}"
                                        class="currency {{ $inputClass }}" inputmode="numeric" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Heir -->
                    <div>
                        <h3 class="text-sm font-bold text-indigo-600 uppercase tracking-wider mb-4 border-b pb-2">
                            <i class="fas fa-user-friends"></i> Ahli Waris
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Nama</label>
                                <input type="text" name="heir_name" value="{{ old('heir_name') }}"
                                    class="{{ $inputClass }}" required>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Hubungan</label>
                                <select name="heir_relationship" class="{{ $inputClass }}" required>
                                    @foreach ($heirRelationships as $relationship)
                                        <option value="{{ $relationship }}" @selected(old('heir_relationship') === $relationship)>{{ $relationship }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">No. Telepon</label>
                                <input type="text" name="heir_phone" value="{{ old('heir_phone') }}"
                                    class="{{ $inputClass }}">
                            </div>
                        </div>
                    </div>

                    <!-- Document and Consent -->
                    <div>
                        <h3 class="text-sm font-bold text-indigo-600 uppercase tracking-wider mb-4 border-b pb-2">
                            <i class="fas fa-file-signature"></i> Dokumen dan Persetujuan
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Foto KTP</label>
                                <input type="file" id="ktpPhoto" name="ktp_photo" accept="image/*"
                                    class="{{ $inputClass }} text-sm" required>
                                <p class="text-xs text-gray-400 mt-1">JPG atau PNG, maksimal 2 MB.</p>
                            </div>
                            <div
                                class="h-36 rounded-lg border-2 border-dashed border-gray-200 bg-gray-50 flex items-center justify-center overflow-hidden">
                                <span id="ktpPlaceholder" class="text-sm text-gray-400"><i class="fas fa-id-card"></i> Pratinjau KTP</span>
                                <img id="ktpPreview" alt="Pratinjau KTP" class="hidden h-full w-full object-contain">
                            </div>
                        </div>

                        <div class="bg-indigo-50 p-4 rounded-lg border border-indigo-100 mt-5">
                            <label class="flex items-start gap-3 cursor-pointer">
                                <input type="checkbox" name="data_consent" value="1" @checked(old('data_consent'))
                                    class="w-5 h-5 mt-0.5 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500" required>
                                <div>
                                    <span class="block text-sm font-bold text-indigo-800">Persetujuan pemrosesan data pribadi</span>
                                    <span class="block text-xs text-indigo-600 mt-1">{{ $consentText }}</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-sm text-yellow-800">
                        Setelah disimpan, pendaftaran menunggu persetujuan kepala cabang. Anggota aktif dan nomor anggota
                        terbit setelah calon anggota menyetor simpanan pokok di teller.
                    </div>

                    <!-- Footer -->
                    <div class="flex items-center justify-end gap-4 pt-6 border-t border-gray-100">
                        <a href="{{ route('member') }}"
                            class="px-6 py-3 text-gray-700 hover:bg-gray-100 rounded-lg font-bold">Batal</a>
                        <button type="submit" id="submitBtn"
                            class="px-8 py-3 bg-indigo-600 text-white rounded-lg shadow-lg font-bold hover:bg-indigo-700 transition disabled:bg-indigo-300 disabled:cursor-not-allowed flex items-center gap-2">
                            <i class="fas fa-save"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <!-- SCRIPTS -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="{{ asset('modal/memberCreate.js') }}"></script>

    @include('sweetalert::alert')
    @include('layout.loading')
</body>

</html>
