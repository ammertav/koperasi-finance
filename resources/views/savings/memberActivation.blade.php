<!DOCTYPE html>
<html lang="id">

<head>
    <title>Aktivasi Anggota</title>
    @include('layout.head')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-gray-50 font-sans">
    @include('layout.sidebar')

    <main class="md:ml-64 xl:ml-72 2xl:ml-72">
        @include('layout.navbar')
        <div class="p-6 space-y-6">

            <!-- Header -->
            <div
                class="md:flex justify-between items-center bg-white p-5 rounded-xl shadow-sm border border-gray-100 space-y-2 md:space-y-0">
                <div>
                    <h1 class="font-bold text-2xl text-gray-800 flex items-center gap-2">
                        <i class="fas fa-user-check text-emerald-600"></i> Aktivasi Anggota
                    </h1>
                    <p class="text-sm text-gray-500 mt-1">
                        Calon anggota yang disetujui kepala cabang menyetor simpanan pokok Rp {{ number_format($principalSavings, 0, ',', '.') }}
                    </p>
                </div>
            </div>

            @unless ($cashSession)
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-center">
                    <p class="text-yellow-800 font-semibold">Sesi kas teller belum dibuka</p>
                    <p class="text-sm text-yellow-600 mt-1">Setoran simpanan pokok diterima teller setelah <a href="{{ route('cashSession') }}" class="font-bold underline">sesi kas dibuka</a>.</p>
                </div>
            @endunless

            <!-- Table -->
            <div class="w-full bg-white rounded-xl shadow-md border border-gray-100">
                <div class="p-5 overflow-auto">
                    @if ($members === [])
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-center">
                            <p class="text-yellow-800 font-semibold">Tidak ada calon anggota yang menunggu setoran pokok</p>
                        </div>
                    @else
                        <table class="w-full text-left">
                            <thead class="bg-gray-100 text-gray-600 text-sm leading-normal">
                                <tr>
                                    <th class="p-4 font-bold rounded-tl-lg text-center" width="5%">No</th>
                                    <th class="p-4 font-bold">Calon Anggota</th>
                                    <th class="p-4 font-bold">Tgl Daftar</th>
                                    <th class="p-4 font-bold">Disetujui</th>
                                    <th class="p-4 font-bold text-right">Setoran Pokok</th>
                                    <th class="p-4 font-bold text-center rounded-tr-lg" width="15%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-700 text-sm">
                                @php $no = 1; @endphp
                                @foreach ($members as $item)
                                    <tr class="hover:bg-gray-50 transition duration-150">
                                        <td class="p-4 font-medium text-center">{{ $no++ }}</td>
                                        <td class="p-4 space-y-1">
                                            <a href="{{ route('detailMember', $item['id']) }}" class="font-bold text-gray-900 text-base hover:text-emerald-700">{{ $item['name'] }}</a>
                                            <div class="text-xs text-gray-400 font-mono">NIK {{ $item['nik_masked'] }}</div>
                                        </td>
                                        <td class="p-4 whitespace-nowrap">{{ $item['registration_date']->format('d M Y') }}</td>
                                        <td class="p-4 space-y-1">
                                            <div>{{ $item['approved_by_name'] }}</div>
                                            <div class="text-xs text-gray-400">{{ $item['approved_at']->format('d M Y, H:i') }}</div>
                                        </td>
                                        <td class="p-4 text-right whitespace-nowrap font-semibold">Rp {{ number_format($principalSavings, 0, ',', '.') }}</td>
                                        <td class="p-4">
                                            <form action="{{ route('activateMember', $item['id']) }}" method="POST" class="activateForm flex justify-center"
                                                data-name="{{ $item['name'] }}">
                                                @csrf
                                                <x-button type="button" variant="success" size="md" icon="money-bill-wave" class="activate-confirm" :disabled="! $cashSession">
                                                    Terima Setoran
                                                </x-button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>

        </div>
    </main>

    <!-- SCRIPTS -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="{{ asset('modal/memberActivation.js') }}"></script>

    @include('sweetalert::alert')
    @include('layout.loading')
</body>

</html>
