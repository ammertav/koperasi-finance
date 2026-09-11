---
paths:
  - "resources/views/**"
  - "resources/css/**"
  - "public/modal/**"
---

# Desain visual (sama persis dengan referensi)

Tampilan, UX, dan warna meniru proyek referensi. Jangan mengganti kelas Tailwind di bawah dengan gaya lain.

## Kerangka
- Latar `<body class="bg-gray-50 font-sans">`; konten `<div class="p-6 space-y-6">` di dalam `<main class="md:ml-64 xl:ml-72 2xl:ml-72">`.
- Sidebar `<aside id="sidebar" class="font-poppins fixed inset-y-0 my-6 ml-4 w-full max-w-72 md:max-w-60 xl:max-w-64 2xl:max-w-64 z-50 rounded-lg bg-white overflow-y-auto transform transition-transform duration-300 -translate-x-full md:translate-x-0 ease-in-out shadow-xl">`, logo `asset('logo.svg')` di atas, pemisah `<hr class="mx-5 shadow-2xl text-gray-100 rounded-xl" />`.
- Grup menu `<li class="p-4 mx-2">`: ikon `<div class="bg-sky-600 p-2 rounded-xl"><i class="material-icons text-white">group</i></div>` + judul `text-black text-base font-normal`. Submenu `<li class="p-4 mx-2 ml-16 md:ml-14">` dengan teks `text-gray-500 hover:text-black text-base font-normal`. Tidak ada penanda menu aktif. Keluar berupa form POST di item terakhir.
- Navbar `<nav id="navbar" class="font-poppins mx-3 xl:mx-4 rounded-xl bg-white bg-opacity-90 sticky top-0 z-40">`, isi rata kanan: kotak cari (`material-icons` search, form `data-page-loading`), nama pengguna + ikon `person`, hamburger `md:hidden` yang men-toggle `-translate-x-full` pada `#sidebar`.
- Font mengikuti referensi apa adanya: `@theme { --font-sans: 'Instrument Sans', ... }`, Poppins dimuat di `layout.head`, kelas `font-poppins` dipasang di sidebar/navbar.

## Warna
- Warna merek `sky`: ikon grup sidebar `bg-sky-600`, latar login `bg-linear-to-b from-sky-800 to-gray-100`, tombol login `bg-sky-700 hover:bg-sky-800`.
- Satu aksen per modul, dipakai di ikon judul halaman (`text-{aksen}-600`), tombol Tambah, dan ikon judul modal tambah. Palet referensi: indigo (dashboard, karyawan, payroll, log), cyan (cabang), slate (jabatan, pengaturan), emerald (tunjangan), rose (potongan), yellow-500 (cuti), purple (lembur), teal (catatan), blue (shift).
- Pemetaan modul proyek ini: Organization cyan, pengguna/peran/pengaturan slate, Member indigo, Savings emerald, Loan purple, Cash teal, Accounting/laporan/audit/dashboard indigo, antar kantor (RAK) blue, tutup buku rose.
- Tombol Tambah memakai `variant` bila tersedia (`primary` = indigo, `rose`, `purple`, `yellow`); aksen lain di-override: `<x-button id="addBtn" size="lg" variant="primary" class="bg-cyan-600 hover:bg-cyan-700 shadow-md" icon="plus">Tambah</x-button>`.
- Submit modal tambah selalu `bg-slate-700 hover:bg-slate-800`; modal edit `bg-blue-600 hover:bg-blue-700` dengan ikon judul `fas fa-edit text-blue-600`.

## Komponen tampilan
- Kartu header dan KPI: `bg-white p-5 rounded-xl shadow-sm border border-gray-100`. Kartu tabel dan isi memakai `shadow-md`.
- Header halaman: `md:flex justify-between items-center … space-y-2 md:space-y-0`, judul `font-bold text-2xl text-gray-800` + ikon, subjudul `text-sm text-gray-500`.
- Tabel: pembungkus `w-full bg-white rounded-xl shadow-md border border-gray-100` > `p-5 overflow-auto`; `thead` `bg-gray-100 text-gray-600 text-sm leading-normal`; `th` `p-4 font-bold` (pertama `rounded-tl-lg text-center` width 5%, terakhir `rounded-tr-lg text-center` width 15%); `tbody` `text-gray-700 text-sm`; baris `hover:bg-gray-50 transition duration-150`; sel `p-4`. Nama utama `font-bold text-gray-900 text-base`, subteks `text-xs text-gray-400`.
- Tombol aksi baris: `w-10 h-10 flex items-center justify-center bg-blue-500 text-white rounded-lg shadow hover:bg-blue-600 hover:scale-105 transition` berisi `<i class="fas fa-edit text-lg">`; hapus sama dengan `bg-red-500 hover:bg-red-600` dan `fa-trash`.
- Badge status memakai `match`:
```blade
@php $color = match ($item->status) { 'posted' => 'bg-green-100 text-green-700 border-green-200',
    'pending_approval' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
    'cancelled' => 'bg-red-100 text-red-700 border-red-200', default => 'bg-gray-100 text-gray-700 border-gray-200' }; @endphp
<span class="{{ $color }} text-xs px-3 py-1 rounded-full font-bold border">Diposting</span>
```
- KPI dashboard: label `text-xs text-gray-500 uppercase tracking-wide`, nilai `text-2xl font-bold text-gray-800 mt-1`, tren `text-xs text-emerald-600 mt-2`. Varian berwarna: label `text-{c}-600 uppercase font-semibold tracking-wide`, nilai `text-3xl font-bold text-{c}-700 mt-2`, ikon kanan `text-4xl text-{c}-300 opacity-50`.
- Grafik: kartu `bg-white p-6 rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition`, judul `font-bold text-gray-800 text-lg` + `text-xs text-gray-500 mt-1`, `<canvas height="100">`, line chart `tension: 0.4`, legend `position: 'bottom'`, label uang `'Rp ' + value.toLocaleString('id-ID')`.
- Halaman detail: header berisi `<x-button :href="route('member')" variant="secondary" icon="arrow-left">Kembali</x-button>` dan tombol Edit `.editBtn`; kartu profil `shadow-md p-6` dengan avatar inisial `w-24 h-24 rounded-2xl bg-indigo-50 border border-indigo-100 text-3xl font-bold text-indigo-600`; kartu KPI dengan ikon `w-12 h-12 rounded-lg bg-{c}-50 text-{c}-600`.
- Tab: `<button data-tab="x" class="tab-btn px-4 py-3 text-sm font-semibold text-gray-500 border-b-2 border-transparent hover:text-indigo-600 transition whitespace-nowrap">`, isi `<div class="tab-content hidden" data-tab-content="x">`, gaya aktif di `<style>`: `.tab-btn.active { color: #4f46e5; border-color: #4f46e5; background-color: #eef2ff; }`. Judul bagian `text-sm font-bold text-indigo-600 uppercase tracking-wider mb-4 border-b pb-2`.
- Form halaman penuh: `<div class="p-6 flex justify-center">` > kartu `w-full max-w-2xl bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden`, header `bg-{aksen}-600 p-6` (judul putih, subjudul `text-{aksen}-100`), body `p-8`, footer `flex items-center justify-end gap-4 pt-6 border-t border-gray-100` berisi link Batal `px-6 py-3 text-gray-700 hover:bg-gray-100 rounded-lg font-bold` dan submit `px-8 py-3 bg-{aksen}-600 text-white rounded-lg shadow-lg font-bold`.
- Kotak kosong/peringatan: `bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-center` dengan teks `text-yellow-800 font-semibold`.
- Login: kartu `w-full max-w-sm bg-white rounded-2xl shadow-xl p-8 space-y-6` di tengah, input `w-full p-3 bg-gray-100 rounded-xl focus:ring-2 focus:ring-sky-600 outline-none`, toggle kata sandi `fas fa-eye`.

## Konflik
- BRIEF.md bagian "Tema visual" (hijau tua) dan sebutan "Topbar". Berlaku: keputusan Andreas, warna dan navbar sama dengan referensi.
