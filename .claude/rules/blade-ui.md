---
paths:
  - "resources/views/**"
  - "resources/css/**"
  - "resources/js/**"
  - "public/modal/**"
---

# Konvensi Blade dan UI

Kelas Tailwind dan warna yang persis ada di ui-design.md.

## File dan komponen
- Nama view camelCase di folder domain: `{domain}/{resource}.blade.php` (daftar), `{resource}Show` (detail), `{resource}Create` (form halaman penuh).
- Modal selalu berpasangan dan dipisah per domain: `resources/views/{domain}/modal/{resource}Add.blade.php` dan `{resource}Edit.blade.php`, dimuat dengan `@include('member.modal.memberAdd')`.
- Partial di folder `layout/` (`head`, `sidebar`, `navbar`, `loading`). Komponen anonim di `components/`. `<x-button>` menerima `type`, `variant` (primary|secondary|success|danger|warning|rose|purple|yellow), `size` (sm|md|lg), `icon` (nama Font Awesome tanpa `fa-`), `iconPosition`, `href` (dirender sebagai `<a>`), `disabled`.
- Semua teks UI berbahasa Indonesia dengan `<html lang="id">`. Komentar penanda bagian berbahasa Inggris: `<!-- Header -->`, `<!-- Table -->`, `<!-- SCRIPTS -->`, `<!-- Modals -->`.

## Kerangka halaman
- Tidak memakai `@extends`/`@section`. Setiap halaman adalah dokumen HTML utuh yang menyusun partial:
```blade
<head><title>Data Anggota</title> @include('layout.head') </head>
<body class="bg-gray-50 font-sans"> @include('layout.sidebar')
<main class="md:ml-64 xl:ml-72 2xl:ml-72"> @include('layout.navbar') <div class="p-6 space-y-6">
```
- Header halaman: `<h1>` dengan ikon `fas fa-*`, subjudul, dan `<x-button id="addBtn" icon="plus">Tambah</x-button>`. Material Icons hanya dipakai di sidebar dan navbar.
- Urutan akhir `<body>`: jQuery, DataTables, `<script src="{{ asset('modal/member.js') }}">`, `@include` modal Add lalu Edit, `@include('sweetalert::alert')`, `@include('layout.loading')`.

## Memuat JS dan CSS
- `layout.head` memuat font/ikon dari CDN lalu `@vite('resources/css/app.css')`; `resources/js/app.js` tidak dimuat. `app.css` hanya `@import 'tailwindcss';`, `@source`, `@theme`.
- Di `<head>`: `<link href="//cdn.datatables.net/2.0.2/css/dataTables.dataTables.min.css" rel="stylesheet" />` dan `<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>`.
- Di akhir body: `https://code.jquery.com/jquery-3.7.1.min.js` lalu `//cdn.datatables.net/2.0.2/js/dataTables.min.js`.
- Override gaya DataTables ditulis di `<style>` halaman dengan class versi 2 (`.dt-container .dt-search input`, `.dt-length select`); selector `.dataTables_wrapper` milik versi 1.x dan tidak berpengaruh.
- JS per halaman di `public/modal/{resource}.js` (jQuery dalam `$(document).ready`), dimuat lewat `asset()`, bukan Vite.

## Tabel, flash, dan format
- `<table id="myTable" class="w-full text-left">`, kolom pertama `No` (`@php $no = 1; @endphp` lalu `{{ $no++ }}`), kolom terakhir `Aksi`. Init: `if ($('#myTable').length) { new DataTable('#myTable', {}); }`
- Tombol edit `<button class="editBtn" data-id="…" data-name="…">`. Hapus berupa `<form class="inline deleteForm">` dengan `@csrf @method('delete')` dan `<button type="button" class="delete-confirm">`, dikonfirmasi `Swal.fire({ title: 'Hapus?', icon: 'warning', showCancelButton: true, confirmButtonText: 'Ya, hapus!' })` lalu submit jika `result.isConfirmed`.
- Flash `success` dan error validasi hanya tampil sebagai SweetAlert lewat `@include('sweetalert::alert')`. Tidak ada banner `session('success')` manual dan tidak ada `@error` di form.
- Uang: `Rp {{ number_format($amount, 0, ',', '.') }}` (dengan spasi). Tanggal bisnis `->format('d M Y')`, metadata `Dibuat: {{ $item->created_at->format('Y-m-d') }}`, waktu log `d M Y, H:i`, tanggal di header dashboard `now()->translatedFormat('l, d F Y')`.

## Modal dan form
- Root modal `<div id="addModal" class="hidden fixed inset-0 bg-gray-900/60 backdrop-blur-sm flex items-center justify-center z-50 overflow-y-auto px-4 py-6">` (edit: `id="editModal"`). Buka/tutup dengan toggle class `hidden` lewat `#addBtn`, `#closeAddModal`, `#closeModal`, atau klik backdrop.
- Form sederhana: kartu `bg-white rounded-2xl p-8 w-full max-w-lg shadow-2xl relative`, tombol × `absolute top-5 right-5`, judul `<h2 class="text-2xl font-bold mb-6 text-gray-800">`.
- Form panjang (banyak bagian atau peta): kartu `bg-white rounded-2xl p-0 w-full max-w-2xl shadow-2xl relative my-5 flex flex-col max-h-[90vh]`, header `p-6 border-b border-gray-100 bg-gray-50 rounded-t-2xl sticky top-0 z-10`, body `p-6 overflow-y-auto flex-grow`.
- Form tambah memakai `action="{{ route('postMember') }}"`. Form edit `id="editForm"` tanpa action, input `id="edit{Field}"`; action ditulis literal di JS, bukan dari `route()`:
```js
$(document).on('click', '.editBtn', function () {
    const btn = $(this);
    $('#editName').val(btn.data('name'));
    $('#editForm').attr('action', `/savings-account/${btn.data('id')}/update`);
    $('#editModal').removeClass('hidden');
});
```
- Label `block text-sm font-semibold text-gray-700 mb-1`; input `w-full rounded-lg border-gray-300 shadow-sm p-2.5 border focus:ring-2 focus:ring-{aksen}-500`. Submit `<x-button type="submit" icon="save" class="w-full justify-center">Simpan</x-button>`, di modal edit `Perbarui`.
- Input uang `<input type="text" class="currency">`: JS memberi titik ribuan saat mengetik dan membuang titik saat submit. `layout.loading` menonaktifkan tombol submit dan menampilkan spinner; form pencarian memakai atribut `data-page-loading`.

## Konflik (CLAUDE.md berlaku)
- Referensi: `chart.js` tanpa versi. Berlaku: Chart.js 4.4.0 dengan versi dikunci di URL.
- Referensi: semua view dan modal di root `resources/views/`. Berlaku: `resources/views/{domain}/` dan `{domain}/modal/`.
