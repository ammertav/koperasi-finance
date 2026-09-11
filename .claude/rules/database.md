---
paths:
  - "database/migrations/**"
  - "app/Models/**"
  - "database/seeders/**"
---

# Aturan database

- Kolom uang: DECIMAL(18,2), tidak nullable. Cast di model: `'decimal:2'`.
- Tabel transaksional wajib punya `office_id` (foreign key ke `offices`) dan `book_date` (DATE), dengan index gabungan (`office_id`, `book_date`).
- Gunakan foreign key constraint. Tidak ada cascade delete untuk data keuangan.
- Jangan pakai SoftDeletes untuk data keuangan. Gunakan kolom `status` yang terdokumentasi (mis. `draft`, `pending_approval`, `posted`, `cancelled`).
- Struktur jurnal minimal:
  - `journals`: `number`, `office_id`, `book_date`, `source_type`, `source_id`, `description`, `status`, `reversal_of_id`, `created_by`
  - `journal_details`: `journal_id`, `account_id`, `debit`, `credit`, `description`
- Nomor dokumen disimpan di kolom `number` dengan unique index, formatnya memuat kode kantor.
- Nama tabel, kolom, dan nilai status berbahasa Inggris. Tabel plural snake_case bawaan Laravel (`savings_accounts`); `protected $table` hanya diisi jika nama tabel berbeda dari tebakan Eloquent.

## Konvensi dari proyek referensi

### Migration
- Tabel inti yang dirujuk FK tabel lain (mis. `offices`, dirujuk `users`) memakai prefix urut `0001_00_{NN}_000000_` agar jalan sebelum migration bawaan `0001_01_01_*`: `0001_00_01_000000_create_offices_table.php`. Tabel lain memakai timestamp dari `php artisan make:migration`.
- Satu migration per tabel (`create_{table}_table`), kelas anonim `return new class extends Migration`, `down()` berisi `Schema::dropIfExists('{table}')`.
- Perubahan tabel dibuat di migration baru `add_{column}_to_{table}_table`, kolom diberi `->after('...')`, dan `down()` membalik dengan `dropForeign([...])` lalu `dropColumn(...)`.
- Urutan kolom: `$table->id()`, foreign key, kolom data, `$table->timestamps()`. Index/unique gabungan ditulis di akhir blok: `$table->unique(['member_id', 'period_start']);`
- FK memakai `foreignId('{singular}_id')->constrained()`; nama tabel disebut eksplisit bila berbeda dari tebakan Laravel: `->constrained('offices')`.
- Status dan tipe memakai `enum` bernilai snake_case Inggris huruf kecil dengan default. Boolean berawalan `is_` dengan default:
  `$table->enum('status', ['draft', 'pending_approval', 'posted', 'cancelled'])->default('draft');` dan `$table->boolean('is_active')->default(true);`
- Nama kolom: tanggal `*_date` atau pasangan `period_start`/`period_end`, jam `*_time`, akumulasi `total_*`.

### Model
- `protected $fillable` selalu diisi eksplisit, satu kolom per baris; tidak memakai `$guarded`.
- Relasi `belongsTo` bernama singular (`office()`), `hasMany` plural camelCase (`savingsAccounts()`); FK non-standar disebut: `belongsTo(Journal::class, 'reversal_of_id')`.
- Atribut turunan memakai accessor `getDurationDaysAttribute()`, dibaca sebagai `$loan->duration_days`.

### Seeder
- Kelas `{Name}Seeder`: data referensi di `database/seeders/`, skenario demo di `database/seeders/Demo/`. Data referensi berupa array literal yang disimpan idempoten: `Account::updateOrCreate(['code' => $account['code']], $account);`
- Akun demo memakai `updateOrCreate(['email' => '...'], [...])`, dengan kata sandi per pengguna dari konstanta `PASSWORDS` di `UserSeeder` (bukan `.env`).
- FK dicari lewat kode, bukan ID hardcode: `'office_id' => Office::where('code', '07')->value('id')`.
- `DatabaseSeeder` memanggil semua seeder sesuai urutan dependensi. Seeder baru dari tiap milestone disisipkan di posisinya:
```php
$this->call([OfficeSeeder::class, RoleSeeder::class, AuthorityMatrixSeeder::class, UserSeeder::class,
    AccountSeeder::class, JournalTemplateSeeder::class, SavingsProductSeeder::class, LoanProductSeeder::class,
    Demo\MemberSeeder::class, Demo\TransactionHistorySeeder::class, Demo\DemoScenarioSeeder::class]);
```
- Urutan tetap: kantor → peran dan matriks wewenang → pengguna → COA → template jurnal → produk → anggota → histori transaksi 6 bulan (lewat `JournalService`) → skenario babak demo.

### Konflik (CLAUDE.md berlaku)
- Referensi: uang `decimal(15, 2)` tanpa cast. Berlaku: DECIMAL(18,2) dengan cast `'decimal:2'`.
- Referensi: hampir semua FK `->onUpdate('cascade')->onDelete('cascade')`. Berlaku: tanpa cascade delete untuk data keuangan.
- Referensi: kolom tenant `compani_id` jarang di-index. Berlaku: `office_id` + `book_date` dengan index gabungan.
- Referensi: seeder menulis semua model langsung dan tidak didaftarkan di `DatabaseSeeder`. Berlaku: jurnal data historis diposting lewat `JournalService` (CLAUDE.md #11) dan semua seeder dipanggil dari `DatabaseSeeder`.

### Keputusan lain (mengikuti referensi)
- Migration mempertahankan docblock `/** Run the migrations. */` dan `/** Reverse the migrations. */`. Model yang punya factory memakai `use HasFactory;`.
- Kolom alamat dan catatan memakai `text`; isi panjang (konten, pengumuman) memakai `longText`.
