# Sistem Keuangan KSP Multi Cabang — Prototype

## Tentang proyek

Prototype aplikasi keuangan terpusat untuk koperasi simpan pinjam (KSP) dengan 1 kantor pusat dan 9 cabang. Prototype ini akan didemokan ke calon klien sebelum discovery, dan dibangun di atas asumsi baseline PRD, bukan kebutuhan final klien.

**Mode saat ini: prototype tampilan (sejak 11 Sep 2026).**
- Milestone 0–1 sudah dibangun full MVC dan tetap berjalan: autentikasi, peran, pengalih peran demo, mesin akuntansi.
- Milestone 2 dan seterusnya dibangun sebagai tampilan dengan data contoh hardcoded, tanpa tabel, service, seeder, atau test baru.
- Tujuannya menunjukkan gambaran aplikasi ke klien dengan biaya pembangunan serendah mungkin.

Panduan full MVC (aturan domain, struktur kode, milestone asli, cara melanjutkan) disimpan di `docs/prototype/FULL-MVC.md`. Baca file itu hanya jika mengubah kode nyata M0–M1 atau jika Andreas meminta melanjutkan ke full MVC.

@docs/prototype/BRIEF.md
@docs/prototype/PROGRESS.md

## Dokumen rujukan (baca bagian yang dibutuhkan saja)

- `docs/prd/PRD-KSP-Multi-Cabang-Baseline-v0.1.md` adalah PRD lengkap. Cari ID kebutuhan (mis. `PJM-06`) dengan grep, lalu baca bagian terkait.
  - Bagian 5: peran dan matriks akses
  - Bagian 6: aturan lintas modul (ATR-xx)
  - Bagian 8: kebutuhan per modul
  - Bagian 9: alur end-to-end
  - Bagian 13: asumsi baseline
  - Bagian 16: glosarium
- PRD dikelola Andreas. Jangan mengubah file PRD. Tulis usulan perubahan di PROGRESS.md.

## Stack

- Laravel (versi mengikuti composer.json), MySQL 8, Redis (session, cache, queue)
- Tailwind CSS 4 melalui Vite
- Library JS via CDN, bukan npm: jQuery 3.7.1, DataTables 2.0.2, SweetAlert2 v11, Chart.js 4.4.0
- Flash message dan error validasi tampil sebagai SweetAlert melalui paket composer `realrashid/sweet-alert`
- Blade dengan komponen Blade


## Aturan prototype tampilan

1. Layar mockup tidak memakai migration, model, service, seeder, atau test baru.
2. Data contoh ditulis di `app/Mockups/{Domain}Mockup.php` sebagai kelas dengan method statis yang mengembalikan array. Controller tipis mengambil data itu dan mengirimnya ke view dengan `compact()`. Jangan menulis data contoh langsung di Blade.
3. Kunci array memakai nama kolom dan nilai status yang direncanakan untuk full MVC (Inggris, snake_case, mis. `status => 'pending_approval'`) agar mudah diganti model nanti.
4. Data contoh dan angka turunannya harus konsisten:
   - Data dibuat deterministik, misalnya Faker `id_ID` dengan seed tetap.
   - Angka turunan (total, saldo, jumlah per cabang, rasio kolektibilitas) dihitung dari data yang sama, bukan diketik manual, supaya dashboard, laporan, dan halaman detail saling cocok.
   - Jurnal contoh seimbang, neraca seimbang, dan RAK konsolidasi nol.
   - Nominal berupa integer rupiah penuh.
5. Form tidak menyimpan ke database:
   - Submit ke route POST yang memvalidasi ringan, lalu redirect dengan flash `success`. Alternatifnya, aksi JavaScript murni.
   - State demo sederhana (mis. status pengajuan pinjaman babak 2) boleh disimpan di session agar alur tetap nyambung saat berganti peran. Pengalih peran meregenerasi session tanpa menghapus isinya.
6. Interaksi kunci naskah demo harus terasa nyata dengan skenario tetap:
   - cek NIK mengenali NIK skenario babak 1;
   - simulasi angsuran flat/anuitas dan hitung pecahan kas dihitung di JavaScript;
   - eskalasi persetujuan mengikuti batas di `config/demo.php`.
7. Menu dan halaman tetap dijaga middleware `module.access` dan peran dari M0, sehingga berganti peran mengubah menu dan tombol yang tampil. Pembatasan per kantor pada mockup cukup dengan memfilter data contoh berdasarkan kantor pengguna yang login.
8. Tampilan mengikuti `.claude/rules/blade-ui.md` dan `.claude/rules/ui-design.md`, dan harus terlihat seperti aplikasi jadi.
9. Kode nyata M0–M1 hanya diubah bila perlu, misalnya menambah menu sidebar atau route. Jika diubah, aturan domain di `docs/prototype/FULL-MVC.md` berlaku dan test yang ada harus tetap lulus.
10. `.claude/rules/services.md` dan `.claude/rules/database.md` hanya berlaku untuk kode nyata. `.claude/rules/laravel-backend.md` tetap berlaku untuk route, penamaan, controller, dan flash message.

## Struktur kode

- `app/Http/Controllers/{Domain}/` — controller tipis: ambil data mockup, kembalikan view atau redirect
- `app/Mockups/` — data contoh per domain
- `resources/views/{domain}/` — Blade; JS halaman di `public/modal/{resource}.js`
- Domain nyata: Organization, Accounting. Domain mockup: Member, Savings, Loan, Cash, Closing, Report, Audit.
- `app/Services/`, `app/Models/`, `database/` — hanya untuk kode nyata M0–M1

## Penamaan

- Kode berbahasa Inggris: class, model, tabel, kolom, dan nilai status. Istilah glosarium PRD dipetakan: Kantor → `Office`, Anggota → `Member`, Rekening Simpanan → `SavingsAccount`, Pinjaman → `Loan`, Jurnal → `Journal`.
- Nama tabel plural snake_case bawaan Laravel (`savings_accounts`). `protected $table` hanya diisi jika nama tabel berbeda dari tebakan Eloquent.
- Nama route untuk aksi: `post{Resource}`, `update{Resource}`, `del{Resource}` (mis. `postMember`).


## Cara kerja sesi

- Kerjakan satu milestone (atau satu fitur) per sesi.
- Sebelum menulis kode untuk milestone baru, buat rencana singkat: layar yang dibuat, babak demo dan ID PRD yang ditunjukkan, serta interaksi yang disimulasikan. Tunggu persetujuan sebelum mulai.
- Jika PRD ambigu, pakai asumsi baseline PRD dan catat di PROGRESS.md. Bertanya hanya jika benar-benar menghalangi pekerjaan.
- Setelah milestone selesai:
  - Jalankan `php artisan view:cache` untuk menangkap error Blade, lalu `php artisan view:clear`.
  - Pastikan route baru terdaftar (`php artisan route:list`).
  - Jalankan `php artisan test` hanya jika kode nyata M0–M1 diubah.
  - Ingatkan Andreas menjalankan `npm run build` bila ada kelas Tailwind baru.
  - Perbarui PROGRESS.md: yang selesai, keputusan, langkah berikutnya.

===

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application running on PHP 8.3. You are an expert with the Laravel ecosystem. Always use the APIs that match the installed major version of each package — do not assume a version.

Before relying on a package's API, confirm its installed version:
- PHP packages: run `composer show --direct` to list direct dependencies with versions, or `composer show <vendor/package>` for a single package.
- JS packages: check `package.json` for the installed versions.

## Skills Activation

This project has domain-specific skills available in `**/skills/**`. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Use `search-docs` before changes that depend on Laravel ecosystem APIs, behavior, configuration, or version-specific syntax. Skip it for copy-only edits and other changes where package documentation is irrelevant. Reuse sufficient results already in context instead of searching again.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Project Rules

- This project contains committed, area-grouped rules in `.ai/rules` when that directory exists (settled decisions, non-obvious traps, standing constraints). Framework and package guidelines that only apply to specific paths (testing, frontend, components) also live there, under `.ai/rules/boost` — this is not just recorded decisions, it is load-bearing guidance you have not seen inline. Before you enter plan mode or create/edit any file, you MUST first: open @.ai/rules/index.md (it maps file globs to rule files), read every rule file whose globs cover the path(s) in scope, and run `grep -rin 'keyword' .ai/rules` to catch what a path match alone misses. Do not write code until you have read and are following every matching rule. If `.ai/rules` does not exist, continue without it.
- Record a rule with `record-rule` only when the user explicitly asks for one. Instructions for the work at hand are not rules, no matter how emphatic: "remove this typo", "use X here" are work to do, not rules to record. Never record a rule on your own initiative, as a byproduct of a change, or to summarize what you just did. When the user does ask, pass a `glob` (e.g. `app/Http/Controllers/**`), a short `title`, and a few-line `note`. Use `record-rule` rather than your native memory or notes tool, because native memory is personal and session-scoped, while only `.ai/rules` is shared with the team and persists in the repo.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.
- Activate the `deploying-to-cloud` skill whenever deploying to Laravel Cloud, configuring Cloud environments or resources, using the Cloud CLI, or troubleshooting Cloud deployments.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== pest/core rules ===

# Pest

- This project uses Pest. Create tests with `php artisan make:test --pest {name}`.
- Do not include the test suite directory in `{name}`. Use `SomeFeatureTest`, not `Feature/SomeFeatureTest`.
- Read the `testing-best-practices` skill for guidance on coverage, naming, structure, dependency isolation, and review.
- Do not delete tests or test files without approval. They are part of the application.

## Running Tests

- Run the narrowest set of tests that covers the change. Pass a file path or `--filter=testName` to `php artisan test --compact`.
- Rerun a test after each change to it.
- Run `vendor/bin/pest` to call the test runner directly. It accepts the same file path and `--filter=testName` arguments.
- After the feature tests pass, ask the user to run the complete suite with `php artisan test --compact`.

</laravel-boost-guidelines>
