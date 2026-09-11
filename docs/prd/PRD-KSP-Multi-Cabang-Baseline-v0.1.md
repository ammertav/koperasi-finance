# PRD — Sistem Keuangan Terpusat Koperasi Simpan Pinjam Multi Cabang

**Baseline general — Opsi A: sistem inti simpan pinjam terpusat**

| Informasi | Keterangan |
|---|---|
| Versi | 0.1 (draft) |
| Tanggal | 11 September 2026 |
| Status | Baseline general, belum difitting ke kebutuhan klien |
| Disusun oleh | Sivinaries |
| Belum lengkap | Kriteria penerimaan (Given/When/Then) per kebutuhan, disusun per modul pada iterasi berikutnya |

---

## 0. Cara membaca dan memakai dokumen ini

### 0.1 Konvensi

- **M01–M10** adalah kode modul (lihat Bagian 7).
- **ID kebutuhan** memakai awalan modul: ORG, AGT, SIM, PJM, KAS, AKT, RAK, TTP, LAP, AUD. Aturan lintas modul memakai ATR, kebutuhan non-fungsional memakai NFR.
- **Prioritas (MoSCoW)** untuk fase 1: *Must* (wajib), *Should* (penting, bisa menyusul), *Could* (tambahan), *Won't* (tidak di fase 1).
- **[ASUMSI]** menandai keputusan sementara yang dipakai baseline sampai dikonfirmasi klien.
- **OQ-xx** merujuk ke pertanyaan terbuka di register (Bagian 15).

### 0.2 Bagaimana baseline ini difitting ke klien

Dokumen ini disusun untuk koperasi simpan pinjam konvensional pada umumnya. Bagian yang kemungkinan berbeda antar koperasi ditandai asumsi dan ditautkan ke pertanyaan terbuka. Pertanyaan terbuka dibagi tiga jenis:

| Jenis | Arti | Contoh | Dampak ke rancangan |
|---|---|---|---|
| **K — Konfigurasi** | Jawaban hanya mengisi nilai parameter. Baseline sudah dirancang agar nilai ini dapat diatur. | Persentase pembagian SHU, batas plafon persetujuan, tarif denda | Kecil. Diisi saat implementasi, desain tidak berubah. |
| **D — Desain** | Jawaban mengubah alur, struktur data, atau aturan modul. | Syariah vs konvensional, transaksi lintas cabang, mode offline | Besar. Harus dijawab sebelum desain detail modul terkait. |
| **S — Scope** | Jawaban menambah atau mengurangi fitur/modul. | Aplikasi kolektor, cetak buku tabungan, migrasi histori | Mengubah estimasi, jadwal, dan biaya. |

Alur fitting:

1. Saat discovery, dahulukan pertanyaan jenis D dan S (daftar prioritas ada di Bagian 15.10).
2. Catat jawaban klien di register dan ubah statusnya.
3. Ganti asumsi terkait dengan keputusan, lalu sesuaikan kebutuhan yang menautkan OQ tersebut di Bagian 8.
4. Catat perubahan di riwayat revisi (Bagian 17).
5. Jawaban jenis K dikumpulkan menjadi dokumen parameter konfigurasi untuk tahap implementasi.

---

## 1. Latar belakang dan masalah

Klien adalah koperasi simpan pinjam dengan 9 kantor cabang yang tersebar. Saat ini data keuangan dan operasional setiap cabang dikumpulkan dengan mendatangi cabang satu per satu. Kondisi ini menimbulkan beberapa masalah:

- Manajemen pusat tidak memiliki gambaran kondisi keuangan yang tepat waktu, seperti posisi kas, total simpanan, pinjaman beredar, dan tunggakan.
- Penyusunan laporan konsolidasi memakan waktu, tenaga, dan biaya perjalanan.
- Data yang direkap dan diinput ulang secara manual rawan selisih dan kesalahan.
- Masalah di cabang, seperti pinjaman bermasalah, selisih kas, atau transaksi tidak wajar, terlambat diketahui pusat.
- Data anggota tidak terpusat, sehingga berpotensi satu orang terdaftar atau meminjam di lebih dari satu cabang tanpa terdeteksi.

[ASUMSI] Cabang saat ini mencatat dengan buku manual, Excel, atau aplikasi terpisah yang tidak saling terhubung (OQ-01, OQ-02).

---

## 2. Tujuan, non-tujuan, dan metrik keberhasilan

### 2.1 Tujuan

| ID | Tujuan |
|---|---|
| T1 | Seluruh transaksi 9 cabang dan kantor pusat tercatat dalam satu sistem dan satu basis data. |
| T2 | Laporan keuangan per cabang dan konsolidasi tersedia tanpa kunjungan fisik ke cabang. |
| T3 | Jurnal akuntansi terbentuk otomatis dari transaksi operasional. |
| T4 | Kontrol internal terstandar di semua cabang: otorisasi berjenjang, jejak audit, dan tutup kas harian. |
| T5 | Data anggota tunggal dan dapat dicek lintas cabang. |

### 2.2 Non-tujuan (baseline)

- Aplikasi layanan mandiri untuk anggota (portal atau aplikasi mobile anggota). Direncanakan untuk fase lanjutan.
- Sistem kepegawaian dan penggajian karyawan koperasi.
- Pengelolaan unit usaha di luar simpan pinjam.
- Produk dan akuntansi syariah (OQ-03).
- Menggantikan peran auditor eksternal atau konsultan pajak.

### 2.3 Metrik keberhasilan

| Metrik | Target baseline | Catatan |
|---|---|---|
| Waktu penyusunan laporan keuangan konsolidasi bulanan | ≤ 3 hari kerja setelah akhir bulan | Kondisi saat ini diukur saat discovery (OQ-02) |
| Kunjungan ke cabang untuk pengumpulan data rutin | 0 | Kunjungan audit dan pembinaan tetap dapat dilakukan |
| Transaksi operasional yang terjurnal otomatis | 100% | |
| Selisih Rekening Antar Kantor saat tutup bulan | Rp0 | |
| Hari kerja dengan tutup kas cabang selesai di hari yang sama | 100% | |
| Anggota dengan NIK ganda | 0 | |

---

## 3. Ruang lingkup

### 3.1 Termasuk dalam baseline

- Modul M01 sampai M10 sebagaimana dijelaskan di Bagian 7 dan 8.
- Migrasi saldo awal dari pencatatan lama (Bagian 11.2).

### 3.2 Tidak termasuk dalam fase 1

| Item | Pertanyaan terkait |
|---|---|
| Aplikasi mobile kolektor | OQ-32 |
| Portal atau aplikasi anggota | — |
| Integrasi virtual account atau pembayaran bank secara real-time | OQ-36 |
| Notifikasi WhatsApp/SMS | — |
| Cetak buku tabungan (passbook) | OQ-22 |
| Mode offline cabang | OQ-06 |
| Produk syariah | OQ-03 |
| Pengiriman laporan langsung ke sistem regulator | OQ-45 |

---

## 4. Struktur organisasi dalam sistem

[ASUMSI] Koperasi terdiri dari 1 kantor pusat dan 9 kantor cabang, tanpa cabang pembantu atau kantor kas (OQ-11). Kantor pusat tidak melayani transaksi anggota secara langsung (OQ-12).

```
Koperasi (badan hukum)
└── Kantor Pusat (kode 00) — manajemen, akuntansi, komite kredit, pengelolaan likuiditas
    ├── Cabang 01
    ├── Cabang 02
    ├── ...
    └── Cabang 09
```

Ketentuan:

- Setiap kantor memiliki kode unik yang dipakai dalam nomor anggota, nomor rekening, dan nomor dokumen.
- Setiap kantor memiliki pembukuan sendiri (buku besar per kantor), lalu dikonsolidasi di tingkat koperasi.
- Model data mendukung penambahan tingkat kantor (cabang pembantu, kantor kas) tanpa perubahan struktur. Ini selaras dengan perizinan usaha simpan pinjam yang membedakan izin usaha kantor pusat dan izin jaringan pelayanan untuk kantor cabang, cabang pembantu, dan kantor kas.

---

## 5. Peran pengguna dan hak akses

### 5.1 Daftar peran

| Kode | Peran | Lokasi | Tanggung jawab utama |
|---|---|---|---|
| ADM | Admin sistem | Pusat | Mengelola pengguna, peran, dan parameter. Tidak melakukan transaksi keuangan. |
| MGR | Manajer pusat | Pusat | Mengawasi seluruh cabang dan memberi persetujuan tingkat tertinggi. |
| KAK | Kepala keuangan dan akuntansi | Pusat | Kebijakan akuntansi, tutup buku, konsolidasi, pengelolaan likuiditas. |
| SAK | Staf akuntansi pusat | Pusat | Jurnal, rekonsiliasi, penyusunan laporan. |
| KKR | Komite kredit | Pusat | Persetujuan pinjaman di atas batas wewenang cabang. |
| AUD | Pengawas dan auditor internal | Pusat | Pemeriksaan, akses lihat ke seluruh data. |
| PGR | Pengurus | Pusat | Memantau dashboard dan laporan, mengesahkan penetapan SHU. |
| KCB | Kepala cabang | Cabang | Otorisasi transaksi cabang, persetujuan pinjaman sesuai batas, tutup hari. |
| CS | Customer service | Cabang | Pendaftaran anggota, pembukaan rekening, penerimaan pengajuan pinjaman. |
| TLR | Teller | Cabang | Setoran, penarikan, angsuran, pencairan tunai, kas teller. |
| ANK | Analis kredit | Cabang | Survei, analisis, rekomendasi pinjaman, penagihan. |
| ADC | Admin dan pembukuan cabang | Cabang | Akad, jaminan, biaya operasional, rekening bank cabang, jurnal memorial cabang. |
| KOL | Kolektor | Cabang | Penagihan lapangan melalui aplikasi (fase 3, OQ-32). |

### 5.2 Matriks akses (baseline)

Keterangan: **P** = kelola penuh termasuk konfigurasi, **O** = input/operasional, **A** = menyetujui/otorisasi, **L** = lihat, **—** = tidak ada akses.

| Peran | M01 Org | M02 Anggota | M03 Simpanan | M04 Pinjaman | M05 Kas | M06 Akuntansi | M07 Antar kantor | M08 Tutup buku | M09 Laporan | M10 Audit |
|---|---|---|---|---|---|---|---|---|---|---|
| ADM | P | — | — | — | — | — | — | — | — | L |
| MGR | L | L | A | A | L | L | A | A | L | L |
| KAK | L | L | L | L | A | P | P | P | L | L |
| SAK | — | L | L | L | O | O | O | O | L | — |
| KKR | — | L | — | A | — | — | — | — | L | — |
| AUD | L | L | L | L | L | L | L | L | L | L |
| PGR | — | — | — | — | — | — | — | A | L | — |
| KCB | — | A | A | A | A | L | L | O | L | L |
| CS | — | O | O | O | — | — | — | — | L | — |
| TLR | — | L | O | O | O | — | — | — | L | — |
| ANK | — | L | L | O | — | — | — | — | L | — |
| ADC | — | L | L | O | O | O | L | O | L | — |

Ketentuan akses:

- Peran cabang hanya melihat dan mengolah data kantornya sendiri. Pengecualian: pencarian anggota lintas cabang untuk pengecekan identitas, status keanggotaan, dan status pinjaman, tanpa detail saldo.
- Akses laporan untuk CS dan teller terbatas pada laporan transaksi yang mereka tangani.
- [ASUMSI] Satu pengguna boleh memiliki lebih dari satu peran (OQ-13), tetapi sistem menolak jika pembuat dan penyetuju pada transaksi yang sama adalah orang yang sama.
- Batas nominal otorisasi diatur di matriks wewenang (ORG-06, OQ-14).

---

## 6. Aturan lintas modul

| ID | Aturan | OQ |
|---|---|---|
| ATR-01 | **Kepemilikan kantor.** Setiap data anggota, rekening, pinjaman, dan transaksi memiliki kantor pemilik. Akses dibatasi sesuai lingkup peran. | OQ-11 |
| ATR-02 | **Jurnal otomatis.** Setiap transaksi keuangan menghasilkan jurnal berpasangan (debit = kredit) berdasarkan template jurnal per jenis transaksi yang dapat dikonfigurasi. Bagian akuntansi tidak menginput ulang transaksi operasional. | OQ-38 |
| ATR-03 | **Tidak ada ubah/hapus transaksi terposting.** Koreksi dilakukan melalui transaksi pembalik yang merujuk transaksi asal dan memerlukan otorisasi. | — |
| ATR-04 | **Tanggal buku.** Setiap kantor memiliki tanggal buku aktif dan transaksi hanya dapat dilakukan pada tanggal tersebut. Koreksi tanggal mundur hanya melalui jurnal koreksi dengan otorisasi pusat, dan tidak dapat masuk ke periode yang sudah ditutup. | — |
| ATR-05 | **Maker-checker.** Transaksi di atas batas nominal atau jenis transaksi tertentu memerlukan persetujuan pengguna lain sesuai matriks wewenang. | OQ-14 |
| ATR-06 | **Parameter berversi.** Perubahan parameter produk (jasa, denda, biaya) berlaku sejak tanggal efektif. Kontrak yang sudah berjalan tetap memakai parameter saat akad, kecuali direstrukturisasi. | OQ-19, OQ-25 |
| ATR-07 | **Penomoran.** Nomor anggota, rekening, pinjaman, dan bukti transaksi unik di seluruh koperasi dan memuat kode kantor. Format dapat dikonfigurasi. | — |
| ATR-08 | **Pembulatan.** Nominal dibulatkan ke rupiah penuh. Aturan pembulatan angsuran dapat dikonfigurasi, dan selisih pembulatan disesuaikan pada angsuran terakhir. | OQ-25 |
| ATR-09 | **Jejak audit.** Setiap pembuatan, perubahan, persetujuan, penolakan, dan pembatalan data tercatat: siapa, kapan, dari perangkat/alamat mana, serta nilai sebelum dan sesudah. | — |
| ATR-10 | **Periode terkunci.** Setelah tutup bulan, tidak ada transaksi atau jurnal yang dapat masuk ke periode tersebut, kecuali periode dibuka kembali oleh KAK dengan alasan tercatat. | — |
| ATR-11 | **Bukti transaksi.** Setiap transaksi kas menghasilkan bukti yang dapat dicetak atau diunduh. | OQ-22 |
| ATR-12 | **Transaksi lintas kantor.** Transaksi yang melibatkan dua kantor otomatis membentuk jurnal Rekening Antar Kantor di kedua kantor. | OQ-16 |

---

## 7. Peta modul

| Kode | Modul | Fungsi singkat | Fase usulan |
|---|---|---|---|
| M01 | Organisasi dan hak akses | Kantor, pengguna, peran, matriks wewenang, parameter umum | 1 |
| M02 | Keanggotaan | Pendaftaran, pengelolaan data, mutasi, dan keluar anggota | 1 |
| M03 | Simpanan | Produk dan rekening simpanan pokok, wajib, sukarela, berjangka | 1 |
| M04 | Pinjaman dan jaminan | Pengajuan, persetujuan, pencairan, angsuran, kolektibilitas, penagihan, jaminan | 1 |
| M05 | Kas, teller, dan bank | Kas teller, brankas, tutup kas, rekening bank, biaya operasional | 1 |
| M06 | Akuntansi dan buku besar | COA, template jurnal, jurnal memorial, buku besar, laporan keuangan | 1 |
| M07 | Antar kantor dan konsolidasi | Rekening Antar Kantor, dropping dana, rekonsiliasi, eliminasi, konsolidasi | 1 |
| M08 | Tutup buku dan SHU | Tutup hari, tutup bulan, tutup tahun, perhitungan dan pembagian SHU | 1 (harian dan bulanan), 2 (tahunan dan SHU) |
| M09 | Laporan dan dashboard | Laporan operasional, keuangan, manajemen, dan kepatuhan | 1–2 |
| M10 | Audit dan kontrol | Jejak audit, laporan pengecualian, akses pemeriksa | 1 |

Ketergantungan antar modul:

```
M01 ──► seluruh modul
M02 ──► M03, M04
M03, M04, M05 ──► M06 (jurnal otomatis)
M06 ──► M07 ──► M08 ──► M09
M10 mencatat aktivitas seluruh modul
```

---

## 8. Kebutuhan fungsional per modul

Kebutuhan di bagian ini ditulis pada tingkat pernyataan. Pada iterasi berikutnya, setiap kebutuhan *Must* dilengkapi dengan user story dan kriteria penerimaan Given/When/Then. Urutan pendetailan yang diusulkan: M01 → M02 → M03 → M04 → M05 → M06 → M07 → M08 → M09 → M10.

### M01 — Organisasi dan hak akses

Tujuan: menyediakan struktur kantor, pengguna, dan kontrol wewenang yang menjadi dasar seluruh modul.

| ID | Kebutuhan | Prioritas | OQ |
|---|---|---|---|
| ORG-01 | Mengelola data kantor: kode, nama, alamat, tipe kantor, kantor induk, status aktif. | Must | OQ-11 |
| ORG-02 | Mengelola pengguna: data diri, kantor penempatan, peran, status aktif, reset kata sandi. | Must | OQ-13 |
| ORG-03 | Mengelola peran dan hak akses per fitur (lihat, input, setujui, kelola) beserta lingkup data (kantor sendiri atau semua kantor). | Must | — |
| ORG-04 | Autentikasi dua langkah untuk peran pusat dan peran penyetuju. | Should | — |
| ORG-05 | Kalender hari kerja dan hari libur per kantor, dipakai untuk jatuh tempo, denda, dan tutup hari. | Must | OQ-10 |
| ORG-06 | Matriks wewenang: batas nominal per peran per jenis transaksi (penarikan, pencairan, jurnal memorial, pembalikan, selisih kas, penghapusan denda). | Must | OQ-14 |
| ORG-07 | Parameter umum koperasi: identitas badan hukum, tahun buku, format penomoran. | Must | OQ-44 |
| ORG-08 | Mutasi pengguna antar kantor dengan riwayat penempatan. | Should | — |
| ORG-09 | Pembatasan jam akses per peran. | Could | — |

### M02 — Keanggotaan

Tujuan: data anggota tunggal di seluruh kantor, dengan siklus hidup keanggotaan yang tercatat lengkap.

| ID | Kebutuhan | Prioritas | OQ |
|---|---|---|---|
| AGT-01 | Registrasi calon anggota: identitas (NIK, nama, tempat dan tanggal lahir, alamat, kontak), pekerjaan dan penghasilan, ahli waris, unggah dokumen (KTP, foto). | Must | OQ-15 |
| AGT-02 | Pengecekan NIK ganda di seluruh kantor saat registrasi. Jika ditemukan, sistem menampilkan kantor asal dan status keanggotaan. | Must | — |
| AGT-03 | Aktivasi anggota setelah verifikasi dan pelunasan simpanan pokok, disertai penerbitan nomor anggota. | Must | OQ-15, OQ-18 |
| AGT-04 | Profil anggota terpadu: data diri, rekening simpanan, pinjaman aktif dan riwayat, jaminan, status kolektibilitas. | Must | — |
| AGT-05 | Perubahan data anggota dengan persetujuan untuk data kunci (NIK, nama, ahli waris) serta riwayat perubahan. | Must | — |
| AGT-06 | Mutasi anggota antar cabang beserta rekening dan pinjamannya, dengan jurnal antar kantor. | Should | OQ-16 |
| AGT-07 | Proses keluar anggota: validasi tidak ada pinjaman atau kewajiban tersisa, perhitungan pengembalian simpanan, persetujuan, pembayaran. | Must | OQ-17 |
| AGT-08 | Penanganan anggota meninggal dunia: penonaktifan, pengembalian simpanan ke ahli waris, penyelesaian sisa pinjaman. | Should | OQ-17 |
| AGT-09 | Status khusus anggota (diblokir, daftar hitam) dengan alasan dan persetujuan. | Should | — |
| AGT-10 | Pencatatan persetujuan pemrosesan data pribadi saat registrasi. | Must | OQ-47 |
| AGT-11 | Profil risiko anggota sesuai prinsip mengenali pengguna jasa, termasuk sumber dana untuk transaksi bernilai besar. | Should | OQ-46 |
| AGT-12 | Cetak kartu anggota. | Could | — |

### M03 — Simpanan

Tujuan: mengelola seluruh produk simpanan, dari pembukaan rekening sampai perhitungan jasa, dengan jurnal otomatis.

Catatan akuntansi: klasifikasi setiap produk (ekuitas atau kewajiban) diatur melalui template jurnal produk dan mengikuti kebijakan akuntansi koperasi yang disepakati dengan akuntan klien (OQ-38).

| ID | Kebutuhan | Prioritas | OQ |
|---|---|---|---|
| SIM-01 | Konfigurasi produk simpanan: jenis (pokok, wajib, sukarela, berjangka), nominal atau minimal setoran, saldo minimal, biaya administrasi, metode dan tarif jasa, akun jurnal. | Must | OQ-19 |
| SIM-02 | Rekening simpanan pokok dan wajib dibuka otomatis saat anggota aktif. Rekening sukarela dan berjangka dibuka atas permintaan anggota. | Must | — |
| SIM-03 | Setoran dan penarikan tunai melalui teller, dengan bukti transaksi. | Must | OQ-22 |
| SIM-04 | Setoran dan penarikan melalui transfer bank, diinput dengan referensi mutasi bank. | Should | OQ-36 |
| SIM-05 | Penarikan di atas batas wewenang memerlukan otorisasi. | Must | OQ-14 |
| SIM-06 | Simpanan pokok dan wajib tidak dapat ditarik selama keanggotaan aktif. | Must | — |
| SIM-07 | Tagihan simpanan wajib per periode dan daftar tunggakan simpanan wajib. | Must | OQ-24 |
| SIM-08 | Perhitungan jasa simpanan sukarela secara periodik sesuai metode produk (saldo harian, saldo terendah, atau saldo rata-rata) dan pengkreditan otomatis. | Must | OQ-20 |
| SIM-09 | Pemotongan pajak atas jasa simpanan dengan tarif dan batas yang dapat dikonfigurasi. | Must | OQ-23 |
| SIM-10 | Simpanan berjangka: tenor, tarif jasa, cara pembayaran jasa (bulanan atau saat jatuh tempo, ke rekening sukarela atau tunai), perpanjangan otomatis, pencairan sebelum jatuh tempo dengan penalti. | Must | OQ-21 |
| SIM-11 | Akrual jasa simpanan berjangka saat tutup bulan. | Must | — |
| SIM-12 | Pemindahbukuan antar rekening simpanan milik anggota yang sama. | Should | — |
| SIM-13 | Pemblokiran sebagian saldo, misalnya sebagai jaminan pinjaman. | Should | OQ-31 |
| SIM-14 | Penandaan rekening pasif setelah tidak ada transaksi dalam periode tertentu. | Could | — |
| SIM-15 | Mutasi rekening anggota yang dapat dicetak atau diunduh. | Must | — |
| SIM-16 | Produk simpanan berencana, misalnya simpanan hari raya atau pendidikan. | Could | OQ-19 |

### M04 — Pinjaman dan jaminan

Tujuan: mengelola siklus pinjaman dari pengajuan sampai lunas dengan kontrol persetujuan berjenjang, serta memantau kualitas pinjaman seluruh cabang.

**Produk dan pengajuan**

| ID | Kebutuhan | Prioritas | OQ |
|---|---|---|---|
| PJM-01 | Konfigurasi produk pinjaman: plafon minimum dan maksimum, tenor, metode jasa (flat, efektif/menurun, anuitas), tarif, biaya provisi, administrasi, dan asuransi, aturan denda, syarat (lama keanggotaan, wajib jaminan, rasio angsuran terhadap penghasilan), akun jurnal. | Must | OQ-25, OQ-28 |
| PJM-02 | Simulasi pinjaman berupa jadwal angsuran dan total biaya sebelum pengajuan. | Must | — |
| PJM-03 | Pengajuan pinjaman oleh CS: data pinjaman, tujuan penggunaan, dokumen pendukung, calon jaminan. | Must | — |
| PJM-04 | Pengecekan otomatis saat pengajuan: status anggota, kelayakan terhadap syarat produk, pinjaman aktif dan tunggakan di seluruh kantor, tunggakan simpanan wajib. | Must | OQ-16, OQ-34 |

**Analisis dan persetujuan**

| ID | Kebutuhan | Prioritas | OQ |
|---|---|---|---|
| PJM-05 | Formulir analisis kredit: hasil survei (catatan dan foto), penilaian 5C, perhitungan kemampuan bayar, rekomendasi plafon dan tenor. | Must | — |
| PJM-06 | Alur persetujuan berjenjang berdasarkan plafon (contoh: kepala cabang → komite kredit pusat → manajer), dengan catatan, persetujuan bersyarat, penolakan, dan pengembalian untuk revisi. | Must | OQ-26 |
| PJM-07 | Pelacakan status pengajuan yang dapat dilihat pusat lintas cabang, termasuk lama waktu di setiap tahap. | Should | — |

**Akad dan pencairan**

| ID | Kebutuhan | Prioritas | OQ |
|---|---|---|---|
| PJM-08 | Pembuatan akad: penerbitan nomor pinjaman, cetak surat perjanjian dan jadwal angsuran dari template. | Must | — |
| PJM-09 | Pencairan tunai melalui teller atau transfer, dengan potongan provisi, administrasi, asuransi, dan simpanan (jika ada) dihitung otomatis. | Must | OQ-33 |
| PJM-10 | Pencairan hanya dapat diproses jika persetujuan lengkap, akad sudah ditandatangani, dan jaminan tercatat telah diterima. | Must | — |

**Angsuran dan pelunasan**

| ID | Kebutuhan | Prioritas | OQ |
|---|---|---|---|
| PJM-11 | Pembayaran angsuran di teller atau melalui transfer, dengan alokasi pembayaran sesuai urutan yang dikonfigurasi. | Must | OQ-27 |
| PJM-12 | Pembayaran sebagian, pembayaran lebih, dan pelunasan dipercepat dengan perhitungan penalti atau potongan jasa. | Must | OQ-27, OQ-30 |
| PJM-13 | Perhitungan denda keterlambatan otomatis sesuai parameter dan kalender kerja. Penghapusan denda memerlukan otorisasi. | Must | OQ-28 |
| PJM-14 | Pembayaran angsuran dengan pendebetan simpanan sukarela anggota, atas instruksi atau otomatis. | Should | — |

**Kolektibilitas dan penagihan**

| ID | Kebutuhan | Prioritas | OQ |
|---|---|---|---|
| PJM-15 | Penentuan kolektibilitas harian berdasarkan jumlah hari tunggakan, dengan kategori dan ambang hari yang dapat dikonfigurasi. | Must | OQ-29 |
| PJM-16 | Perhitungan cadangan kerugian pinjaman saat tutup bulan dan jurnal otomatisnya. | Must | OQ-29, OQ-38 |
| PJM-17 | Perlakuan pendapatan jasa untuk pinjaman bermasalah sesuai kebijakan akuntansi, misalnya penghentian akrual pada kategori tertentu. | Should | OQ-38 |
| PJM-18 | Daftar tagihan dan tunggakan per petugas, pencatatan hasil kunjungan penagihan, janji bayar, dan penerbitan surat peringatan. | Must | OQ-32 |
| PJM-19 | Restrukturisasi (penjadwalan ulang tenor atau angsuran) dengan persetujuan dan riwayat kontrak. | Should | OQ-30 |
| PJM-20 | Top-up atau refinancing: pelunasan pinjaman lama dari pencairan pinjaman baru. | Should | OQ-30 |
| PJM-21 | Hapus buku pinjaman macet dengan persetujuan pusat, serta pencatatan penagihan setelah hapus buku. | Should | OQ-30 |
| PJM-22 | Pengingat jatuh tempo angsuran untuk petugas cabang di dalam aplikasi. | Should | — |
| PJM-23 | Aplikasi mobile kolektor. | Won't (fase 3) | OQ-32 |

**Jaminan**

| ID | Kebutuhan | Prioritas | OQ |
|---|---|---|---|
| PJM-24 | Registrasi jaminan: jenis, dokumen (BPKB, sertifikat, dan lainnya), nilai taksiran, nama pemilik, foto, lokasi penyimpanan. | Must | OQ-31 |
| PJM-25 | Mutasi jaminan: serah terima masuk, peminjaman sementara, pengembalian saat lunas, dengan tanda terima dan riwayat. | Must | OQ-31 |

### M05 — Kas, teller, dan bank

Tujuan: memastikan setiap rupiah uang tunai dan saldo bank di setiap kantor tercatat, dicocokkan setiap hari, dan terlihat oleh pusat.

| ID | Kebutuhan | Prioritas | OQ |
|---|---|---|---|
| KAS-01 | Membuka sesi kas teller dengan saldo awal dari brankas cabang. | Must | OQ-35 |
| KAS-02 | Perpindahan kas antara teller dan brankas, dengan konfirmasi dari kedua pihak. | Must | — |
| KAS-03 | Batas maksimum saldo kas teller disertai peringatan. | Should | OQ-35 |
| KAS-04 | Tutup kas teller: hitung fisik per pecahan, dibandingkan dengan saldo sistem, selisih dicatat dengan otorisasi kepala cabang. | Must | OQ-35 |
| KAS-05 | Selisih kas dijurnal otomatis ke akun selisih kas dan masuk laporan pengecualian. | Must | — |
| KAS-06 | Pengelolaan rekening bank per kantor, termasuk transaksi setor ke bank dan tarik dari bank. | Must | OQ-36 |
| KAS-07 | Rekonsiliasi bank dengan impor mutasi bank (CSV/Excel) dan pencocokan terhadap transaksi sistem. | Should | OQ-36 |
| KAS-08 | Pengeluaran biaya operasional kantor dengan kategori biaya, unggah bukti, dan persetujuan sesuai batas. | Must | OQ-37 |
| KAS-09 | Kas kecil per kantor beserta pengisian kembali. | Should | OQ-37 |
| KAS-10 | Posisi kas dan bank seluruh kantor yang dapat dipantau pusat secara real-time. | Must | — |
| KAS-11 | Laporan mutasi kas harian per teller dan per kantor. | Must | — |

### M06 — Akuntansi dan buku besar

Tujuan: pembukuan per kantor yang terbentuk otomatis dari transaksi, sesuai standar akuntansi yang berlaku bagi koperasi simpan pinjam (SAK Indonesia untuk Entitas Privat).

| ID | Kebutuhan | Prioritas | OQ |
|---|---|---|---|
| AKT-01 | Bagan akun (COA) berjenjang yang sama untuk semua kantor, dengan opsi membatasi akun tertentu hanya untuk pusat. | Must | OQ-38 |
| AKT-02 | Template jurnal otomatis per jenis transaksi dan per produk, dapat dikonfigurasi oleh KAK. | Must | OQ-38 |
| AKT-03 | Jurnal memorial (manual) dengan lampiran bukti dan persetujuan. | Must | OQ-14 |
| AKT-04 | Jurnal pembalik untuk koreksi sesuai ATR-03. | Must | — |
| AKT-05 | Buku besar, buku pembantu, dan neraca saldo per kantor dan konsolidasi, per tanggal atau periode. | Must | — |
| AKT-06 | Laporan posisi keuangan dan laporan hasil usaha per kantor dan konsolidasi. | Must | OQ-38 |
| AKT-07 | Laporan perubahan ekuitas dan laporan arus kas. | Should | OQ-38 |
| AKT-08 | Pencocokan saldo buku pembantu (simpanan, pinjaman) dengan akun kontrol di buku besar, dengan peringatan jika berbeda. | Must | — |
| AKT-09 | Laporan hasil usaha per cabang sebagai pusat laba. | Must | OQ-39 |
| AKT-10 | Register aset tetap dan penyusutan otomatis bulanan. | Should | OQ-41 |
| AKT-11 | Alokasi biaya kantor pusat ke cabang. | Could | OQ-39 |
| AKT-12 | Biaya dibayar di muka beserta amortisasinya. | Could | — |
| AKT-13 | Anggaran per kantor dan perbandingan dengan realisasi. | Could | — |
| AKT-14 | Ekspor data akuntansi ke Excel untuk kebutuhan auditor eksternal dan perpajakan. | Must | — |

### M07 — Antar kantor dan konsolidasi

Tujuan: menggantikan proses keliling cabang dengan konsolidasi otomatis.

Catatan desain: walaupun semua kantor berada di satu basis data, Rekening Antar Kantor (RAK) tetap diperlukan agar setiap cabang memiliki laporan keuangan yang utuh. Keuntungan opsi A adalah kedua sisi jurnal RAK dibentuk oleh sistem secara bersamaan, sehingga selisih RAK karena kelalaian input tidak terjadi.

| ID | Kebutuhan | Prioritas | OQ |
|---|---|---|---|
| RAK-01 | Akun Rekening Antar Kantor dibentuk otomatis untuk setiap pasangan pusat–cabang. | Must | — |
| RAK-02 | Setiap transaksi lintas kantor membentuk jurnal RAK di kedua kantor dalam satu proses yang tidak dapat berhasil sebagian. | Must | OQ-16 |
| RAK-03 | Dropping dana: permintaan dana oleh cabang, persetujuan pusat, pengiriman, dan konfirmasi penerimaan. | Must | OQ-40 |
| RAK-04 | Penyetoran kelebihan likuiditas cabang ke pusat. | Must | OQ-40 |
| RAK-05 | Rekonsiliasi RAK: laporan saldo RAK berpasangan dan daftar transaksi yang belum cocok. | Must | — |
| RAK-06 | Konsolidasi laporan keuangan dengan eliminasi saldo RAK dan transaksi antar kantor. | Must | — |
| RAK-07 | Laporan perbandingan kinerja antar cabang. | Should | — |
| RAK-08 | Input rekap saldo untuk cabang yang belum go-live selama masa transisi, agar konsolidasi tetap dapat disusun. | Should | OQ-08 |

### M08 — Tutup buku dan SHU

Tujuan: memastikan proses harian, bulanan, dan tahunan berjalan konsisten di semua kantor dan dapat dipantau pusat.

Keterangan prioritas: **Must\*** berarti wajib tersedia sebelum tutup tahun buku pertama setelah go-live.

| ID | Kebutuhan | Prioritas | OQ |
|---|---|---|---|
| TTP-01 | Tutup hari cabang dengan prasyarat semua sesi teller ditutup dan tidak ada transaksi menunggu persetujuan. Proses harian (denda, kolektibilitas) dijalankan, lalu tanggal buku maju. | Must | — |
| TTP-02 | Daftar periksa tutup hari yang menampilkan hal yang belum selesai per kantor. | Must | — |
| TTP-03 | Tutup bulan: akrual jasa, pengkreditan jasa simpanan, penyusutan, cadangan kerugian pinjaman, pengecekan RAK dan buku pembantu, penguncian periode. | Must | — |
| TTP-04 | Pemantauan status tutup hari dan tutup bulan seluruh kantor oleh pusat. | Must | — |
| TTP-05 | Pembukaan kembali periode oleh KAK dengan alasan tercatat. | Should | — |
| TTP-06 | Tutup tahun: penutupan akun pendapatan dan beban ke SHU tahun berjalan. | Must\* | OQ-44 |
| TTP-07 | Konfigurasi pos pembagian SHU dan persentasenya sesuai AD/ART. | Must\* | OQ-42 |
| TTP-08 | Perhitungan SHU per anggota: jasa modal proporsional terhadap basis simpanan, jasa usaha proporsional terhadap basis transaksi atau jasa pinjaman. | Must\* | OQ-42 |
| TTP-09 | Simulasi SHU sebelum RAT dan penetapan final setelah disahkan RAT. | Must\* | OQ-44 |
| TTP-10 | Pembayaran SHU anggota secara tunai atau dikreditkan ke rekening simpanan, dengan jurnal otomatis. | Must\* | OQ-43 |
| TTP-11 | Pencatatan dan pelaporan SHU yang belum diambil anggota. | Should | OQ-43 |

### M09 — Laporan dan dashboard

Tujuan: menyediakan informasi yang sebelumnya dikumpulkan dengan kunjungan ke cabang, secara langsung dan konsisten. Laporan keuangan utama tercakup di M06 dan M07.

| ID | Kebutuhan | Prioritas | OQ |
|---|---|---|---|
| LAP-01 | Nominatif simpanan per produk, kantor, dan tanggal. | Must | — |
| LAP-02 | Nominatif pinjaman: sisa pokok, kolektibilitas, jaminan. | Must | — |
| LAP-03 | Daftar tunggakan dan jadwal jatuh tempo. | Must | — |
| LAP-04 | Laporan pencairan dan pelunasan pinjaman per periode. | Must | — |
| LAP-05 | Laporan anggota masuk dan keluar. | Must | — |
| LAP-06 | Rekap transaksi harian per teller dan per kantor. | Must | — |
| LAP-07 | Dashboard pusat: total aset, simpanan, pinjaman beredar, rasio pinjaman bermasalah, posisi kas dan bank, pertumbuhan anggota, per cabang dan konsolidasi. | Must | OQ-02 |
| LAP-08 | Dashboard kepala cabang. | Should | — |
| LAP-09 | Rasio keuangan: likuiditas, rentabilitas, permodalan. | Should | — |
| LAP-10 | Laporan penilaian kesehatan usaha simpan pinjam. | Should | OQ-45 |
| LAP-11 | Paket laporan RAT: laporan keuangan, perkembangan anggota, SHU. | Should | OQ-45 |
| LAP-12 | Laporan berkala untuk kementerian atau dinas sesuai format yang berlaku. | Should | OQ-45 |
| LAP-13 | Laporan transaksi di atas ambang untuk pemantauan prinsip mengenali pengguna jasa. | Should | OQ-46 |
| LAP-14 | Seluruh laporan dapat difilter per kantor dan periode serta diekspor ke Excel dan PDF. | Must | — |
| LAP-15 | Laporan terjadwal yang dikirim ke email. | Could | — |

Catatan: format laporan kepatuhan sengaja tidak dikunci karena regulasi perkoperasian sedang dalam proses perubahan (RUU Perkoperasian yang menggantikan UU No. 25 Tahun 1992 masih dibahas DPR per September 2026, termasuk wacana lembaga pengawas koperasi).

### M10 — Audit dan kontrol

Tujuan: memudahkan pengawasan jarak jauh terhadap seluruh kantor dan mencegah kecurangan.

| ID | Kebutuhan | Prioritas | OQ |
|---|---|---|---|
| AUD-01 | Jejak audit sesuai ATR-09 yang dapat dicari per pengguna, data, dan periode. | Must | — |
| AUD-02 | Laporan pengecualian: pembalikan transaksi, selisih kas, penghapusan denda, pelampauan batas wewenang, pembukaan periode, perubahan parameter. | Must | — |
| AUD-03 | Riwayat login dan percobaan akses gagal. | Must | — |
| AUD-04 | Akses pemeriksa (hanya lihat) ke seluruh data untuk pengawas dan auditor. | Must | — |
| AUD-05 | Peringatan otomatis ke pusat untuk kejadian tertentu, misalnya selisih kas di atas batas atau tutup hari terlambat. | Should | — |
| AUD-06 | Log perubahan hak akses dan parameter sistem. | Must | — |

---

## 9. Alur kunci end-to-end

### 9.1 Pendaftaran anggota

1. CS menginput data calon anggota.
2. Sistem mengecek NIK di seluruh kantor.
3. CS mengunggah dokumen dan mencatat persetujuan pemrosesan data pribadi.
4. Kepala cabang menyetujui pendaftaran.
5. Calon anggota menyetor simpanan pokok (dan simpanan wajib pertama) di teller.
6. Status berubah menjadi aktif, nomor anggota terbit, rekening pokok dan wajib terbuka, jurnal terbentuk otomatis.

### 9.2 Siklus pinjaman

1. CS melakukan simulasi dan menginput pengajuan.
2. Sistem menjalankan pengecekan otomatis lintas kantor.
3. Analis kredit melakukan survei, analisis, dan memberi rekomendasi.
4. Persetujuan berjenjang sesuai plafon (kepala cabang, komite kredit, manajer).
5. Admin cabang menyiapkan akad dan meregistrasi jaminan.
6. Teller mencairkan dana, jurnal terbentuk otomatis.
7. Anggota membayar angsuran berkala. Denda dan kolektibilitas diperbarui setiap tutup hari.
8. Jika menunggak, pinjaman masuk daftar penagihan.
9. Saat lunas, jaminan dikembalikan dengan tanda terima.

### 9.3 Hari operasional cabang

1. Teller membuka sesi kas dengan saldo dari brankas.
2. Transaksi layanan anggota berlangsung.
3. Teller menutup kas dengan hitung fisik.
4. Kepala cabang mengotorisasi selisih kas jika ada.
5. Tutup hari dijalankan, proses harian otomatis berjalan, tanggal buku maju.

### 9.4 Tutup bulan dan konsolidasi

1. Semua kantor menyelesaikan tutup hari terakhir di bulan tersebut.
2. Proses akhir bulan berjalan: akrual, jasa simpanan, penyusutan, cadangan kerugian pinjaman.
3. Sistem mencocokkan buku pembantu dengan buku besar.
4. Staf akuntansi menyelesaikan rekonsiliasi bank dan RAK.
5. KAK mengunci periode.
6. Laporan keuangan per kantor dan konsolidasi tersedia otomatis.

### 9.5 Tutup tahun dan SHU

1. Tutup bulan terakhir tahun buku selesai.
2. Tutup tahun menghasilkan SHU tahun berjalan.
3. KAK menjalankan simulasi pembagian SHU untuk bahan RAT.
4. RAT mengesahkan SHU, pengurus menetapkan di sistem.
5. SHU anggota dibayarkan atau dikreditkan ke rekening simpanan.

### 9.6 Dropping dana

1. Admin cabang mengajukan permintaan dana.
2. KAK atau manajer menyetujui.
3. Pusat mengirim dana melalui bank atau tunai.
4. Cabang mengonfirmasi penerimaan.
5. Jurnal RAK terbentuk di kedua kantor.

---

## 10. Kebutuhan non-fungsional

| ID | Kebutuhan | OQ |
|---|---|---|
| NFR-01 | **Arsitektur.** Aplikasi web terpusat dengan satu basis data, diakses melalui browser dari semua kantor. Tampilan responsif; dashboard dapat dibuka di tablet dan ponsel. | OQ-06, OQ-09 |
| NFR-02 | **Kinerja.** [ASUMSI] Halaman transaksi dan pencarian anggota tampil ≤ 3 detik pada koneksi 10 Mbps. Tutup hari ≤ 15 menit dan tutup bulan ≤ 1 jam untuk volume asumsi. | OQ-05 |
| NFR-03 | **Ketersediaan.** 99,5% pada jam operasional, pemeliharaan terjadwal di luar jam operasional. | — |
| NFR-04 | **Cadangan dan pemulihan.** Backup basis data harian dan backup log transaksi berkelanjutan, disimpan di lokasi terpisah. [ASUMSI] Kehilangan data maksimal 1 jam (RPO) dan pemulihan maksimal 4 jam (RTO). | OQ-09 |
| NFR-05 | **Keamanan.** HTTPS, kata sandi di-hash, kebijakan kata sandi, batas percobaan login, batas waktu sesi, autentikasi dua langkah untuk peran sensitif, enkripsi data sensitif (NIK, dokumen), hak akses minimum. | — |
| NFR-06 | **Pelindungan data pribadi.** Pencatatan persetujuan, pembatasan akses data pribadi, penyamaran NIK di layar yang tidak memerlukannya, prosedur permintaan subjek data, mengacu UU No. 27 Tahun 2022. | OQ-47 |
| NFR-07 | **Retensi data.** [ASUMSI] Data transaksi dan bukti disimpan minimal 10 tahun, mengacu kewajiban penyimpanan dokumen perusahaan. | OQ-47 |
| NFR-08 | **Uji keamanan.** Penetration test sebelum go-live. | — |
| NFR-09 | **Skalabilitas.** Penambahan kantor tidak memerlukan perubahan kode. | — |
| NFR-10 | **Lokalisasi.** Bahasa Indonesia, format Rupiah dan tanggal Indonesia. | OQ-10 |
| NFR-11 | **Perangkat cabang.** Printer bukti transaksi dan pemindai dokumen standar, tanpa ketergantungan perangkat khusus di fase 1. | OQ-22 |
| NFR-12 | **Koneksi.** Dirancang tetap nyaman di koneksi lambat (halaman ringan), tanpa mode offline. | OQ-06 |

---

## 11. Integrasi dan migrasi data

### 11.1 Integrasi

- Fase 1 tidak memiliki integrasi real-time wajib. Transaksi bank dicatat manual dan dicocokkan melalui impor mutasi (KAS-07).
- Kandidat integrasi fase lanjutan: virtual account bank, notifikasi WhatsApp, tanda tangan elektronik atau e-meterai untuk akad.

### 11.2 Migrasi data

[ASUMSI] Cakupan migrasi (OQ-07):

- Data master anggota aktif.
- Saldo setiap rekening simpanan per tanggal cut-off.
- Pinjaman aktif: plafon, sisa pokok, jasa dan denda tertunggak, sisa jadwal angsuran, kolektibilitas, jaminan.
- Saldo awal neraca per kantor per tanggal cut-off, termasuk hasil usaha tahun berjalan sampai cut-off.
- Histori transaksi tidak dimigrasi. Data lama diarsipkan dalam format aslinya.

Proses migrasi: template impor, validasi data, rekonsiliasi saldo migrasi dengan neraca setiap kantor, dan berita acara saldo awal yang ditandatangani klien.

---

## 12. Fase rilis usulan

| Fase | Cakupan |
|---|---|
| **Fase 1 — Go-live inti** | M01, M02, M03, M04 (tanpa mobile kolektor), M05, M06, M07, M08 tutup hari dan tutup bulan, M09 laporan berprioritas Must, M10, migrasi saldo awal |
| **Fase 2 — Penyempurnaan** | Tutup tahun dan SHU, laporan kesehatan dan paket RAT, impor rekonsiliasi bank, register aset tetap, dashboard cabang, restrukturisasi dan hapus buku |
| **Fase 3 — Perluasan kanal** | Aplikasi mobile kolektor, portal atau aplikasi anggota, integrasi virtual account, notifikasi |

Catatan:

- [ASUMSI] Rollout dimulai dari kantor pusat dan 1 cabang percontohan, lalu cabang lain bertahap (OQ-08).
- Jika go-live mendekati akhir tahun buku, tutup tahun dan SHU harus ditarik ke fase 1.

---

## 13. Ringkasan asumsi baseline

| ID | Asumsi | OQ |
|---|---|---|
| A-01 | Koperasi menjalankan simpan pinjam konvensional. | OQ-03 |
| A-02 | 1 kantor pusat dan 9 cabang; pusat tidak melayani transaksi anggota. | OQ-11, OQ-12 |
| A-03 | Seluruh cabang, termasuk teller, bertransaksi langsung di sistem. | OQ-04 |
| A-04 | Koneksi internet cabang memadai selama jam operasional. | OQ-06 |
| A-05 | Volume: hingga 20.000 anggota, 3.000 transaksi per hari, 150 pengguna. | OQ-05 |
| A-06 | Anggota boleh menyetor dan membayar angsuran di cabang mana pun; penarikan dan pengajuan pinjaman hanya di cabang asal. | OQ-16 |
| A-07 | Tahun buku Januari–Desember. | OQ-44 |
| A-08 | Klasifikasi dan kebijakan akuntansi mengikuti kebijakan akuntansi koperasi dan SAK EP; COA disusun bersama akuntan klien. | OQ-38 |
| A-09 | Tidak ada kolektor yang memakai aplikasi di fase 1. | OQ-32 |
| A-10 | Migrasi berupa saldo awal, bukan histori transaksi. | OQ-07 |
| A-11 | Hosting cloud dengan pusat data di Indonesia. | OQ-09 |

---

## 14. Risiko

| Risiko | Dampak | Mitigasi |
|---|---|---|
| Data awal cabang tidak rapi atau saldonya tidak cocok | Go-live tertunda, kepercayaan pengguna turun | Rekonsiliasi saldo sebelum migrasi, berita acara saldo awal |
| Kebiasaan kerja setiap cabang berbeda | Resistensi pengguna, salah input | SOP standar, pelatihan, cabang percontohan |
| Aturan produk dan SHU tidak terdokumentasi di pihak klien | Perhitungan jasa, denda, atau SHU keliru | Workshop parameter, uji paralel perhitungan manual vs sistem selama 1 bulan |
| Koneksi internet cabang tidak stabil | Layanan anggota terhenti | Survei koneksi per cabang saat discovery, koneksi cadangan |
| Perubahan regulasi perkoperasian | Format dan kewajiban laporan berubah | Modul laporan kepatuhan fleksibel, tidak di-hardcode |
| Penambahan fitur di luar fase yang disepakati | Jadwal dan biaya membengkak | Pembagian fase tertulis dan disetujui klien |
| Kebocoran data pribadi anggota | Sanksi hukum dan kerusakan reputasi | NFR-05, NFR-06, uji keamanan |
| Kecurangan internal | Kerugian keuangan | Maker-checker, jejak audit, laporan pengecualian |

---

## 15. Register pertanyaan terbuka

Jenis: **K** = Konfigurasi, **D** = Desain, **S** = Scope (lihat Bagian 0.2). Saat dijawab, ganti "Terbuka" dengan ringkasan jawaban dan tanggalnya.

### 15.1 Umum dan scope

| ID | Pertanyaan | Modul | Asumsi baseline | Dampak jika berbeda | Jenis | Status |
|---|---|---|---|---|---|---|
| OQ-01 | Saat ini cabang mencatat transaksi dengan apa: buku manual, Excel, atau aplikasi tertentu? | Semua, migrasi | Manual atau Excel, tanpa sistem yang perlu diintegrasikan | Jika sudah ada aplikasi: perlu analisis struktur data lama dan rencana migrasi atau integrasi | S | Terbuka |
| OQ-02 | Data apa saja yang dikumpulkan saat kunjungan ke cabang, untuk laporan apa, dan berapa lama prosesnya sekarang? | M07, M09 | Laporan keuangan dan operasional bulanan untuk konsolidasi | Menentukan laporan prioritas fase 1 dan nilai awal metrik keberhasilan | S | Terbuka |
| OQ-03 | Apakah koperasi konvensional, syariah (KSPPS), atau memiliki keduanya? | Semua | Konvensional | Syariah memerlukan akad pembiayaan, bagi hasil, akuntansi syariah, dan kemungkinan pengelolaan dana sosial. Perubahan besar di M03, M04, M06, M08. | D | Terbuka |
| OQ-04 | Apakah seluruh cabang, termasuk teller, siap bertransaksi langsung di sistem? | Semua | Ya | Jika tidak: sebagian cabang hanya mengirim rekap, bergeser ke model hibrida antara opsi A dan B | D | Terbuka |
| OQ-05 | Berapa jumlah anggota, transaksi per hari, pinjaman aktif, dan calon pengguna sistem? | NFR | Lihat A-05 | Ukuran infrastruktur, desain proses batch tutup hari dan bulan | K | Terbuka |
| OQ-06 | Bagaimana kualitas koneksi internet di setiap cabang? | NFR, arsitektur | Stabil selama jam operasional | Jika buruk: perlu mode offline dan sinkronisasi, perubahan arsitektur yang besar | D | Terbuka |
| OQ-07 | Data historis apa yang harus dimigrasi, dan sejak kapan? | Migrasi | Saldo awal per tanggal cut-off | Migrasi histori transaksi menambah waktu dan kebutuhan pembersihan data | S | Terbuka |
| OQ-08 | Go-live serentak di 9 cabang atau bertahap? | M07, fase | Pusat dan 1 cabang percontohan, lalu bertahap | Jika bertahap, RAK-08 menjadi Must. Jika serentak, beban pelatihan dan pendampingan naik. | S | Terbuka |
| OQ-09 | Hosting di cloud atau server milik koperasi? Adakah ketentuan lokasi data? | NFR | Cloud dengan pusat data di Indonesia | Server sendiri memengaruhi backup, keamanan, ketersediaan, dan tanggung jawab operasional | D | Terbuka |
| OQ-10 | Apakah ada cabang di zona waktu berbeda? Adakah hari libur lokal khusus? | M01 | Satu zona waktu WIB, kalender libur nasional | Pengaturan zona waktu per kantor untuk tanggal buku dan jam layanan | K | Terbuka |

### 15.2 Organisasi dan hak akses

| ID | Pertanyaan | Modul | Asumsi baseline | Dampak jika berbeda | Jenis | Status |
|---|---|---|---|---|---|---|
| OQ-11 | Apakah ada cabang pembantu atau kantor kas di bawah cabang? | M01, M07 | Tidak ada | Tambahan tingkat kantor, aturan konsolidasi bertingkat, hak akses per tingkat | D | Terbuka |
| OQ-12 | Apakah kantor pusat juga melayani transaksi anggota? | M01, M05, M07 | Tidak | Pusat memerlukan fungsi teller dan kas seperti cabang, serta pemisahan pembukuan layanan dan manajemen | D | Terbuka |
| OQ-13 | Bagaimana struktur jabatan nyata di cabang? Apakah ada rangkap jabatan? | M01 | Peran terpisah, rangkap jabatan diizinkan dengan kontrol maker-checker | Penyesuaian peran dan pengaturan penyetuju alternatif di cabang kecil | K | Terbuka |
| OQ-14 | Berapa batas wewenang setiap jabatan untuk penarikan, pencairan, jurnal memorial, pembalikan, dan selisih kas? | M01, M03–M06 | Berjenjang dengan nilai dikonfigurasi | Nilai matriks wewenang | K | Terbuka |

### 15.3 Keanggotaan

| ID | Pertanyaan | Modul | Asumsi baseline | Dampak jika berbeda | Jenis | Status |
|---|---|---|---|---|---|---|
| OQ-15 | Apa syarat dan alur penerimaan anggota? Perlukah persetujuan pengurus atau masa calon anggota? | M02 | Aktif setelah verifikasi CS, persetujuan kepala cabang, dan setoran pokok lunas | Tambahan tahap persetujuan atau status calon anggota | K | Terbuka |
| OQ-16 | Bolehkah anggota bertransaksi di cabang lain selain cabang asal? Transaksi apa saja? | M02–M05, M07 | Setor dan angsuran boleh di mana pun; tarik dan pengajuan pinjaman di cabang asal | Mengubah aturan akses data lintas cabang, verifikasi identitas, dan volume transaksi RAK | D | Terbuka |
| OQ-17 | Bagaimana aturan keluar anggota dan anggota meninggal: jangka waktu pengembalian, potongan, penyelesaian pinjaman? | M02, M03, M04 | Pengembalian setelah dikurangi kewajiban, dengan persetujuan pusat | Alur dan perhitungan pengembalian | K | Terbuka |
| OQ-18 | Bolehkah simpanan pokok dicicil? | M02, M03 | Tidak, harus lunas sebelum aktif | Status aktif bersyarat dan tagihan cicilan simpanan pokok | K | Terbuka |

### 15.4 Simpanan

| ID | Pertanyaan | Modul | Asumsi baseline | Dampak jika berbeda | Jenis | Status |
|---|---|---|---|---|---|---|
| OQ-19 | Produk simpanan apa saja yang dimiliki dan bagaimana parameternya? | M03 | Empat jenis: pokok, wajib, sukarela, berjangka | Jumlah dan parameter produk; produk khusus di luar empat jenis tersebut | K | Terbuka |
| OQ-20 | Bagaimana metode perhitungan jasa simpanan sukarela dan kapan dikreditkan? | M03 | Saldo harian, dikreditkan bulanan | Parameter metode perhitungan | K | Terbuka |
| OQ-21 | Bagaimana ketentuan simpanan berjangka: tenor, pembayaran jasa, perpanjangan otomatis, penalti pencairan awal, bilyet? | M03 | Sesuai SIM-10 | Parameter produk; cetak bilyet jika diperlukan | K | Terbuka |
| OQ-22 | Apakah anggota memakai buku tabungan yang dicetak (passbook)? | M03, NFR | Tidak, bukti transaksi berupa slip | Integrasi printer passbook, pencatatan baris cetak, dan pengadaan perangkat | S | Terbuka |
| OQ-23 | Bagaimana perlakuan dan pemotongan pajak atas jasa simpanan yang berlaku bagi koperasi ini? | M03, M06 | Pajak dipotong dengan tarif dan batas yang dapat dikonfigurasi, divalidasi konsultan pajak | Parameter pajak dan laporan pemotongan | K | Terbuka |
| OQ-24 | Bagaimana penanganan tunggakan simpanan wajib? Ditagih, didebet dari simpanan sukarela, atau dipotong saat pencairan pinjaman? | M03, M04 | Ditagih dan dicatat sebagai tunggakan | Aturan pendebetan otomatis | K | Terbuka |

### 15.5 Pinjaman dan jaminan

| ID | Pertanyaan | Modul | Asumsi baseline | Dampak jika berbeda | Jenis | Status |
|---|---|---|---|---|---|---|
| OQ-25 | Produk pinjaman apa saja dan bagaimana parameternya: plafon, tenor, metode jasa, biaya, pembulatan angsuran? | M04 | Konfigurasi sesuai PJM-01 | Parameter produk | K | Terbuka |
| OQ-26 | Bagaimana alur dan batas wewenang persetujuan pinjaman antara cabang dan pusat? | M04 | Berjenjang berdasarkan plafon | Nilai batas dan urutan penyetuju | K | Terbuka |
| OQ-27 | Bagaimana urutan alokasi pembayaran dan perlakuan pembayaran sebagian atau lebih? | M04, M06 | Denda → jasa → pokok; kelebihan menjadi titipan angsuran berikutnya | Parameter alokasi | K | Terbuka |
| OQ-28 | Bagaimana aturan denda: dasar perhitungan, tarif, toleransi hari, batas maksimum? | M04 | Persentase harian dari angsuran tertunggak dengan toleransi hari | Parameter denda | K | Terbuka |
| OQ-29 | Kategori dan ambang hari kolektibilitas apa yang dipakai, serta berapa persentase cadangan kerugian per kategori? | M04, M06 | Lancar, kurang lancar, diragukan, macet, dengan ambang dikonfigurasi | Parameter kategori dan cadangan | K | Terbuka |
| OQ-30 | Apakah koperasi melakukan pelunasan dipercepat, top-up, restrukturisasi, dan hapus buku? Bagaimana aturannya? | M04, M06 | Semua didukung dengan persetujuan pusat | Jika ada skema khusus, alur kontrak dan akuntansi berubah | D | Terbuka |
| OQ-31 | Jenis jaminan apa yang diterima, disimpan di mana (cabang atau pusat), dan apakah dinilai atau diasuransikan? | M04 | Disimpan di cabang pemberi pinjaman | Alur mutasi jaminan antar kantor dan data penilaian | K | Terbuka |
| OQ-32 | Apakah ada kolektor lapangan? Bagaimana hasil tagihan disetor dan dicatat? | M04, M05 | Kolektor menyetor ke teller, teller menginput | Aplikasi mobile kolektor, kas kolektor, dan rekonsiliasi setoran | S | Terbuka |
| OQ-33 | Pencairan dilakukan tunai atau transfer? Adakah potongan simpanan saat pencairan? | M04, M05 | Keduanya didukung, potongan dikonfigurasi per produk | Parameter potongan | K | Terbuka |
| OQ-34 | Apakah pengecekan riwayat pinjaman cukup dari data internal lintas cabang, atau perlu sumber eksternal? | M04 | Data internal lintas cabang | Integrasi pihak ketiga dan persetujuan anggota | S | Terbuka |

### 15.6 Kas dan bank

| ID | Pertanyaan | Modul | Asumsi baseline | Dampak jika berbeda | Jenis | Status |
|---|---|---|---|---|---|---|
| OQ-35 | Bagaimana pengelolaan kas cabang: brankas terpisah dari kas teller, batas kas, hitung per pecahan? | M05 | Brankas dan kas teller terpisah, hitung per pecahan | Penyederhanaan alur kas untuk cabang kecil | K | Terbuka |
| OQ-36 | Berapa rekening bank per kantor, dan apakah mutasi bank bisa diunduh dalam format tertentu? | M05 | Beberapa rekening per kantor, impor manual CSV/Excel | Integrasi API bank atau virtual account | S | Terbuka |
| OQ-37 | Pengeluaran biaya operasional dilakukan di cabang atau terpusat? Adakah anggaran per cabang? | M05, M06 | Di cabang dengan batas dan persetujuan | Jika terpusat: alur permintaan biaya dari cabang dan pembayaran oleh pusat | D | Terbuka |

### 15.7 Akuntansi dan antar kantor

| ID | Pertanyaan | Modul | Asumsi baseline | Dampak jika berbeda | Jenis | Status |
|---|---|---|---|---|---|---|
| OQ-38 | COA dan kebijakan akuntansi apa yang dipakai sekarang, termasuk klasifikasi simpanan dan metode cadangan kerugian? | M03, M04, M06 | COA standar koperasi disesuaikan SAK EP, disusun bersama akuntan klien | Isi COA dan template jurnal | K | Terbuka |
| OQ-39 | Apakah setiap cabang dinilai sebagai pusat laba dengan laporan hasil usaha sendiri? Apakah biaya pusat dialokasikan ke cabang? | M06, M07 | Laporan per cabang lengkap tanpa alokasi biaya pusat | Mekanisme alokasi biaya dan transfer pricing dana antar kantor | D | Terbuka |
| OQ-40 | Siapa yang mengelola likuiditas: dropping dana ke cabang dan penempatan dana di bank? | M05, M07 | Dikelola pusat melalui persetujuan KAK atau manajer | Pengaturan wewenang dan alur | K | Terbuka |
| OQ-41 | Apakah aset tetap perlu dikelola beserta penyusutannya di sistem? | M06 | Ya, prioritas Should | Modul aset tetap dapat dihapus atau ditingkatkan prioritasnya | S | Terbuka |

### 15.8 Tutup buku dan SHU

| ID | Pertanyaan | Modul | Asumsi baseline | Dampak jika berbeda | Jenis | Status |
|---|---|---|---|---|---|---|
| OQ-42 | Bagaimana formula pembagian SHU sesuai AD/ART: pos alokasi, persentase, serta basis jasa modal dan jasa usaha? | M08 | Pos dan persentase dikonfigurasi; basis rata-rata saldo simpanan dan jasa pinjaman yang dibayar | Parameter. Jika basis perhitungan tidak lazim, logika perhitungan perlu disesuaikan. | K | Terbuka |
| OQ-43 | SHU anggota dibayar tunai atau dikreditkan ke simpanan? Bagaimana perlakuan SHU yang tidak diambil? | M08 | Keduanya didukung | Parameter pembayaran | K | Terbuka |
| OQ-44 | Kapan tahun buku dan jadwal RAT? | M01, M08 | Januari–Desember, RAT di kuartal pertama tahun berikutnya | Jadwal fase SHU | K | Terbuka |

### 15.9 Laporan dan kepatuhan

| ID | Pertanyaan | Modul | Asumsi baseline | Dampak jika berbeda | Jenis | Status |
|---|---|---|---|---|---|---|
| OQ-45 | Laporan apa yang wajib dibuat secara rutin dan dalam format apa, untuk pengurus, pengawas, RAT, dan instansi pemerintah? | M09 | Laporan di Bagian M09 dengan format umum | Jumlah dan kompleksitas laporan; kemungkinan format khusus | S | Terbuka |
| OQ-46 | Bagaimana penerapan prinsip mengenali pengguna jasa di koperasi ini, termasuk ambang transaksi yang dipantau? | M02, M09 | Profil risiko anggota dan laporan transaksi di atas ambang | Alur verifikasi tambahan, pemantauan, dan pelaporan | D | Terbuka |
| OQ-47 | Adakah kebijakan retensi data dan persetujuan data pribadi yang sudah dimiliki koperasi? | M02, NFR | Retensi 10 tahun, persetujuan dicatat saat registrasi | Parameter retensi dan teks persetujuan | K | Terbuka |

### 15.10 Prioritas discovery

Pertanyaan jenis D dan S berikut sebaiknya dijawab pada pertemuan pertama dengan klien, karena menentukan bentuk desain dan estimasi:

1. **Penentu arsitektur:** OQ-03 (syariah/konvensional), OQ-04 (kesiapan semua cabang), OQ-06 (koneksi), OQ-09 (hosting).
2. **Penentu struktur organisasi dan data:** OQ-11 (tingkat kantor), OQ-12 (peran pusat), OQ-16 (transaksi lintas cabang), OQ-39 (cabang sebagai pusat laba).
3. **Penentu scope dan estimasi:** OQ-01 (sistem saat ini), OQ-02 (data yang dikumpulkan), OQ-07 (migrasi), OQ-08 (strategi go-live), OQ-22 (passbook), OQ-32 (kolektor), OQ-45 (laporan wajib).
4. **Penentu alur khusus:** OQ-30 (restrukturisasi dan sejenisnya), OQ-34 (pengecekan eksternal), OQ-36 (integrasi bank), OQ-37 (pengeluaran biaya), OQ-41 (aset tetap), OQ-46 (mengenali pengguna jasa).

Pertanyaan jenis K dapat dikumpulkan melalui kuesioner parameter atau workshop terpisah dengan bagian keuangan klien.

---

## 16. Glosarium

| Istilah | Arti |
|---|---|
| AD/ART | Anggaran Dasar dan Anggaran Rumah Tangga koperasi, sumber aturan internal seperti pembagian SHU. |
| Akad | Perjanjian pinjaman antara koperasi dan anggota. |
| Brankas | Tempat penyimpanan kas utama cabang, terpisah dari kas yang dipegang teller. |
| Cadangan kerugian pinjaman | Penyisihan untuk mengantisipasi pinjaman yang tidak tertagih. |
| COA | Bagan akun (chart of accounts). |
| Dropping dana | Pengiriman dana dari pusat ke cabang untuk kebutuhan likuiditas. |
| Jasa | Istilah koperasi untuk bunga simpanan maupun bunga pinjaman. |
| Kolektibilitas | Penggolongan kualitas pinjaman berdasarkan ketepatan pembayaran. |
| Maker-checker | Kontrol di mana transaksi dibuat satu orang dan disetujui orang lain. |
| Nominatif | Daftar rinci rekening simpanan atau pinjaman beserta saldonya. |
| RAK | Rekening Antar Kantor, akun perantara untuk transaksi antara pusat dan cabang. |
| RAT | Rapat Anggota Tahunan, forum tertinggi koperasi untuk pertanggungjawaban dan pengesahan SHU. |
| SAK EP | Standar Akuntansi Keuangan Indonesia untuk Entitas Privat. |
| SHU | Sisa Hasil Usaha, selisih pendapatan dan beban koperasi dalam satu tahun buku. |
| Simpanan berjangka | Simpanan dengan jangka waktu tetap, serupa deposito. |
| Simpanan pokok | Simpanan yang disetor sekali saat menjadi anggota dan tidak dapat ditarik selama masih anggota. |
| Simpanan sukarela | Simpanan yang dapat disetor dan ditarik sewaktu-waktu. |
| Simpanan wajib | Simpanan dengan nominal tetap yang disetor secara berkala. |
| Tanggal buku | Tanggal akuntansi aktif sebuah kantor, maju setelah tutup hari. |
| Tutup hari | Proses akhir hari kantor: tutup kas, proses otomatis harian, dan pergantian tanggal buku. |

---

## 17. Riwayat revisi

| Versi | Tanggal | Perubahan | Oleh |
|---|---|---|---|
| 0.1 | 11 September 2026 | Draft baseline general opsi A | Sivinaries |
