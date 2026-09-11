# Progress Prototype

Diperbarui di akhir setiap sesi. Jaga file ini tetap ringkas karena dimuat di setiap sesi.

## Status milestone

Mode prototype tampilan sejak 11 Sep 2026. Penomoran milestone mengikuti BRIEF baru; milestone asli full MVC ada di `docs/prototype/FULL-MVC.md`.

| No | Milestone | Status |
|---|---|---|
| 0 | Fondasi (nyata) | Selesai (11 Sep 2026) |
| 1 | Mesin akuntansi (nyata) | Selesai (11 Sep 2026) |
| 2 | Keanggotaan (mockup) | Selesai (11 Sep 2026) |
| 3 | Simpanan dan kas (mockup) | Selesai (11 Sep 2026) |
| 4 | Pinjaman (mockup) | Belum mulai |
| 5 | Tutup kas dan tutup hari (mockup) | Belum mulai |
| 6 | Dashboard dan laporan (mockup) | Belum mulai |
| 7 | Audit dan penyelesaian (mockup) | Belum mulai |

## Keputusan prototype

Keputusan yang diambil saat PRD ambigu. Format: tanggal — keputusan — alasan — ID PRD/OQ terkait.

- 2026-09-11 — Kantor demo: Kantor Pusat (00) dan Cabang 01–09 di kota Jawa Barat, nama di `config/demo.php` — BRIEF tidak menetapkan nama — OQ-11.
- 2026-09-11 — Enum tipe kantor sudah mencakup `sub_branch` dan `cash_office` meski belum dipakai — PRD bagian 4 meminta struktur siap untuk tingkat kantor tambahan — OQ-11.
- 2026-09-11 — Satu pengguna boleh punya lebih dari satu peran (tabel `role_user`); pengguna melihat semua kantor bila salah satu perannya berlingkup semua kantor — asumsi PRD 5.2 — OQ-13.
- 2026-09-11 — Peran pusat hanya untuk pengguna kantor pusat, peran cabang hanya untuk kantor selain pusat — kolom Lokasi di PRD 5.1.
- 2026-09-11 — Matriks wewenang: persetujuan pinjaman KCB Rp25.000.000, KKR Rp100.000.000, MGR tanpa batas (BRIEF); penarikan KCB Rp50.000.000, pembalikan KCB Rp10.000.000, penghapusan denda KCB Rp1.000.000, selisih kas KCB tanpa batas, jurnal memorial dan pembalikan KAK tanpa batas (asumsi) — OQ-14.
- 2026-09-11 — Kode kantor dan tanggal buku tidak bisa diubah dari form edit kantor; tanggal buku maju lewat tutup hari — ATR-04, ATR-07.
- 2026-09-11 — Peran KOL tidak dibuat karena aplikasi kolektor fase 3 — OQ-32.
- 2026-09-11 — RAK hanya per pasangan pusat–cabang, kode `1.9.{kode kantor lawan}`: buku pusat memakai "RAK {cabang}" (hanya pusat), semua cabang memakai satu akun "RAK Kantor Pusat" (1.9.00). Transaksi antar dua cabang dilewatkan pusat (3 jurnal, satu `inter_office_group`). Yang nol saat konsolidasi adalah jumlah seluruh akun RAK, bukan per akun. Akun RAK dibuat otomatis saat kantor ditambah atau saat posting pertama — disetujui Andreas — RAK-01, RAK-02.
- 2026-09-11 — Simpanan pokok dan wajib = ekuitas (3.1.xx); simpanan sukarela dan berjangka = kewajiban (2.1.xx) — disetujui Andreas — OQ-38.
- 2026-09-11 — Nomor jurnal `JU-{kode kantor}-{YYYYMMDD}-{urut 4 digit}`, urut per kantor per tanggal buku, format di `config/numbering.php`, penghitung di tabel `document_sequences` (dipakai ulang untuk nomor dokumen lain) — disetujui Andreas — ATR-07.
- 2026-09-11 — Baris template punya `office_role`: `origin` (kantor transaksi, mis. kas) dan `destination` (kantor pemilik rekening). Bila kedua kantor berbeda, `postFromTemplate()` otomatis memakai jalur RAK. Template khusus produk didahulukan dari template umum. Kode produk yang dipesan: SP, SW, SS, PUM, PUS — ATR-02, AKT-02, A-06.
- 2026-09-11 — `JournalService` menolak nominal yang tidak bulat rupiah; pembulatan menjadi tanggung jawab perhitungan transaksi (M4) — ATR-08.
- 2026-09-11 — Jurnal pembalik memakai tanggal buku aktif saat pembalikan, menukar debit dan kredit, menyalin `source`, dan membalik semua jurnal dalam grup antar kantor. Jurnal asal tidak diubah (status tetap `posted`; unique `reversal_of_id` mencegah pembalikan ganda). Pembuat jurnal tidak boleh membaliknya; nominal dicek ke matriks wewenang `reversal` — ATR-03, ATR-05, AKT-04.
- 2026-09-11 — Contoh akun khusus pusat: 3.2.01 Cadangan Umum dan 3.3.01 SHU Tahun Lalu — AKT-01, OQ-38.
- 2026-09-11 — Jurnal kaki pusat pada transaksi antar cabang memakai tanggal buku pusat, yang bisa berbeda dari cabang bila tutup hari tidak serentak. Selisih waktu ini ditampilkan di rekonsiliasi RAK (M7) — RAK-05.
- 2026-09-11 — Filter halaman akuntansi: peran berlingkup semua kantor melihat konsolidasi secara default; peran cabang terkunci ke kantornya (kantor lain 404). Daftar jurnal default tanggal buku, buku besar dan neraca saldo default awal bulan s.d. tanggal buku — AKT-05.
- 2026-09-11 — Prototype dilanjutkan sebagai tampilan dengan data hardcoded mulai M2. Kode M0–M1 tetap nyata, dan panduan full MVC disimpan di `docs/prototype/FULL-MVC.md`. Data contoh berada di `app/Mockups/`. Seeder historis 6 bulan dihapus dari rencana — kebutuhan: menunjukkan gambaran aplikasi dengan biaya token rendah — keputusan Andreas.
- 2026-09-11 — Folder `app/Mockups/` disetujui untuk data contoh — disetujui Andreas.
- 2026-09-11 — Menu Akuntansi saat demo diarahkan ke layar jurnal, buku besar, dan neraca saldo versi mockup. Layar ini dibuat bertahap di M3–M6 dan memuat saldo awal serta transaksi mockup. Halaman nyata M1 tetap ada route-nya — agar babak 3 dan 5 konsisten — disetujui Andreas.
- 2026-09-11 — Data mockup anggota: 60–120 per cabang (total 738, 711 aktif), setiap cabang punya 2 calon `pending_approval` dan 1 `approved`. ID anggota `{kode kantor}{urut 4 digit}`, nomor anggota `{kode kantor}.{urut 6 digit}`, rekening `{kode kantor}.{SP|SW|SS}.{urut}`, pinjaman `PJ-{kode kantor}-{urut}`. Sekitar 42% anggota aktif punya pinjaman. Peluang kolektibilitas 85/8/4/3%, Cabang 05 60/17/12/11% — BRIEF data demo.
- 2026-09-11 — Skenario babak 1: Siti Rahmawati, Cabang Subang (07), NIK `3213054708850003`, aktif dengan 1 pinjaman lancar (`config/demo.php`) — BRIEF.
- 2026-09-11 — Calon anggota baru dan keputusan KCB disimpan di session (`demo.member_candidates`, `demo.member_decisions`) dan hilang saat logout; pengalih peran tidak menghapusnya — AGT-01, ATR-05.
- 2026-09-11 — Cek NIK mengabaikan pendaftaran berstatus ditolak, jadi NIK itu boleh didaftarkan ulang. Hasil cek hanya menampilkan nama, NIK tersamar, kantor asal, status keanggotaan, dan jumlah pinjaman berjalan beserta kolektibilitas, tanpa saldo — AGT-02, PRD 5.2.
- 2026-09-11 — Aktivasi anggota (setoran pokok di teller, nomor anggota terbit, rekening SP/SW dibuka) ditunjukkan di M3; M2 berhenti di status `approved` — AGT-03, OQ-15.
- 2026-09-11 — Aktivasi di teller: setoran pokok Rp100.000 diterima, nomor anggota melanjutkan urutan kantor, rekening SP dan SW dibuka (SW saldo nol). Anggota yang diaktifkan saat demo tidak punya pinjaman — AGT-03, SIM-02, OQ-15.
- 2026-09-11 — Produk simpanan (asumsi, `config/demo.php`): SP hanya disetor saat aktivasi, SW kelipatan Rp50.000, SS setoran minimal Rp10.000, saldo minimal Rp25.000, jasa 3% per tahun — SIM-01, OQ-19.
- 2026-09-11 — Penarikan SS: teller langsung sampai Rp5.000.000 (`demo.savings.teller_withdrawal_limit`). Di atasnya otorisasi KCB sampai Rp50.000.000, lalu MGR. Otorisasi dicek lewat `AuthorityService` dan teller tidak boleh mengotorisasi transaksinya sendiri. Jurnal terbentuk setelah diotorisasi — SIM-05, OQ-14.
- 2026-09-11 — Sesi kas: saldo awal dihitung per pecahan (isian awal Rp20.000.000) dan langsung dikonfirmasi KCB sebagai pemegang brankas. Perpindahan kas lain menunggu konfirmasi KCB atau ADC di kantor yang sama, bukan pengaju. Setor/tarik ditolak bila sesi belum dibuka atau kas teller kurang — KAS-01, KAS-02, OQ-35.
- 2026-09-11 — Jurnal mockup rinci hanya 30 hari terakhir (`demo.cash.detail_days`). Sebelumnya diringkas jadi saldo awal per kantor yang dihitung dari simpanan, pinjaman, pendapatan jasa, dan beban contoh. RAK 1.9.00 menjadi penyeimbang buku cabang, SHU Tahun Lalu penyeimbang buku pusat. Kas teller contoh dimulai dan diakhiri nol setiap hari lewat jurnal brankas. Filter tanggal dibatasi ke periode rinci; daftar jurnal default 7 hari terakhir — AKT-05, RAK-06.
- 2026-09-11 — Mutasi rekening contoh 6 bulan dibangun mundur dari saldo agar saldo akhir cocok. Tidak ada transaksi contoh pada tanggal buku aktif, jadi hari demo dimulai dari buka sesi kas — SIM-15.
- 2026-09-11 — Pencocokan buku pembantu (SP, SW, SS, PUM, PUS) tampil di neraca saldo mockup hanya bila tanggal akhir = tanggal buku — AKT-08.

## Deviasi dari PRD

- ORG-02: reset kata sandi dilakukan admin lewat form edit pengguna, tanpa email (penyederhanaan BRIEF).
- ORG-03 dan ORG-06: matriks akses dan matriks wewenang diisi seeder, halamannya hanya lihat (penyederhanaan BRIEF).
- ATR-09: perubahan peran pengguna (`roles()->sync()`) belum tercatat di audit trail karena tidak memicu event model. Ditangani saat melanjutkan ke full MVC.
- NFR-10: pesan validasi Laravel dan judul "Validation Error" dari paket sweet-alert masih berbahasa Inggris.
- AKT-01 dan AKT-02: COA dan template jurnal diisi seeder dan hanya dapat dilihat; KAK belum bisa mengonfigurasi (penyederhanaan BRIEF).
- AKT-03: jurnal memorial belum dibuat (di luar cakupan BRIEF).
- AKT-04/ATR-03: pembalikan langsung oleh KAK tanpa penyetuju kedua; pembalikan per transaksi operasional oleh KCB menyusul bersama transaksinya (M3–M5).
- AKT-05: buku pembantu menyusul di M3–M4; tanpa ekspor Excel (AKT-14) dan tanpa kunci periode (ATR-10).
- ATR-09: audit trail mencatat pembuatan header jurnal (termasuk jurnal pembalik), baris jurnal tidak diaudit satu per satu.
- AGT-01: foto KTP wajib dipilih dan divalidasi tetapi tidak disimpan (mockup); foto diri tidak diminta; satu ahli waris.
- AGT-05: data anggota tidak bisa diubah setelah didaftarkan (di luar cakupan BRIEF).
- NFR-06: NIK disamarkan di daftar anggota dan hasil cek lintas cabang; NIK lengkap tampil di profil.
- SIM-04, pembukaan rekening sukarela baru, dan simpanan berjangka tidak dibuat. SIM-15: mutasi hanya dicetak HTML, tanpa unduh.
- KAS-03: batas maksimum saldo teller tidak dibuat.
- KAS-11: mutasi kas harian tampil per sesi teller untuk tanggal buku aktif saja, tanpa pilihan tanggal lampau.
- AKT-05 mockup: saldo pinjaman belum bergerak di jurnal rinci karena angsuran menyusul di M4.

## Usulan perubahan PRD

- RAK-01: tegaskan bahwa transaksi antar dua cabang dibukukan melalui RAK pusat (tiga jurnal), karena PRD hanya menyebut pasangan pusat–cabang.

## Sesi terakhir

- Tanggal: 11 Sep 2026
- Selesai: Milestone 3 — Simpanan dan kas (mockup).
  - Data contoh: `SavingsMockup` (produk, mutasi 6 bulan, transaksi session), `CashMockup` (pecahan, sesi, perpindahan, posisi kas), `AccountingMockup` (saldo awal, jurnal, buku besar, neraca saldo, pencocokan buku pembantu). `MemberMockup::activate()` ditambahkan.
  - Controller `Savings\{SavingsProduct, SavingsAccount, SavingsTransaction, MemberActivation}`, `Cash\{CashSession, CashTransfer, CashPosition}`, `Accounting\{JournalMockup, GeneralLedgerMockup, TrialBalanceMockup}`; 25 route baru.
  - Sidebar: menu Simpanan dan Kas; menu Akuntansi Jurnal/Buku Besar/Neraca Saldo diarahkan ke versi mockup. Profil anggota `approved` punya tombol Terima Setoran Pokok.
  - Layar: produk simpanan, rekening dan mutasi (cetak HTML), setor/tarik dengan cari rekening dan pratinjau jurnal (JS), bukti transaksi (cetak), otorisasi penarikan, aktivasi anggota, buka sesi kas per pecahan (JS), mutasi kas teller, brankas dan perpindahan kas dengan konfirmasi, posisi kas semua kantor dengan grafik, jurnal/buku besar/neraca saldo mockup.
- Verifikasi: tinker memastikan setiap kantor seimbang, RAK konsolidasi nol, buku pembantu cocok, dan saldo akhir semua mutasi = saldo rekening. Alur HTTP TLR03 → KCB03 → KAK berjalan (buka sesi, aktivasi 30105, setor SW, tarik SP ditolak, tarik SS Rp8.000.000 menunggu lalu diotorisasi KCB, perpindahan kas dikonfirmasi, neraca saldo tetap seimbang dan cocok). `view:cache` berhasil, 92 test lulus (routes dan sidebar berubah).
- Catatan:
  - Jalankan `npm run build` untuk kelas Tailwind baru.
  - Halaman konsolidasi (neraca saldo, jurnal, posisi kas) butuh sekitar 2 detik karena data contoh dihitung ulang setiap request. Pertimbangkan cache per request atau bekukan data bila terasa lambat saat demo.
  - Pesan validasi masih berjudul "Validation Error" (NFR-10).
- Berikutnya: Milestone 4 — Pinjaman (mockup), termasuk angsuran yang bergerak di jurnal mockup. Buat rencana singkat dan tunggu persetujuan.
