# Brief Prototype — KSP Multi Cabang

## Tujuan

Menunjukkan ke calon klien bahwa kegiatan keuangan 9 cabang bisa dijalankan, dipantau, dan dikonsolidasi dari pusat tanpa keliling ke cabang. Prototype juga menjadi alat discovery: setiap layar sebaiknya memancing klien menjawab pertanyaan terbuka di PRD.

Audiens demo adalah pengurus, manajer pusat, dan kepala keuangan koperasi, bukan orang teknis.

## Pendekatan

Sejak 11 Sep 2026 prototype dibangun sebagai **prototype tampilan**.

- **Milestone 0–1** sudah full MVC dan tetap dipakai: login, peran, pengalih peran demo, kantor, pengguna, dan mesin akuntansi.
- **Milestone 2 ke atas** berupa layar dengan data contoh hardcoded yang konsisten. Form tidak menyimpan ke database. Alur naskah demo disimulasikan dengan skenario tetap.
- Aturan teknisnya ada di CLAUDE.md.
- Panduan untuk melanjutkan ke full MVC ada di `docs/prototype/FULL-MVC.md`.

## Pesan utama demo

1. Semua cabang ada di satu sistem, dan pusat melihatnya secara real-time.
2. Data anggota tunggal, sehingga duplikasi lintas cabang langsung terdeteksi.
3. Jurnal terbentuk otomatis dari transaksi, jadi laporan keuangan tidak perlu direkap manual.
4. Kontrol internal berjalan: persetujuan berjenjang, tutup kas, dan jejak audit.

## Naskah demo (5 babak)

1. **Pagi di Cabang 03.** Teller membuka sesi kas. CS mendaftarkan calon anggota, tetapi NIK-nya ternyata sudah terdaftar di Cabang 07 dengan pinjaman berjalan. Sistem menahan pendaftaran.
2. **Pengajuan pinjaman.** CS mengajukan pinjaman Rp35.000.000 untuk anggota lain. Pengecekan lintas cabang berjalan otomatis. Analis mengisi analisis 5C. Kepala cabang menyetujui, tetapi plafon melebihi batas cabang sehingga naik ke komite kredit pusat. Pengguna berganti peran ke komite dan menyetujui dari pusat.
3. **Pencairan dan jurnal otomatis.** Teller mencairkan pinjaman dengan potongan provisi dan administrasi. Tampilkan jurnal yang terbentuk dan saldo buku besar cabang yang berubah.
4. **Sore: tutup kas.** Teller menghitung kas per pecahan dan ditemukan selisih kurang Rp50.000. Kepala cabang mengotorisasi selisih. Tutup hari berjalan dan kolektibilitas diperbarui.
5. **Kantor pusat, tanpa keliling.** Manajer membuka dashboard konsolidasi: posisi 9 cabang, pinjaman bermasalah per cabang, laporan posisi keuangan konsolidasi, RAK yang seimbang, dan laporan pengecualian yang memuat selisih kas dari babak 4.

Pada prototype tampilan, setiap babak adalah urutan layar dengan skenario tetap. Hasil babak sebelumnya (mis. pengajuan yang sudah disetujui KCB, selisih kas Rp50.000) boleh dibawa ke layar berikutnya lewat session atau sudah tertanam di data contoh.

## Pengalih peran demo

Navbar memiliki dropdown "Masuk sebagai..." untuk berpindah antar pengguna demo tanpa logout. Fitur ini hanya aktif jika `APP_DEMO=true` di `.env`. Tanpa fitur ini, demo 5 babak akan tersendat oleh login berulang.

## Cakupan layar

Kolom "Nyata" sudah full MVC. Kolom "Mockup" berisi layar yang ditampilkan dengan data contoh.

| Modul | Nyata (ID PRD) | Mockup (ID PRD) | Tidak dibuat |
|---|---|---|---|
| M01 Organisasi | ORG-01, ORG-02, ORG-03, ORG-06 | — | ORG-04, ORG-05, ORG-07–09 |
| M02 Keanggotaan | — | AGT-01, AGT-02, AGT-03, AGT-04, AGT-10 | AGT-05–09, AGT-11–12 |
| M03 Simpanan | — | SIM-01 (lihat produk), SIM-02, SIM-03, SIM-05, SIM-06, SIM-15 | Simpanan berjangka, perhitungan jasa, pajak, tagihan wajib |
| M04 Pinjaman | — | PJM-02–06, PJM-08–11, PJM-13, PJM-15, PJM-24 | Restrukturisasi, top-up, hapus buku, penagihan, mutasi jaminan, cadangan kerugian |
| M05 Kas | — | KAS-01, KAS-02, KAS-04, KAS-05, KAS-10, KAS-11 | Rekening bank, rekonsiliasi, biaya operasional, kas kecil |
| M06 Akuntansi | AKT-01, AKT-02, AKT-04, AKT-05 | AKT-06, AKT-08 | Jurnal memorial, aset tetap, anggaran |
| M07 Antar kantor | RAK-01, RAK-02 | RAK-03, RAK-05, RAK-06 | RAK-04, RAK-07, RAK-08 |
| M08 Tutup buku | — | TTP-01, TTP-02, TTP-04 | Tutup bulan, tutup tahun, SHU |
| M09 Laporan | — | LAP-01, LAP-02, LAP-03, LAP-07 | Laporan lainnya |
| M10 Audit | — | AUD-01, AUD-02 | AUD-03–06 |

Penyederhanaan umum: tanpa 2FA, reset kata sandi lewat email, atau email; parameter tidak punya UI edit; cetak dokumen berupa halaman HTML; ekspor Excel/PDF tidak dibuat.

## Asumsi angka untuk prototype

Semua angka di bawah adalah asumsi demo dan disimpan di `config/demo.php` agar mudah diganti.

- Simpanan pokok Rp100.000, simpanan wajib Rp50.000 per bulan.
- Produk "Pinjaman Umum": jasa flat 1,5% per bulan, tenor 6–36 bulan. Produk "Pinjaman Usaha": anuitas 18% per tahun, tenor 12–60 bulan.
- Provisi 1% dan administrasi Rp50.000 dipotong saat pencairan.
- Denda 0,1% per hari dari angsuran tertunggak, dengan toleransi 3 hari.
- Kolektibilitas: lancar 0–30 hari, kurang lancar 31–90 hari, diragukan 91–180 hari, macet di atas 180 hari.
- Batas persetujuan pinjaman: kepala cabang sampai Rp25.000.000, komite kredit sampai Rp100.000.000, di atasnya manajer pusat.
- Setiap selisih kas memerlukan otorisasi kepala cabang.

## Data demo

- **Kantor dan pengguna:** kantor Pusat (kode 00) dan Cabang 01–09, serta pengguna per peran, sudah dibuat seeder M0. Kata sandi per pengguna ditulis di `database/seeders/UserSeeder.php` dengan pola `{bagian email sebelum @}-ksp` (mis. `cs03-ksp`).
- **Anggota (mockup):** 60–120 per cabang dengan nama dan alamat Indonesia yang realistis dan NIK 16 digit fiktif. Data dibuat deterministik (Faker `id_ID` dengan seed tetap), sehingga sama di setiap pemuatan halaman.
- **Pinjaman (mockup):** sekitar 40% anggota punya pinjaman aktif. Sebaran kolektibilitas kira-kira 85% lancar, 8% kurang lancar, 4% diragukan, 3% macet. Cabang 05 dibuat lebih buruk agar dashboard punya cerita.
- **Histori transaksi (mockup):** 6 bulan terakhir. Ringkasan dashboard dan laporan dihitung dari data contoh yang sama.
- **Skenario babak 1:** satu anggota di Cabang 07 dengan pinjaman aktif. NIK-nya disimpan di `config/demo.php` dan dicatat di README demo.
- **Tutup kas:** pecahan uang mengikuti pecahan Rupiah kertas dan logam yang beredar.
- **Catatan:** Faker adalah dependensi dev. Jika demo dijalankan dari instalasi `--no-dev`, data contoh perlu dibekukan ke file.

## Tema visual

Warna, navbar, dan komponen mengikuti `.claude/rules/ui-design.md` (keputusan Andreas, meniru proyek referensi). Dashboard memakai kartu KPI dan grafik Chart.js. Amber untuk peringatan, merah untuk kolektibilitas buruk.

## Milestone

| No | Milestone | Isi | Selesai jika |
|---|---|---|---|
| 0 | Fondasi (nyata) | Autentikasi, peran, layout, pengalih peran demo, kantor, pengguna | Selesai |
| 1 | Mesin akuntansi (nyata) | COA, template jurnal, `JournalService`, RAK, buku besar, neraca saldo | Selesai |
| 2 | Keanggotaan | Daftar anggota, form registrasi dengan cek NIK lintas cabang dan persetujuan data pribadi, persetujuan KCB, profil anggota bertab (data diri, simpanan, pinjaman, jaminan, kolektibilitas) | Bagian CS dari babak 1 bisa diperagakan |
| 3 | Simpanan dan kas | Produk simpanan, daftar rekening, setor dan tarik dengan bukti transaksi, mutasi rekening, buka sesi kas teller, brankas, serta jurnal, buku besar, dan neraca saldo versi mockup (menggantikan menu Akuntansi nyata) | Setor dan tarik tampil beserta jurnal contoh dan saldo teller |
| 4 | Pinjaman | Produk, simulasi angsuran (JS), pengajuan dengan cek lintas cabang, analisis 5C, persetujuan berjenjang dengan eskalasi, pencairan dengan potongan dan jurnal, akad HTML, jadwal dan pembayaran angsuran | Babak 2 dan 3 bisa diperagakan |
| 5 | Tutup kas dan tutup hari | Hitung kas per pecahan (JS), otorisasi selisih, tutup hari dengan ringkasan denda dan kolektibilitas | Babak 4 bisa diperagakan |
| 6 | Dashboard dan laporan | Dashboard konsolidasi, laporan posisi keuangan dan hasil usaha, kolektibilitas per cabang, rekonsiliasi RAK, laporan pengecualian | Babak 5 bisa diperagakan |
| 7 | Audit dan penyelesaian | Jejak audit dan log akses, poles UI, README berisi naskah demo 5 babak | Demo 5 babak berjalan lancar dari `migrate:fresh --seed` |

## Risiko prototype tampilan

- **Klien meminta input bebas di luar skenario.** Sistem hanya menampilkan pesan sukses tanpa hasil nyata. Arahkan demo ke skenario yang disiapkan.
- **Data akuntansi nyata tidak sinkron dengan transaksi mockup.** Halaman Akuntansi nyata M1 hanya berisi saldo awal. Karena itu menu Akuntansi saat demo diarahkan ke jurnal, buku besar, dan neraca saldo versi mockup, yang dibuat bertahap di M3–M6.
