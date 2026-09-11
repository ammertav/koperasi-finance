# Panduan Full MVC — KSP Multi Cabang

Dokumen ini menyimpan panduan pembangunan full MVC (evolusioner, siap produksi) yang berlaku sampai 11 Sep 2026. Sejak tanggal itu prototype dilanjutkan sebagai **prototype tampilan** (lihat CLAUDE.md dan BRIEF.md). File ini tidak dimuat otomatis. Baca jika:

- mengubah kode nyata Milestone 0–1, atau
- Andreas meminta melanjutkan pembangunan ke full MVC.

## Kondisi kode saat berpindah mode

**Nyata (full MVC, ber-test):**
- **M0 Fondasi:** autentikasi, peran dan matriks akses (`role_permissions`), matriks wewenang (`authority_limits`), `OfficeScope`, trait `Auditable`, pengalih peran demo, halaman Kantor, Pengguna, Peran, dan Matriks Wewenang.
- **M1 Mesin akuntansi:**
  - COA berjenjang (AKT-01) dan template jurnal per transaksi dan produk (AKT-02)
  - `JournalService`: `post`, `postFromTemplate`, `postInterOffice`, `canReverse`, `reverse`
  - Jurnal pembalik (AKT-04)
  - `LedgerService`: buku besar dan neraca saldo per kantor dan konsolidasi (AKT-05)
  - RAK otomatis dua/tiga sisi (RAK-01, RAK-02)
  - `DocumentNumberService`, `AuthorityService`
  - Guard model agar jurnal terposting tidak bisa diubah atau dihapus
  - Halaman: Jurnal, Detail Jurnal, Buku Besar, Neraca Saldo, Bagan Akun, Template Jurnal
  - Seeder: `AccountSeeder`, `JournalTemplateSeeder`, `Demo\OpeningBalanceSeeder`
- **Verifikasi terakhir:** `migrate:fresh --seed` berhasil, 92 test lulus.
- **Catatan:** jurnal saldo awal demo bertanggal buku hari ini dan perlu dimundurkan ke awal histori 6 bulan jika seeder historis dibuat.

**Mockup (tampilan saja):** semua layar M2 ke atas. Data berasal dari `app/Mockups/{Domain}Mockup.php`.

## Cara melanjutkan ke full MVC

1. Ubah bagian "Mode saat ini" di CLAUDE.md kembali ke full MVC, lalu pindahkan aturan di bawah ke CLAUDE.md.
2. Kerjakan per modul sesuai tabel milestone asli di bawah. Untuk setiap modul:
   - buat migration, model, service, controller, seeder, dan test;
   - pertahankan Blade mockup dan ganti sumber datanya dari array mockup ke model;
   - hapus kelas mockup modul itu setelah modul nyata selesai.
3. Array mockup sengaja memakai nama kunci dan nilai status yang direncanakan untuk kolom (Inggris, snake_case), sehingga bisa menjadi acuan skema.
4. Keputusan dan deviasi di PROGRESS.md tetap berlaku, kecuali yang ditandai khusus mockup.

## Prinsip evolusioner

Model data dan mesin akuntansi dibuat benar sejak awal agar bisa dilanjutkan ke produksi. Fitur dan UI boleh disederhanakan sesuai BRIEF.

Yang tidak boleh disederhanakan: aturan domain di bawah (double-entry, transaksi tidak bisa diubah, `office_id`, tanggal buku, RAK).

## Aturan domain (tidak boleh dilanggar)

1. Kolom uang bertipe DECIMAL(18,2). Di PHP jangan pakai float; gunakan string dengan fungsi bcmath. Pembulatan ke rupiah penuh (ATR-08).
2. Transaksi keuangan ditulis di controller, di dalam `DB::beginTransaction()` dengan `try`/`catch` yang me-rollback. Logika yang dipakai lebih dari satu tempat (termasuk seeder) dipindah ke service di `app/Services/`.
3. Setiap transaksi keuangan membuat jurnal double-entry melalui `JournalService`. Jika total debit tidak sama dengan kredit, lempar exception dan rollback.
4. Transaksi dan jurnal yang sudah diposting tidak boleh di-update atau di-delete. Koreksi dilakukan dengan jurnal pembalik yang merujuk transaksi asal (ATR-03).
5. Setiap tabel transaksional punya `office_id`. Pengguna cabang hanya melihat data kantornya melalui global scope. Pengecualian: pencarian identitas anggota lintas cabang (AGT-02).
6. Tanggal transaksi memakai tanggal buku aktif kantor (`offices.book_date`), bukan `now()`. Timestamp audit tetap dari server.
7. Transaksi antar kantor membentuk jurnal RAK di kedua kantor dalam satu DB transaction (RAK-02).
8. Saldo, jurnal, dan data transaksi tidak boleh di-cache. Cache Redis hanya untuk data referensi dan agregat dashboard, dan wajib di-invalidate setiap ada posting baru.
9. Untuk aksi yang memerlukan persetujuan, pembuat tidak boleh sama dengan penyetuju (ATR-05).
10. Semua create, update, approve, reject, dan reversal tercatat di audit trail terpusat (trait atau observer), bukan log manual per controller.
11. Seeder data historis wajib memposting jurnal melalui `JournalService` dengan urutan yang sama seperti controller, bukan insert jurnal atau saldo langsung, agar buku besar cocok dengan buku pembantu.

Aturan rinci ada di `.claude/rules/services.md` dan `.claude/rules/database.md`.

## Struktur kode

- `app/Http/Controllers/{Domain}/` — validasi input, logika bisnis transaksi di dalam DB transaction, kembalikan view atau redirect
- `app/Services/{Domain}/` — logika yang dipakai bersama: `JournalService`, otorisasi matriks wewenang, perhitungan
- Domain: Organization, Accounting, Member, Savings, Loan, Cash
- `app/Models/` — Eloquent, relasi, scope
- `database/seeders/Demo/` — seeder skenario demo
- `resources/views/{domain}/` — Blade

## Cara kerja sesi

- Kerjakan satu milestone (atau satu fitur) per sesi.
- Sebelum menulis kode untuk milestone baru, buat rencana singkat: file yang dibuat atau diubah, ID kebutuhan PRD yang dicakup, dan penyederhanaan yang diambil. Tunggu persetujuan sebelum mulai.
- Jika PRD ambigu, pakai asumsi baseline PRD dan catat di PROGRESS.md. Bertanya hanya jika benar-benar menghalangi pekerjaan.
- Setelah milestone selesai: jalankan `php artisan migrate:fresh --seed` dan `php artisan test`, lalu perbarui PROGRESS.md (yang selesai, keputusan, deviasi dari PRD, langkah berikutnya).
- Test wajib untuk service akuntansi, RAK, kas, dan pinjaman. UI tidak wajib ditest pada tahap prototype.

## Data demo (full MVC)

- Anggota 60–120 per cabang dibuat seeder (Faker `id_ID`) dengan NIK 16 digit fiktif.
- Sekitar 40% anggota punya pinjaman aktif. Sebaran kolektibilitas kira-kira 85% lancar, 8% kurang lancar, 4% diragukan, 3% macet. Cabang 05 lebih buruk.
- Histori transaksi 6 bulan terakhir (setoran, angsuran, pencairan) dibuat melalui service agar jurnal dan saldo konsisten, dan berakhir di tanggal buku hari ini.
- Setiap milestone boleh membuat seeder minimal untuk kebutuhannya sendiri. Seeder historis lengkap dibuat di milestone 6.

## Milestone asli

| No | Milestone | Isi | Selesai jika |
|---|---|---|---|
| 0 | Fondasi | Setup proyek, autentikasi, peran, layout, pengalih peran demo, seeder kantor dan pengguna | Semua peran bisa login dan data terbatas per kantor |
| 1 | Mesin akuntansi | COA, template jurnal, `JournalService`, RAK otomatis, buku besar, neraca saldo | Test lulus: jurnal seimbang, jurnal pembalik, RAK dua sisi |
| 2 | Keanggotaan | M02 dan pengecekan NIK lintas cabang | Bagian CS dari babak 1 berjalan |
| 3 | Simpanan dan kas | M03, sesi kas teller, brankas | Setor dan tarik tercatat, jurnal terbentuk, saldo teller sesuai |
| 4 | Pinjaman | M04 dari simulasi sampai angsuran | Babak 2 dan 3 berjalan |
| 5 | Tutup kas dan tutup hari | KAS-04, KAS-05, TTP-01, TTP-02, denda, kolektibilitas | Babak 4 berjalan |
| 6 | Data demo historis | Seeder 6 bulan melalui service | `migrate:fresh --seed` berhasil dan buku pembantu sama dengan buku besar |
| 7 | Dashboard dan laporan | LAP-01, LAP-02, LAP-03, LAP-07, AKT-06, RAK-05, RAK-06 | Babak 5 berjalan |
| 8 | Audit dan penyelesaian | AUD-01, AUD-02, poles UI, README berisi naskah demo 5 babak | Demo penuh bisa dijalankan dari database segar |

## Rencana Milestone 2 full MVC (disusun 11 Sep 2026, belum disetujui)

- **Cakupan:**
  - AGT-01, AGT-02, AGT-04 (kerangka profil), AGT-10, ATR-05
  - AGT-03 (aktivasi, nomor anggota, rekening SP/SW, jurnal setoran pokok) diusulkan pindah ke M3 karena butuh setoran tunai di teller
- **Alur status:** `pending_approval` (CS) → `approved` (KCB, menunggu setoran pokok) atau `rejected` → `active` (M3) → `exited`.
- **Tabel:**
  - `members`: `office_id`, `registered_by`, `approved_by`, `number` nullable unique, `nik` cast `encrypted`, `nik_hash` HMAC-SHA256 unique, identitas, `monthly_income` DECIMAL(18,2), `ktp_photo_path`, `data_consent_at`, `status`, `registration_date`, `approved_at`, `rejection_reason`
  - `member_heirs`: `member_id`, nama, hubungan, telepon
- **Nomor anggota:** format `member` di `config/numbering.php`, contoh `03.000123`, terbit saat aktivasi.
- **Service:** `app/Services/Member/MemberLookupService` (`nikHash`, `findByNik` melewati global scope tanpa data saldo, `checkRegistrable`). Dipakai ulang oleh PJM-04.
- **Controller:** `Member\MemberController` (`index`, `create`, `store`, `show`, `checkNik` POST JSON, `approve`, `reject`, `ktpPhoto`).
  - Maker-checker: pendaftar ≠ penyetuju.
  - Foto KTP disimpan di disk `local` (privat).
- **Tampilan:** NIK disamarkan di daftar dan hasil cek lintas cabang (NFR-06).
- **Belum dibuat:** tanpa edit data setelah daftar (AGT-05). NIK yang ditolak tidak bisa didaftarkan ulang karena `nik_hash` unique.
- **Test:** `MemberLookupServiceTest` dan `MemberControllerTest`.
