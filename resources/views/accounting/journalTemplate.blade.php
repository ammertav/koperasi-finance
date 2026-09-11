<!DOCTYPE html>
<html lang="id">

<head>
    <title>Template Jurnal</title>
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
                        <i class="fas fa-project-diagram text-indigo-600"></i> Template Jurnal
                    </h1>
                    <p class="text-sm text-gray-500 mt-1">Aturan pembentukan jurnal otomatis per jenis transaksi dan produk (hanya lihat)</p>
                </div>
            </div>

            <!-- Info -->
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-center">
                <p class="text-yellow-800 font-semibold">Bagian akuntansi tidak menginput ulang transaksi (ATR-02)</p>
                <p class="text-sm text-yellow-600 mt-1">
                    Setiap transaksi memilih template sesuai jenis dan produknya. Jika transaksi dilakukan di kantor selain
                    kantor pemilik rekening, baris "Kantor pemilik/penerima" dibukukan di kantor tersebut dan jurnal RAK
                    terbentuk otomatis di kedua kantor.
                </p>
            </div>

            <!-- Templates -->
            <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                @foreach ($journalTemplates as $template)
                    <div class="bg-white rounded-xl shadow-md border border-gray-100 p-6 space-y-4">
                        <div class="flex justify-between items-start gap-4">
                            <div>
                                <h2 class="font-bold text-gray-900 text-base">{{ $template->name }}</h2>
                                <p class="text-xs text-gray-400 font-mono">{{ $template->code }}</p>
                            </div>
                            <div class="flex flex-wrap gap-2 justify-end">
                                <span
                                    class="bg-indigo-100 text-indigo-700 border-indigo-200 text-xs px-3 py-1 rounded-full font-bold border whitespace-nowrap">
                                    {{ $template->transaction_type_label }}
                                </span>
                                @if ($template->product_code)
                                    <span
                                        class="bg-gray-100 text-gray-700 border-gray-200 text-xs px-3 py-1 rounded-full font-bold border">
                                        Produk {{ $template->product_code }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="overflow-auto">
                            <table class="w-full text-left">
                                <thead class="bg-gray-100 text-gray-600 text-xs leading-normal">
                                    <tr>
                                        <th class="p-3 font-bold rounded-tl-lg">Akun</th>
                                        <th class="p-3 font-bold">Nominal</th>
                                        <th class="p-3 font-bold rounded-tr-lg">Dibukukan di</th>
                                    </tr>
                                </thead>
                                <tbody class="text-gray-700 text-sm">
                                    @foreach ($template->lines as $line)
                                        <tr class="border-b border-gray-50">
                                            <td class="p-3 {{ $line->side === 'credit' ? 'pl-10' : '' }}">
                                                <span class="text-xs font-bold {{ $line->side === 'debit' ? 'text-blue-600' : 'text-emerald-600' }}">
                                                    {{ $line->side === 'debit' ? 'D' : 'K' }}
                                                </span>
                                                <span class="font-mono text-xs text-gray-400">{{ $line->account->code }}</span>
                                                {{ $line->account->name }}
                                            </td>
                                            <td class="p-3 text-xs">
                                                {{ \App\Models\JournalTemplateLine::AMOUNT_KEYS[$line->amount_key] ?? $line->amount_key }}
                                            </td>
                                            <td class="p-3 text-xs">
                                                {{ \App\Models\JournalTemplateLine::OFFICE_ROLES[$line->office_role] }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach
            </div>

        </div>
    </main>

    <!-- SCRIPTS -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    @include('sweetalert::alert')
    @include('layout.loading')
</body>

</html>
