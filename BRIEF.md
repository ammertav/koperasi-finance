# Brief Prototype — KSP Multi Cabang

## Tujuan

Menunjukkan ke calon klien bahwa kegiatan keuangan 9 cabang bisa dijalankan, dipantau, dan dikonsolidasi dari pusat tanpa keliling ke cabang. Prototype juga menjadi alat discovery: setiap layar sebaiknya memancing klien menjawab pertanyaan terbuka di PRD.

Audiens demo adalah pengurus, manajer pusat, dan kepala keuangan koperasi, bukan orang teknis.

## Pesan utama demo

1. Semua cabang ada di satu sistem, dan pusat melihatnya secara real-time.
2. Data anggota tunggal, sehingga duplikasi lintas cabang langsung terdeteksi.
3. Jurnal terbentuk otomatis dari transaksi, jadi laporan keuangan tidak perlu direkap manual.
4. Kontrol internal berjalan: persetujuan berjenjang, tutup kas, dan jejak audit.

## Naskah demo (5 babak)

1. **Pagi di Cabang 03.** Teller membuka sesi kas. CS mendaftarkan calon anggota, tetapi NIK-nya ternyata sudah terdaftar di Cabang 07 dengan pinjaman berjalan. Sistem menahan pendaftaran.
2. **Pengajuan pinjaman.** CS mengajukan pinjaman Rp35.000.000 untuk anggota lain. Pengecekan lintas cabang berjalan otomatis. Analis mengisi analisis 5C. Kepala cabang menyetujui, tetapi plafon melebihi batas cabang sehingga naik ke komite kredit pusat. Pengguna berganti peran ke komite dan menyetujui dari pusat.
3. **Pencairan dan jurnal otomatis.** Teller mencairkan pinjaman dengan potongan provisi dan administrasi. Tampilkan jurnal yang terbentuk dan saldo buku besar cabang yang langsung berubah.
4. **Sore: tutup kas.** Teller menghitung kas per pecahan dan ditemukan selisih kurang Rp50.000. Kepala cabang mengotorisasi selisih. Tutup hari berjalan dan kolektibilitas diperbarui.
5. **Kantor pusat, tanpa keliling.** Manajer membuka dashboard konsolidasi: posisi 9 cabang, pinjaman bermasalah per cabang, laporan posisi keuangan konsolidasi, RAK yang seimbang, dan laporan pengecualian yang memuat selisih kas dari babak 4.

## Pengalih peran demo

Topbar memiliki dropdown "Masuk sebagai..." untuk berpindah antar pengguna demo tanpa logout. Fitur ini hanya aktif jika `APP_DEMO=true` di `.env`. Tanpa fitur ini, demo 5 babak akan tersendat oleh login berulang.

## Cakupan prototype

| Modul | Dibuat (ID PRD) | Disederhanakan | Tidak dibuat |
|---|---|---|---|
| M01 Organisasi | ORG-01, ORG-02, ORG-03, ORG-06 | Hak akses berbasis peran tetap, tanpa UI editor izin. Matriks wewenang diisi seeder dan hanya dapat dilihat. | ORG-04, ORG-05, ORG-07–09 |
| M02 Keanggotaan | AGT-01, AGT-02, AGT-03, AGT-04 | Unggah dokumen cukup foto KTP. | AGT-05–12 |
| M03 Simpanan | SIM-02, SIM-03, SIM-05, SIM-06, SIM-15 | Produk (SIM-01) dari seeder, tampil read-only. | Simpanan berjangka, perhitungan jasa, pajak, tagihan wajib |
| M04 Pinjaman | PJM-02–06, PJM-08–11, PJM-13, PJM-15, PJM-24 | Produk dari seeder. Metode jasa hanya flat dan anuitas. Akad berupa halaman HTML siap cetak. Denda dan kolektibilitas dihitung saat tutup hari. | Restrukturisasi, top-up, hapus buku, penagihan, mutasi jaminan, cadangan kerugian |
| M05 Kas | KAS-01, KAS-02, KAS-04, KAS-05, KAS-10, KAS-11 | Satu brankas per cabang. | Rekening bank, rekonsiliasi, biaya operasional, kas kecil |
| M06 Akuntansi | AKT-01, AKT-02, AKT-04, AKT-05, AKT-06, AKT-08 | COA dan template jurnal dari seeder. Laporan posisi keuangan dan hasil usaha dalam format sederhana. | Jurnal memorial, aset tetap, anggaran |
| M07 Antar kantor | RAK-01, RAK-02, RAK-03, RAK-05, RAK-06 | Transaksi lintas kantor: setoran simpanan di cabang non-asal dan dropping dana sederhana. | RAK-04, RAK-07, RAK-08 |
| M08 Tutup buku | TTP-01, TTP-02, TTP-04 | Tutup hari memajukan tanggal buku serta menghitung denda dan kolektibilitas. | Tutup bulan, tutup tahun, SHU |
| M09 Laporan | LAP-01, LAP-02, LAP-03, LAP-07 | Ekspor Excel/PDF tidak wajib. | Laporan lainnya |
| M10 Audit | AUD-01, AUD-02 | — | AUD-03–06 |

Penyederhanaan umum yang diizinkan: tanpa 2FA, reset kata sandi, dan email; parameter tidak punya UI edit; cetak dokumen berupa halaman HTML.

Yang tidak boleh disederhanakan: aturan domain di CLAUDE.md (double-entry, transaksi tidak bisa diubah, `kantor_id`, tanggal buku, RAK).

## Asumsi angka untuk prototype

Semua angka di bawah adalah asumsi demo dan disimpan di seeder atau `config/demo.php` agar mudah diganti.

- Simpanan pokok Rp100.000, simpanan wajib Rp50.000 per bulan.
- Produk "Pinjaman Umum": jasa flat 1,5% per bulan, tenor 6–36 bulan. Produk "Pinjaman Usaha": anuitas 18% per tahun, tenor 12–60 bulan.
- Provisi 1% dan administrasi Rp50.000 dipotong saat pencairan.
- Denda 0,1% per hari dari angsuran tertunggak, dengan toleransi 3 hari.
- Kolektibilitas: lancar 0–30 hari, kurang lancar 31–90 hari, diragukan 91–180 hari, macet di atas 180 hari.
- Batas persetujuan pinjaman: kepala cabang sampai Rp25.000.000, komite kredit sampai Rp100.000.000, di atasnya manajer pusat.
- Setiap selisih kas memerlukan otorisasi kepala cabang.

## Data demo

- Kantor: Pusat (kode 00) dan Cabang 01–09. Nama cabang disimpan di `config/demo.php`.
- Pengguna: satu per peran di pusat. Setiap cabang punya kepala cabang, CS, teller, analis kredit, dan admin. Kata sandi seragam dari `DEMO_PASSWORD` di `.env`.
- Anggota: 60–120 per cabang dengan nama dan alamat Indonesia yang realistis (Faker `id_ID`) dan NIK 16 digit fiktif.
- Pinjaman: sekitar 40% anggota punya pinjaman aktif. Sebaran kolektibilitas kira-kira 85% lancar, 8% kurang lancar, 4% diragukan, 3% macet. Cabang 05 dibuat lebih buruk agar dashboard punya cerita.
- Histori transaksi 6 bulan terakhir (setoran, angsuran, pencairan) dibuat melalui service agar jurnal dan saldo konsisten, dan berakhir di tanggal buku hari ini.
- Skenario babak 1: satu anggota di Cabang 07 dengan pinjaman aktif disiapkan. NIK-nya dicatat di README demo.
- Pecahan uang untuk tutup kas mengikuti pecahan Rupiah kertas dan logam yang beredar.

## Tema visual

Kesan lembaga keuangan yang tepercaya dan tenang. Usulan default: hijau tua sebagai warna utama, latar netral terang, amber hanya untuk peringatan, merah untuk kolektibilitas buruk. Dashboard memakai kartu KPI dan grafik Chart.js. Ganti bagian ini jika ada arahan desain lain.

## Milestone

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

Setiap milestone boleh membuat seeder minimal untuk kebutuhannya sendiri. Seeder historis lengkap dibuat di milestone 6.
