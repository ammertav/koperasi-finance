<div class="flex">
    <aside id="sidebar"
        class="font-poppins fixed inset-y-0 my-6 ml-4 w-full max-w-72 md:max-w-60 xl:max-w-64 2xl:max-w-64 z-50 rounded-lg bg-white overflow-y-auto transform transition-transform duration-300 -translate-x-full md:translate-x-0 ease-in-out shadow-xl">
        <div class="p-2">
            <div class="p-4">
                <a href="{{ route('dashboard') }}">
                    <div class="w-32 md:w-28 xl:w-32 2xl:w-32 h-auto flex items-center mx-auto">
                        <img src="{{ asset('logo.svg') }}" alt="Logo" class="w-full h-auto object-contain">
                    </div>
                </a>
            </div>

            <hr class="mx-5 shadow-2xl text-gray-100 rounded-xl" />

            <ul>
                <!-- Dashboard -->
                <li class="p-4 mx-2">
                    <a href="{{ route('dashboard') }}">
                        <div class="flex space-x-4">
                            <div class="bg-sky-600 p-2 rounded-xl">
                                <i class="material-icons text-white">home</i>
                            </div>
                            <div class="my-auto">
                                <h1 class="text-gray-500 hover:text-black text-base font-normal">
                                    Dashboard
                                </h1>
                            </div>
                        </div>
                    </a>
                </li>

                @if (auth()->user()->hasAccess('organization'))
                    <!-- Organization -->
                    <li class="p-4 mx-2">
                        <div class="flex space-x-4">
                            <div class="bg-sky-600 p-2 rounded-xl">
                                <i class="material-icons text-white">apartment</i>
                            </div>
                            <div class="my-auto">
                                <h1 class="text-black text-base font-normal">
                                    Organisasi
                                </h1>
                            </div>
                        </div>
                    </li>

                    <hr class="mx-5 shadow-2xl text-gray-100 rounded-xl" />

                    <li class="p-4 mx-2 ml-16 md:ml-14">
                        <a href="{{ route('office') }}">
                            <h1 class="text-gray-500 hover:text-black text-base font-normal">
                                Kantor
                            </h1>
                        </a>
                    </li>

                    <li class="p-4 mx-2 ml-16 md:ml-14">
                        <a href="{{ route('user') }}">
                            <h1 class="text-gray-500 hover:text-black text-base font-normal">
                                Pengguna
                            </h1>
                        </a>
                    </li>

                    <li class="p-4 mx-2 ml-16 md:ml-14">
                        <a href="{{ route('role') }}">
                            <h1 class="text-gray-500 hover:text-black text-base font-normal">
                                Peran & Hak Akses
                            </h1>
                        </a>
                    </li>

                    <li class="p-4 mx-2 ml-16 md:ml-14">
                        <a href="{{ route('authorityLimit') }}">
                            <h1 class="text-gray-500 hover:text-black text-base font-normal">
                                Matriks Wewenang
                            </h1>
                        </a>
                    </li>
                @endif

                @if (auth()->user()->hasAccess('member'))
                    <!-- Member -->
                    <li class="p-4 mx-2">
                        <div class="flex space-x-4">
                            <div class="bg-sky-600 p-2 rounded-xl">
                                <i class="material-icons text-white">groups</i>
                            </div>
                            <div class="my-auto">
                                <h1 class="text-black text-base font-normal">
                                    Keanggotaan
                                </h1>
                            </div>
                        </div>
                    </li>

                    <hr class="mx-5 shadow-2xl text-gray-100 rounded-xl" />

                    <li class="p-4 mx-2 ml-16 md:ml-14">
                        <a href="{{ route('member') }}">
                            <h1 class="text-gray-500 hover:text-black text-base font-normal">
                                Data Anggota
                            </h1>
                        </a>
                    </li>

                    @if (auth()->user()->hasAccess('member', 'operate'))
                        <li class="p-4 mx-2 ml-16 md:ml-14">
                            <a href="{{ route('createMember') }}">
                                <h1 class="text-gray-500 hover:text-black text-base font-normal">
                                    Registrasi Anggota
                                </h1>
                            </a>
                        </li>
                    @endif
                @endif

                @if (auth()->user()->hasAccess('savings'))
                    <!-- Savings -->
                    <li class="p-4 mx-2">
                        <div class="flex space-x-4">
                            <div class="bg-sky-600 p-2 rounded-xl">
                                <i class="material-icons text-white">savings</i>
                            </div>
                            <div class="my-auto">
                                <h1 class="text-black text-base font-normal">
                                    Simpanan
                                </h1>
                            </div>
                        </div>
                    </li>

                    <hr class="mx-5 shadow-2xl text-gray-100 rounded-xl" />

                    <li class="p-4 mx-2 ml-16 md:ml-14">
                        <a href="{{ route('savingsProduct') }}">
                            <h1 class="text-gray-500 hover:text-black text-base font-normal">
                                Produk Simpanan
                            </h1>
                        </a>
                    </li>

                    <li class="p-4 mx-2 ml-16 md:ml-14">
                        <a href="{{ route('savingsAccount') }}">
                            <h1 class="text-gray-500 hover:text-black text-base font-normal">
                                Rekening Simpanan
                            </h1>
                        </a>
                    </li>

                    <li class="p-4 mx-2 ml-16 md:ml-14">
                        <a href="{{ route('savingsTransaction') }}">
                            <h1 class="text-gray-500 hover:text-black text-base font-normal">
                                Setor & Tarik
                            </h1>
                        </a>
                    </li>

                    @if (auth()->user()->hasAccess('savings', 'operate'))
                        <li class="p-4 mx-2 ml-16 md:ml-14">
                            <a href="{{ route('memberActivation') }}">
                                <h1 class="text-gray-500 hover:text-black text-base font-normal">
                                    Aktivasi Anggota
                                </h1>
                            </a>
                        </li>
                    @endif
                @endif

                @if (auth()->user()->hasAccess('cash'))
                    <!-- Cash -->
                    <li class="p-4 mx-2">
                        <div class="flex space-x-4">
                            <div class="bg-sky-600 p-2 rounded-xl">
                                <i class="material-icons text-white">payments</i>
                            </div>
                            <div class="my-auto">
                                <h1 class="text-black text-base font-normal">
                                    Kas
                                </h1>
                            </div>
                        </div>
                    </li>

                    <hr class="mx-5 shadow-2xl text-gray-100 rounded-xl" />

                    <li class="p-4 mx-2 ml-16 md:ml-14">
                        <a href="{{ route('cashSession') }}">
                            <h1 class="text-gray-500 hover:text-black text-base font-normal">
                                Sesi Kas Teller
                            </h1>
                        </a>
                    </li>

                    <li class="p-4 mx-2 ml-16 md:ml-14">
                        <a href="{{ route('cashTransfer') }}">
                            <h1 class="text-gray-500 hover:text-black text-base font-normal">
                                Brankas & Perpindahan Kas
                            </h1>
                        </a>
                    </li>

                    <li class="p-4 mx-2 ml-16 md:ml-14">
                        <a href="{{ route('cashPosition') }}">
                            <h1 class="text-gray-500 hover:text-black text-base font-normal">
                                Posisi Kas
                            </h1>
                        </a>
                    </li>
                @endif

                @if (auth()->user()->hasAccess('accounting'))
                    <!-- Accounting -->
                    <li class="p-4 mx-2">
                        <div class="flex space-x-4">
                            <div class="bg-sky-600 p-2 rounded-xl">
                                <i class="material-icons text-white">account_balance</i>
                            </div>
                            <div class="my-auto">
                                <h1 class="text-black text-base font-normal">
                                    Akuntansi
                                </h1>
                            </div>
                        </div>
                    </li>

                    <hr class="mx-5 shadow-2xl text-gray-100 rounded-xl" />

                    <li class="p-4 mx-2 ml-16 md:ml-14">
                        <a href="{{ route('journalMockup') }}">
                            <h1 class="text-gray-500 hover:text-black text-base font-normal">
                                Jurnal
                            </h1>
                        </a>
                    </li>

                    <li class="p-4 mx-2 ml-16 md:ml-14">
                        <a href="{{ route('generalLedgerMockup') }}">
                            <h1 class="text-gray-500 hover:text-black text-base font-normal">
                                Buku Besar
                            </h1>
                        </a>
                    </li>

                    <li class="p-4 mx-2 ml-16 md:ml-14">
                        <a href="{{ route('trialBalanceMockup') }}">
                            <h1 class="text-gray-500 hover:text-black text-base font-normal">
                                Neraca Saldo
                            </h1>
                        </a>
                    </li>

                    <li class="p-4 mx-2 ml-16 md:ml-14">
                        <a href="{{ route('account') }}">
                            <h1 class="text-gray-500 hover:text-black text-base font-normal">
                                Bagan Akun
                            </h1>
                        </a>
                    </li>

                    <li class="p-4 mx-2 ml-16 md:ml-14">
                        <a href="{{ route('journalTemplate') }}">
                            <h1 class="text-gray-500 hover:text-black text-base font-normal">
                                Template Jurnal
                            </h1>
                        </a>
                    </li>
                @endif

                <!-- Logout -->
                <li class="p-4 mx-2">
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <div class="flex space-x-4">
                            <div class="bg-sky-600 p-2 rounded-xl">
                                <i class="material-icons rotate-180 text-white">logout</i>
                            </div>
                            <button class="text-gray-500 hover:text-black text-base font-normal" type="submit">
                                Keluar
                            </button>
                        </div>
                    </form>
                </li>
            </ul>
        </div>
    </aside>
</div>
