@props([
    'action',
    'offices',
    'selectedOffice' => null,
    'startDate',
    'endDate',
])

<!-- Filter -->
<form method="GET" action="{{ $action }}" data-page-loading
    class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-4 items-end">
    {{ $slot }}

    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1">Kantor</label>
        <select name="office_id"
            class="w-full rounded-lg border-gray-300 shadow-sm p-2.5 border focus:ring-2 focus:ring-indigo-500">
            @if (auth()->user()->canAccessAllOffices())
                <option value="">Semua kantor (konsolidasi)</option>
            @endif
            @foreach ($offices as $office)
                <option value="{{ $office->id }}" @selected($selectedOffice?->id === $office->id)>
                    {{ $office->code }} — {{ $office->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1">Dari Tanggal</label>
        <input type="date" name="start_date" value="{{ $startDate->toDateString() }}"
            class="w-full rounded-lg border-gray-300 shadow-sm p-2.5 border focus:ring-2 focus:ring-indigo-500">
    </div>

    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1">Sampai Tanggal</label>
        <input type="date" name="end_date" value="{{ $endDate->toDateString() }}"
            class="w-full rounded-lg border-gray-300 shadow-sm p-2.5 border focus:ring-2 focus:ring-indigo-500">
    </div>

    <div>
        <x-button type="submit" size="md" variant="primary" icon="search" class="w-full justify-center">Tampilkan</x-button>
    </div>
</form>
