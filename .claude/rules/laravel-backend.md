---
paths:
  - "app/**"
  - "routes/**"
---

# Konvensi backend Laravel

## Penamaan dan struktur
- Semua nama di kode berbahasa Inggris. Controller `{Model}Controller` (singular PascalCase) di `app/Http/Controllers/{Domain}/`, satu per resource. Tidak ada FormRequest dan tidak ada `Route::resource`.
- Method CRUD bernama standar: `index`, `create`, `store`, `show`, `edit`, `update`, `destroy`. Aksi lain camelCase diawali kata kerja: `approveWithoutDeduct`, `batchUpdate`, `exportReport`.
- Service berakhiran `Service`, di-inject lewat constructor promotion: `public function __construct(private JournalService $journalService) {}`
- Trait bersama di `app/Http/Traits/`. Middleware bernama `Ensure{Kondisi}` dengan alias di `bootstrap/app.php`: `$middleware->alias(['office.active' => EnsureOfficeActive::class]);`
- Variabel camelCase: koleksi plural (`$members`), record tunggal singular (`$member`), nilai sebelum update `$old{Field}`.
- Data ke view dikirim dengan `compact()`: `return view('member.member', compact('members', 'offices'));`

## Route
- Ditulis eksplisit per modul, diawali komentar nama modul huruf besar, di dalam grup middleware auth:
```php
// SAVINGS ACCOUNT
Route::get('/savings-account', [SavingsAccountController::class, 'index'])->name('savingsAccount');
Route::post('/post-savings-account', [SavingsAccountController::class, 'store'])->name('postSavingsAccount');
Route::put('/savings-account/{id}/update', [SavingsAccountController::class, 'update'])->name('updateSavingsAccount');
Route::delete('/savings-account/{id}/delete', [SavingsAccountController::class, 'destroy'])->name('delSavingsAccount');
```
- Nama route index = nama resource camelCase. Aksi mengikuti CLAUDE.md: `post{Resource}`, `update{Resource}`, `del{Resource}`, dan `detail{Resource}` untuk show.
- Semua segmen URL kebab-case: store `POST /post-{resource}`, show `GET /{resource}/{id}/show`, update `PUT /{resource}/{id}/update`, hapus `DELETE /{resource}/{id}/delete`, aksi lain `/{resource}/{id}/approve`. Parameter `{id}` diterima sebagai `$id`, tanpa route model binding.

## Controller: logika, validasi, response, error
- Logika bisnis ditulis di controller; transaksi keuangan mengikuti pola `DB::beginTransaction()`/`try`/`catch` di services.md.
- Validasi inline dengan aturan string berpipa: `'office_id' => 'required|exists:offices,id'`.
- Checkbox dibaca dengan `$request->has('is_active')` atau `$request->boolean('is_active')`, bukan dari hasil validasi.
- Record untuk `update` diambil dengan `firstOrFail()`/`findOrFail()` agar 404 otomatis. Record anak milik pihak lain ditolak dengan `abort(403, 'Unauthorized Action')`.
- Sukses: redirect ke route index dengan flash key `success` berbahasa Indonesia. Halaman anak (nested) memakai `back()`.
  `return redirect(route('member'))->with('success', 'Anggota berhasil diperbarui!');`
- Gagal aturan bisnis: `return back()->withErrors(['msg' => 'Pesan'])->withInput();` (key `msg`).
- Flash dan error validasi tidak dirender manual. Paket composer `realrashid/sweet-alert` menampilkan `success` dan `$errors` sebagai SweetAlert lewat middleware di `bootstrap/app.php`: `$middleware->web([\RealRashid\SweetAlert\Http\Middleware\ToSweetAlert::class]);` (namespace v8). Login/logout memakai key `toast_success`.
- JSON (`api/*`) memakai trait `ApiResponse`: `{success, message, data}` atau `{success: false, message, errors}`. 401/404/422 untuk `api/*` dirender JSON di `App\Exceptions\Handler`.

## Konflik (CLAUDE.md berlaku)
- Referensi: `Cache::tags([...])->remember($key, 180, ...)` di setiap `index` plus `clearCache()`. Berlaku: CLAUDE.md #8, saldo/jurnal/transaksi tidak di-cache.
- Referensi: `private function logActivity()` disalin di tiap controller. Berlaku: CLAUDE.md #10, audit trail terpusat (trait/observer).
- Referensi: scoping manual `->where('compani_id', $userCompany->id)` di tiap query. Berlaku: CLAUDE.md #5, global scope `office_id`.
- Referensi: `destroy` menghapus record langsung dan tanggal memakai `now()`. Berlaku: CLAUDE.md #4 (jurnal pembalik) dan #6 (`offices.book_date`).
- Referensi: controller dan service flat tanpa subfolder. Berlaku: `app/Http/Controllers/{Domain}/` dan `app/Services/{Domain}/`.

## Keputusan lain (mengikuti referensi)
- Hasil validasi selalu disimpan ke `$data = $request->validate([...])` lalu dipakai lewat `$data['field']`.
- `destroy` mengambil record dengan `firstOrFail()`. Redirect memakai `redirect(route('x'))`. `Auth::user()` di `index`, `auth()->user()` di aksi tulis.
- Komentar dan docblock berbahasa Indonesia, singkat, hanya untuk logika yang tidak jelas dari kodenya.
- Saat `APP_DEBUG=false`, error 5xx di-redirect ke login dengan `toast_error` (`bootstrap/app.php`); `ValidationException` dan `AuthenticationException` dikecualikan agar tidak dianggap 500.
