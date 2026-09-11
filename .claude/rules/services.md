---
paths:
  - "app/Services/**"
  - "app/Http/Controllers/**"
  - "database/seeders/**"
---

# Aturan transaksi dan service

- Satu method controller mewakili satu transaksi bisnis, mis. `DepositController::store()`. Logikanya ditulis di controller dan dibungkus transaksi manual:
```php
DB::beginTransaction();
try { /* kunci saldo, tulis transaksi, $this->journalService->post(...) */ DB::commit(); }
catch (\Exception $e) { DB::rollBack(); return back()->withErrors(['msg' => 'Transaksi gagal: '.$e->getMessage()])->withInput(); }
```
- Urutan di dalam `try`: cek aturan bisnis → kunci baris saldo dengan `lockForUpdate()` → tulis transaksi dan saldo → posting jurnal melalui `JournalService` → `DB::commit()`. Audit tercatat otomatis oleh observer/trait, tidak dipanggil manual.
- Logika yang dipakai lebih dari satu controller atau oleh seeder dipindah ke `app/Services/{Domain}/{Nama}Service.php` dan di-inject lewat constructor: `public function __construct(private JournalService $journalService) {}`
- Cek aturan bisnis di service mengembalikan hasil ke controller, lalu controller menolak dengan `back()->withErrors(['msg' => $check['message']])->withInput()`. Bentuk hasil: `['allowed' => false, 'message' => 'Saldo tidak mencukupi.']`
- Invarian akuntansi melempar exception, bukan mengembalikan array: `JournalService` melempar exception jika total debit ≠ kredit sehingga `catch` me-rollback.
- Pengecekan batas wewenang melalui service otorisasi yang membaca matriks wewenang, bukan kondisi `if` tersebar di controller.
- Setelah `DB::commit()`, invalidate cache agregat dashboard untuk kantor terkait.
- Seeder data historis membuat transaksi dengan urutan yang sama seperti controller dan memposting jurnal lewat `JournalService`; jurnal dan saldo tidak di-insert langsung.
