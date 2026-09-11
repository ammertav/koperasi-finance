<!DOCTYPE html>
<html lang="id">

<head>
    <title>Profil Anggota {{ $member['name'] }}</title>
    @include('layout.head')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .tab-btn.active {
            color: #4f46e5;
            border-color: #4f46e5;
            background-color: #eef2ff;
        }
    </style>
</head>

<body class="bg-gray-50 font-sans">
    @include('layout.sidebar')

    <main class="md:ml-64 xl:ml-72 2xl:ml-72">
        @include('layout.navbar')
        <div class="p-6 space-y-6">

            @php
                $rupiah = fn (int $amount) => 'Rp ' . number_format($amount, 0, ',', '.');
                $statusColor = match ($member['status']) {
                    'active' => 'bg-green-100 text-green-700 border-green-200',
                    'pending_approval' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
                    'approved' => 'bg-blue-100 text-blue-700 border-blue-200',
                    'rejected' => 'bg-red-100 text-red-700 border-red-200',
                    default => 'bg-gray-100 text-gray-700 border-gray-200',
                };
                $collectibilityColor = fn (?string $code) => match ($code) {
                    'current' => 'bg-green-100 text-green-700 border-green-200',
                    'substandard' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
                    'doubtful' => 'bg-orange-100 text-orange-700 border-orange-200',
                    'loss' => 'bg-red-100 text-red-700 border-red-200',
                    default => 'bg-gray-100 text-gray-700 border-gray-200',
                };
                $initials = collect(explode(' ', $member['name']))->filter()->take(2)->map(fn ($word) => mb_substr($word, 0, 1))->implode('');
                $membershipAge = $member['activation_date']?->diff(\Illuminate\Support\Carbon::parse($member['office_book_date']));
            @endphp

            <!-- Header -->
            <div
                class="md:flex justify-between items-center bg-white p-5 rounded-xl shadow-sm border border-gray-100 space-y-2 md:space-y-0">
                <div>
                    <h1 class="font-bold text-2xl text-gray-800 flex items-center gap-2">
                        <i class="fas fa-id-card text-indigo-600"></i> Profil Anggota
                    </h1>
                    <p class="text-sm text-gray-500 mt-1">Data diri, simpanan, pinjaman, dan jaminan dalam satu tampilan</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <x-button :href="route('member')" variant="secondary" icon="arrow-left">Kembali</x-button>
                    @if ($member['status'] === 'approved' && auth()->user()->hasAccess('savings', 'operate'))
                        <x-button :href="route('memberActivation')" variant="success" icon="money-bill-wave">Terima Setoran Pokok</x-button>
                    @endif
                    @if ($canApprove)
                        <x-button id="rejectBtn" variant="danger" icon="times">Tolak</x-button>
                        <x-button id="approveBtn" variant="success" icon="check">Setujui</x-button>
                    @endif
                </div>
            </div>

            <!-- Status Notice -->
            @if ($member['status'] === 'pending_approval')
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-center">
                    <p class="text-yellow-800 font-semibold">Menunggu persetujuan kepala cabang</p>
                    <p class="text-sm text-yellow-600 mt-1">Didaftarkan oleh {{ $member['registered_by_name'] }} pada
                        {{ $member['data_consent_at']->format('d M Y, H:i') }}. Pendaftar tidak dapat menyetujui pendaftarannya sendiri.</p>
                </div>
            @elseif ($member['status'] === 'approved')
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-center">
                    <p class="text-blue-800 font-semibold">Menunggu setoran simpanan pokok {{ $rupiah($principalSavings) }} di teller</p>
                    <p class="text-sm text-blue-600 mt-1">Disetujui oleh {{ $member['approved_by_name'] }}. Nomor anggota dan rekening simpanan
                        pokok dan wajib terbit setelah setoran diterima.</p>
                </div>
            @elseif ($member['status'] === 'rejected')
                <div class="bg-red-50 border border-red-200 rounded-lg p-4 text-center">
                    <p class="text-red-800 font-semibold">Pendaftaran ditolak</p>
                    <p class="text-sm text-red-600 mt-1">{{ $member['rejection_reason'] }} — {{ $member['rejected_by_name'] }}</p>
                </div>
            @endif

            <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
                <!-- Profile Card -->
                <div class="bg-white rounded-xl shadow-md border border-gray-100 p-6">
                    <div class="flex items-center gap-4">
                        <div
                            class="w-24 h-24 shrink-0 rounded-2xl bg-indigo-50 border border-indigo-100 text-3xl font-bold text-indigo-600 flex items-center justify-center">
                            {{ $initials }}
                        </div>
                        <div class="space-y-2">
                            <h2 class="text-xl font-bold text-gray-900">{{ $member['name'] }}</h2>
                            <p class="font-mono text-sm text-gray-500">{{ $member['number'] ?? 'Nomor anggota belum terbit' }}</p>
                            <span class="{{ $statusColor }} text-xs px-3 py-1 rounded-full font-bold border">{{ $member['status_label'] }}</span>
                        </div>
                    </div>
                    <dl class="mt-6 space-y-3 text-sm">
                        <div class="flex justify-between gap-4">
                            <dt class="text-gray-500">NIK</dt>
                            <dd class="font-mono font-semibold text-gray-800">{{ $member['nik'] }}</dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-gray-500">Kantor</dt>
                            <dd class="font-semibold text-gray-800 text-right">{{ $member['office_code'] }} — {{ $member['office_name'] }}</dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-gray-500">Tanggal Daftar</dt>
                            <dd class="font-semibold text-gray-800">{{ $member['registration_date']->format('d M Y') }}</dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-gray-500">Tanggal Aktif</dt>
                            <dd class="font-semibold text-gray-800">{{ $member['activation_date']?->format('d M Y') ?? '-' }}</dd>
                        </div>
                    </dl>
                </div>

                <!-- KPI Cards -->
                <div class="xl:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 flex items-center gap-4">
                        <div class="w-12 h-12 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">
                            <i class="fas fa-piggy-bank"></i>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wide">Total Simpanan</p>
                            <h3 class="text-2xl font-bold text-gray-800 mt-1">{{ $rupiah($summary['total_savings']) }}</h3>
                            <p class="text-xs text-gray-400 mt-1">{{ count($savingsAccounts) }} rekening</p>
                        </div>
                    </div>
                    <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 flex items-center gap-4">
                        <div class="w-12 h-12 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center text-xl">
                            <i class="fas fa-hand-holding-usd"></i>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wide">Sisa Pokok Pinjaman</p>
                            <h3 class="text-2xl font-bold text-gray-800 mt-1">{{ $rupiah($summary['outstanding_principal']) }}</h3>
                            <p class="text-xs text-gray-400 mt-1">{{ $summary['active_loan_count'] }} pinjaman aktif</p>
                        </div>
                    </div>
                    <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 flex items-center gap-4">
                        <div class="w-12 h-12 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-xl">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wide">Kolektibilitas</p>
                            <div class="mt-2">
                                @if ($summary['collectibility'])
                                    <span class="{{ $collectibilityColor($summary['collectibility']) }} text-sm px-3 py-1 rounded-full font-bold border">
                                        {{ \App\Mockups\LoanMockup::COLLECTIBILITIES[$summary['collectibility']] }}
                                    </span>
                                @else
                                    <span class="text-sm text-gray-400">Tidak ada pinjaman aktif</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 flex items-center gap-4">
                        <div class="w-12 h-12 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wide">Lama Keanggotaan</p>
                            <h3 class="text-2xl font-bold text-gray-800 mt-1">
                                {{ $membershipAge ? ($membershipAge->y ? $membershipAge->y . ' thn ' : '') . $membershipAge->m . ' bln' : '-' }}
                            </h3>
                            <p class="text-xs text-gray-400 mt-1">Sejak aktivasi</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabs -->
            <div class="bg-white rounded-xl shadow-md border border-gray-100">
                <div class="flex overflow-x-auto border-b border-gray-100 px-2">
                    <button data-tab="profile" class="tab-btn active px-4 py-3 text-sm font-semibold text-gray-500 border-b-2 border-transparent hover:text-indigo-600 transition whitespace-nowrap">Data Diri</button>
                    <button data-tab="savings" class="tab-btn px-4 py-3 text-sm font-semibold text-gray-500 border-b-2 border-transparent hover:text-indigo-600 transition whitespace-nowrap">Simpanan</button>
                    <button data-tab="loans" class="tab-btn px-4 py-3 text-sm font-semibold text-gray-500 border-b-2 border-transparent hover:text-indigo-600 transition whitespace-nowrap">Pinjaman</button>
                    <button data-tab="collaterals" class="tab-btn px-4 py-3 text-sm font-semibold text-gray-500 border-b-2 border-transparent hover:text-indigo-600 transition whitespace-nowrap">Jaminan</button>
                    <button data-tab="history" class="tab-btn px-4 py-3 text-sm font-semibold text-gray-500 border-b-2 border-transparent hover:text-indigo-600 transition whitespace-nowrap">Riwayat</button>
                </div>

                <div class="p-6">
                    <!-- Profile -->
                    <div class="tab-content" data-tab-content="profile">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                            <div>
                                <h3 class="text-sm font-bold text-indigo-600 uppercase tracking-wider mb-4 border-b pb-2">Identitas</h3>
                                <dl class="space-y-3 text-sm">
                                    <div class="flex justify-between gap-4"><dt class="text-gray-500">Tempat, Tanggal Lahir</dt><dd class="font-semibold text-gray-800 text-right">{{ $member['birth_place'] }}, {{ $member['birth_date']->format('d M Y') }}</dd></div>
                                    <div class="flex justify-between gap-4"><dt class="text-gray-500">Jenis Kelamin</dt><dd class="font-semibold text-gray-800">{{ $member['gender_label'] }}</dd></div>
                                    <div class="flex justify-between gap-4"><dt class="text-gray-500">Alamat</dt><dd class="font-semibold text-gray-800 text-right">{{ $member['address'] }}</dd></div>
                                    <div class="flex justify-between gap-4"><dt class="text-gray-500">No. Telepon</dt><dd class="font-semibold text-gray-800">{{ $member['phone'] }}</dd></div>
                                </dl>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-indigo-600 uppercase tracking-wider mb-4 border-b pb-2">Pekerjaan dan Ahli Waris</h3>
                                <dl class="space-y-3 text-sm">
                                    <div class="flex justify-between gap-4"><dt class="text-gray-500">Pekerjaan</dt><dd class="font-semibold text-gray-800">{{ $member['occupation'] }}</dd></div>
                                    <div class="flex justify-between gap-4"><dt class="text-gray-500">Penghasilan per Bulan</dt><dd class="font-semibold text-gray-800">{{ $rupiah($member['monthly_income']) }}</dd></div>
                                    <div class="flex justify-between gap-4"><dt class="text-gray-500">Ahli Waris</dt><dd class="font-semibold text-gray-800 text-right">{{ $member['heir_name'] }} ({{ $member['heir_relationship'] }})</dd></div>
                                    <div class="flex justify-between gap-4"><dt class="text-gray-500">Telepon Ahli Waris</dt><dd class="font-semibold text-gray-800">{{ $member['heir_phone'] ?? '-' }}</dd></div>
                                </dl>
                            </div>
                            <div class="lg:col-span-2">
                                <h3 class="text-sm font-bold text-indigo-600 uppercase tracking-wider mb-4 border-b pb-2">Dokumen dan Persetujuan</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="p-4 rounded-lg border border-gray-200 flex items-center gap-3">
                                        <i class="fas fa-id-card text-2xl text-indigo-400"></i>
                                        <div>
                                            <p class="font-semibold text-gray-800 text-sm">Foto KTP</p>
                                            <p class="text-xs text-gray-400">{{ $member['has_ktp_photo'] ? 'Terunggah saat registrasi' : 'Belum diunggah' }}</p>
                                        </div>
                                    </div>
                                    <div class="p-4 rounded-lg border border-gray-200 flex items-center gap-3">
                                        <i class="fas fa-user-shield text-2xl text-emerald-500"></i>
                                        <div>
                                            <p class="font-semibold text-gray-800 text-sm">Persetujuan pemrosesan data pribadi</p>
                                            <p class="text-xs text-gray-400">Dicatat {{ $member['data_consent_at']->format('d M Y, H:i') }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Savings -->
                    <div class="tab-content hidden" data-tab-content="savings">
                        @if ($savingsAccounts === [])
                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-center">
                                <p class="text-yellow-800 font-semibold">Belum ada rekening simpanan</p>
                                <p class="text-sm text-yellow-600 mt-1">Rekening simpanan pokok dan wajib dibuka otomatis saat anggota aktif.</p>
                            </div>
                        @else
                            <div class="overflow-auto">
                                <table class="w-full text-left">
                                    <thead class="bg-gray-100 text-gray-600 text-sm leading-normal">
                                        <tr>
                                            <th class="p-4 font-bold rounded-tl-lg text-center" width="5%">No</th>
                                            <th class="p-4 font-bold">Rekening</th>
                                            <th class="p-4 font-bold">Dibuka</th>
                                            <th class="p-4 font-bold">Transaksi Terakhir</th>
                                            <th class="p-4 font-bold text-right rounded-tr-lg">Saldo</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-gray-700 text-sm">
                                        @php $no = 1; @endphp
                                        @foreach ($savingsAccounts as $account)
                                            <tr class="hover:bg-gray-50 transition duration-150">
                                                <td class="p-4 font-medium text-center">{{ $no++ }}</td>
                                                <td class="p-4 space-y-1">
                                                    <div class="font-bold text-gray-900">{{ $account['product_name'] }}</div>
                                                    <div class="text-xs text-gray-400 font-mono">{{ $account['number'] }}</div>
                                                </td>
                                                <td class="p-4 whitespace-nowrap">{{ $account['opened_date']->format('d M Y') }}</td>
                                                <td class="p-4 whitespace-nowrap">{{ $account['last_transaction_date']->format('d M Y') }}</td>
                                                <td class="p-4 text-right whitespace-nowrap font-semibold">{{ $rupiah($account['balance']) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="text-sm font-bold text-gray-800 bg-gray-50">
                                        <tr>
                                            <td class="p-4" colspan="4">Total</td>
                                            <td class="p-4 text-right whitespace-nowrap">{{ $rupiah($summary['total_savings']) }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        @endif
                    </div>

                    <!-- Loans -->
                    <div class="tab-content hidden" data-tab-content="loans">
                        @if ($loans === [])
                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-center">
                                <p class="text-yellow-800 font-semibold">Belum ada pinjaman</p>
                            </div>
                        @else
                            <div class="overflow-auto">
                                <table class="w-full text-left">
                                    <thead class="bg-gray-100 text-gray-600 text-sm leading-normal">
                                        <tr>
                                            <th class="p-4 font-bold rounded-tl-lg text-center" width="5%">No</th>
                                            <th class="p-4 font-bold">Pinjaman</th>
                                            <th class="p-4 font-bold">Pencairan</th>
                                            <th class="p-4 font-bold text-right">Plafon</th>
                                            <th class="p-4 font-bold text-center">Angsuran</th>
                                            <th class="p-4 font-bold text-right">Sisa Pokok</th>
                                            <th class="p-4 font-bold text-center">Tunggakan</th>
                                            <th class="p-4 font-bold text-center rounded-tr-lg">Kolektibilitas</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-gray-700 text-sm">
                                        @php $no = 1; @endphp
                                        @foreach ($loans as $loan)
                                            <tr class="hover:bg-gray-50 transition duration-150">
                                                <td class="p-4 font-medium text-center">{{ $no++ }}</td>
                                                <td class="p-4 space-y-1">
                                                    <div class="font-bold text-gray-900">{{ $loan['product_name'] }}</div>
                                                    <div class="text-xs text-gray-400 font-mono">{{ $loan['number'] }} · {{ $loan['status_label'] }}</div>
                                                </td>
                                                <td class="p-4 whitespace-nowrap">{{ $loan['disbursement_date']->format('d M Y') }}</td>
                                                <td class="p-4 text-right whitespace-nowrap">{{ $rupiah($loan['principal']) }}</td>
                                                <td class="p-4 text-center whitespace-nowrap">
                                                    <div class="font-semibold">{{ $rupiah($loan['installment_amount']) }}</div>
                                                    <div class="text-xs text-gray-400">{{ $loan['paid_installments'] }}/{{ $loan['tenor_months'] }} bulan</div>
                                                </td>
                                                <td class="p-4 text-right whitespace-nowrap font-semibold">{{ $rupiah($loan['outstanding_principal']) }}</td>
                                                <td class="p-4 text-center whitespace-nowrap">{{ $loan['days_past_due'] > 0 ? $loan['days_past_due'] . ' hari' : '-' }}</td>
                                                <td class="p-4 text-center">
                                                    <span class="{{ $collectibilityColor($loan['collectibility']) }} text-xs px-3 py-1 rounded-full font-bold border whitespace-nowrap">
                                                        {{ $loan['collectibility_label'] }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>

                    <!-- Collaterals -->
                    <div class="tab-content hidden" data-tab-content="collaterals">
                        @if ($collaterals === [])
                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-center">
                                <p class="text-yellow-800 font-semibold">Tidak ada jaminan tercatat</p>
                            </div>
                        @else
                            <div class="overflow-auto">
                                <table class="w-full text-left">
                                    <thead class="bg-gray-100 text-gray-600 text-sm leading-normal">
                                        <tr>
                                            <th class="p-4 font-bold rounded-tl-lg text-center" width="5%">No</th>
                                            <th class="p-4 font-bold">Jaminan</th>
                                            <th class="p-4 font-bold">Pinjaman</th>
                                            <th class="p-4 font-bold text-right">Nilai Taksiran</th>
                                            <th class="p-4 font-bold text-center rounded-tr-lg">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-gray-700 text-sm">
                                        @php $no = 1; @endphp
                                        @foreach ($collaterals as $loan)
                                            <tr class="hover:bg-gray-50 transition duration-150">
                                                <td class="p-4 font-medium text-center">{{ $no++ }}</td>
                                                <td class="p-4 space-y-1">
                                                    <div class="font-bold text-gray-900">{{ $loan['collateral']['type'] }}</div>
                                                    <div class="text-xs text-gray-400">{{ $loan['collateral']['description'] }}</div>
                                                </td>
                                                <td class="p-4 font-mono">{{ $loan['number'] }}</td>
                                                <td class="p-4 text-right whitespace-nowrap">{{ $rupiah($loan['collateral']['estimated_value']) }}</td>
                                                <td class="p-4 text-center">
                                                    <span class="{{ $loan['collateral']['status'] === 'held' ? 'bg-blue-100 text-blue-700 border-blue-200' : 'bg-gray-100 text-gray-700 border-gray-200' }} text-xs px-3 py-1 rounded-full font-bold border whitespace-nowrap">
                                                        {{ $loan['collateral']['status_label'] }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>

                    <!-- History -->
                    <div class="tab-content hidden" data-tab-content="history">
                        <ol class="space-y-5">
                            @foreach ($timeline as $event)
                                <li class="flex gap-4">
                                    <div class="w-10 h-10 shrink-0 rounded-full {{ $event['color'] }} flex items-center justify-center">
                                        <i class="fas fa-{{ $event['icon'] }}"></i>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-800">{{ $event['title'] }}</p>
                                        <p class="text-sm text-gray-500">{{ $event['description'] }}</p>
                                        <p class="text-xs text-gray-400 mt-1">{{ $event['date']->format('d M Y') }}</p>
                                    </div>
                                </li>
                            @endforeach
                        </ol>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <!-- SCRIPTS -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="{{ asset('modal/memberShow.js') }}"></script>

    <!-- Modals -->
    @if ($canApprove)
        <form id="approveForm" action="{{ route('approveMember', $member['id']) }}" method="POST" class="hidden">
            @csrf
        </form>
        @include('member.modal.memberReject')
    @endif

    @include('sweetalert::alert')
    @include('layout.loading')
</body>

</html>
