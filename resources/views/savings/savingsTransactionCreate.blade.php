<!DOCTYPE html>
<html lang="id">

<head>
    <title>Transaksi Setor & Tarik</title>
    @include('layout.head')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-gray-50 font-sans">
    @include('layout.sidebar')

    <main class="md:ml-64 xl:ml-72 2xl:ml-72">
        @include('layout.navbar')

        @php
            $inputClass = 'w-full rounded-lg border-gray-300 shadow-sm p-2.5 border focus:ring-2 focus:ring-emerald-500';
            $office = auth()->user()->office;
        @endphp

        <div class="p-6 flex justify-center">
            <div class="w-full max-w-3xl bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden">
                <!-- Header -->
                <div class="bg-emerald-600 p-6 md:flex justify-between items-center space-y-3 md:space-y-0">
                    <div>
                        <h1 class="text-2xl font-bold text-white flex items-center gap-2">
                            <i class="fas fa-exchange-alt"></i> Setor & Tarik Tunai
                        </h1>
                        <p class="text-emerald-100 text-sm mt-1">
                            {{ $office->name }} · Tanggal buku {{ $office->book_date->format('d M Y') }} · Sesi {{ $cashSession['number'] }}
                        </p>
                    </div>
                    <div class="bg-emerald-700/50 rounded-lg px-4 py-2 text-right">
                        <p class="text-emerald-100 text-xs uppercase tracking-wide">Kas Teller</p>
                        <p class="text-white text-xl font-bold">Rp {{ number_format($tellerBalance, 0, ',', '.') }}</p>
                    </div>
                </div>

                <!-- Body -->
                <form id="transactionForm" method="POST" action="{{ route('postSavingsTransaction') }}" class="p-8 space-y-8"
                    data-teller-balance="{{ $tellerBalance }}" data-teller-limit="{{ $limits['teller_withdrawal'] }}"
                    data-branch-head-limit="{{ $limits['branch_head_withdrawal'] }}" data-mandatory="{{ $limits['mandatory_monthly'] }}"
                    data-voluntary-minimum="{{ $limits['voluntary_minimum_deposit'] }}" data-account-codes='@json($accountCodes)'>
                    @csrf
                    <input type="hidden" id="accountNumber" name="account_number" value="{{ old('account_number') }}">

                    <!-- Account Search -->
                    <div>
                        <h3 class="text-sm font-bold text-emerald-600 uppercase tracking-wider mb-4 border-b pb-2">
                            <i class="fas fa-search"></i> Rekening Anggota
                        </h3>
                        <div class="flex gap-2">
                            <input type="text" id="accountKeyword" class="{{ $inputClass }}"
                                placeholder="Nama anggota, nomor anggota, atau nomor rekening (min. 3 karakter)">
                            <x-button id="searchBtn" variant="primary" size="md" class="bg-emerald-600 hover:bg-emerald-700" icon="search">Cari</x-button>
                        </div>
                        <div id="searchResult" class="hidden mt-3 border border-gray-200 rounded-lg divide-y divide-gray-100 max-h-72 overflow-y-auto"></div>

                        <div id="selectedAccount" class="hidden mt-4 bg-emerald-50 border border-emerald-200 rounded-lg p-4">
                            <div class="flex justify-between items-start gap-4">
                                <div>
                                    <p class="font-bold text-gray-900 text-lg" id="selectedMember"></p>
                                    <p class="text-sm text-gray-600 font-mono" id="selectedNumber"></p>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs text-gray-500 uppercase tracking-wide">Saldo</p>
                                    <p class="text-xl font-bold text-emerald-700" id="selectedBalance"></p>
                                    <p class="text-xs text-gray-500" id="selectedAvailable"></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Transaction -->
                    <div>
                        <h3 class="text-sm font-bold text-emerald-600 uppercase tracking-wider mb-4 border-b pb-2">
                            <i class="fas fa-money-bill-wave"></i> Transaksi
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Jenis Transaksi</label>
                                <div class="grid grid-cols-2 gap-2">
                                    <label class="flex items-center gap-2 p-3 border rounded-lg cursor-pointer hover:bg-gray-50">
                                        <input type="radio" name="type" value="deposit" class="text-emerald-600" @checked(old('type', 'deposit') === 'deposit')>
                                        <span class="font-semibold text-gray-700">Setoran</span>
                                    </label>
                                    <label class="flex items-center gap-2 p-3 border rounded-lg cursor-pointer hover:bg-gray-50">
                                        <input type="radio" name="type" value="withdrawal" class="text-emerald-600" @checked(old('type') === 'withdrawal')>
                                        <span class="font-semibold text-gray-700">Penarikan</span>
                                    </label>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Nominal</label>
                                <div class="flex items-center gap-2">
                                    <span class="text-gray-500 font-semibold">Rp</span>
                                    <input type="text" id="amount" name="amount" value="{{ old('amount') }}"
                                        class="currency {{ $inputClass }} text-lg font-bold" inputmode="numeric" required>
                                </div>
                            </div>
                        </div>
                        <div id="ruleNotice" class="hidden mt-4 rounded-lg p-3 text-sm border"></div>
                    </div>

                    <!-- Journal Preview -->
                    <div>
                        <h3 class="text-sm font-bold text-emerald-600 uppercase tracking-wider mb-4 border-b pb-2">
                            <i class="fas fa-book"></i> Pratinjau Jurnal Otomatis
                        </h3>
                        <table class="w-full text-left text-sm">
                            <thead class="bg-gray-100 text-gray-600 leading-normal">
                                <tr>
                                    <th class="p-3 font-bold rounded-tl-lg">Akun</th>
                                    <th class="p-3 font-bold text-right">Debit</th>
                                    <th class="p-3 font-bold text-right rounded-tr-lg">Kredit</th>
                                </tr>
                            </thead>
                            <tbody id="journalPreview" class="text-gray-700">
                                <tr>
                                    <td class="p-3 text-gray-400 text-center" colspan="3">Pilih rekening dan isi nominal</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Footer -->
                    <div class="flex items-center justify-end gap-4 pt-6 border-t border-gray-100">
                        <a href="{{ route('savingsTransaction') }}"
                            class="px-6 py-3 text-gray-700 hover:bg-gray-100 rounded-lg font-bold">Batal</a>
                        <button type="submit" id="submitBtn" disabled
                            class="px-8 py-3 bg-emerald-600 text-white rounded-lg shadow-lg font-bold hover:bg-emerald-700 transition disabled:bg-emerald-300 disabled:cursor-not-allowed flex items-center gap-2">
                            <i class="fas fa-save"></i> Proses Transaksi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <!-- SCRIPTS -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="{{ asset('modal/savingsTransactionCreate.js') }}"></script>

    @include('sweetalert::alert')
    @include('layout.loading')
</body>

</html>
