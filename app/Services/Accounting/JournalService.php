<?php

namespace App\Services\Accounting;

use App\Models\Account;
use App\Models\Journal;
use App\Models\JournalDetail;
use App\Models\JournalTemplate;
use App\Models\Office;
use App\Models\Scopes\OfficeScope;
use App\Models\User;
use App\Services\Organization\DocumentNumberService;
use DomainException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Satu-satunya jalan untuk membentuk jurnal double-entry (ATR-02, ATR-03, ATR-12).
 * Setiap pelanggaran invarian akuntansi melempar DomainException agar pemanggil me-rollback.
 */
class JournalService
{
    public function __construct(
        private DocumentNumberService $documentNumberService,
        private InterOfficeAccountService $interOfficeAccountService,
    ) {}

    /**
     * Posting jurnal satu kantor pada tanggal buku aktif kantor tersebut.
     *
     * @param  array<int, array{account_id: int, debit?: string|int, credit?: string|int, description?: string|null}>  $lines
     */
    public function post(
        Office $office,
        array $lines,
        string $description,
        ?string $transactionType = null,
        ?Model $source = null,
        ?User $createdBy = null,
    ): Journal {
        return DB::transaction(function () use ($office, $lines, $description, $transactionType, $source, $createdBy) {
            $office = $this->lockOffice($office);
            $normalizedLines = $this->normalizeLines($office, $lines, allowInterOffice: false);
            $this->assertBalanced($normalizedLines);

            return $this->writeJournal(
                $office,
                $normalizedLines,
                $this->journalAttributes($description, $transactionType, $source, $createdBy)
            );
        });
    }

    /**
     * Posting jurnal dari template per jenis transaksi dan produk (AKT-02). Template khusus produk
     * didahulukan dari template umum. Jika kantor tujuan berbeda dari kantor asal, jurnal RAK dibentuk otomatis.
     *
     * @param  array<string, string|int>  $amounts  Nominal per amount_key template, mis. ['principal' => '35000000'].
     */
    public function postFromTemplate(
        string $transactionType,
        ?string $productCode,
        Office $origin,
        ?Office $destination,
        array $amounts,
        string $description,
        ?Model $source = null,
        ?User $createdBy = null,
    ): Journal {
        $template = $this->findTemplate($transactionType, $productCode);
        $isInterOffice = $destination !== null && $destination->getKey() !== $origin->getKey();
        $originLines = [];
        $destinationLines = [];

        foreach ($template->lines as $templateLine) {
            if (! array_key_exists($templateLine->amount_key, $amounts)) {
                throw new DomainException("Nominal \"{$templateLine->amount_key}\" wajib diisi untuk template {$template->code}.");
            }

            $amount = $this->money($amounts[$templateLine->amount_key]);

            if (bccomp($amount, '0', 2) === 0) {
                continue;
            }

            $line = [
                'account_id' => $templateLine->account_id,
                $templateLine->side => $amount,
                'description' => $templateLine->description,
            ];

            if ($isInterOffice && $templateLine->office_role === 'destination') {
                $destinationLines[] = $line;
            } else {
                $originLines[] = $line;
            }
        }

        if (! $isInterOffice) {
            return $this->post($origin, $originLines, $description, $transactionType, $source, $createdBy);
        }

        return $this->postInterOffice($origin, $originLines, $destination, $destinationLines, $description, $transactionType, $source, $createdBy);
    }

    /**
     * Posting transaksi yang melibatkan dua kantor. Baris per kantor tidak perlu seimbang sendiri;
     * selisihnya ditutup akun RAK di setiap kantor dalam satu DB transaction (RAK-02).
     * Transaksi antar dua cabang dilewatkan kantor pusat sehingga membentuk tiga jurnal.
     *
     * @param  array<int, array{account_id: int, debit?: string|int, credit?: string|int, description?: string|null}>  $originLines
     * @param  array<int, array{account_id: int, debit?: string|int, credit?: string|int, description?: string|null}>  $destinationLines
     * @return Journal Jurnal di kantor asal.
     */
    public function postInterOffice(
        Office $origin,
        array $originLines,
        Office $destination,
        array $destinationLines,
        string $description,
        ?string $transactionType = null,
        ?Model $source = null,
        ?User $createdBy = null,
    ): Journal {
        if ($origin->getKey() === $destination->getKey()) {
            throw new DomainException('Kantor asal dan kantor tujuan transaksi antar kantor harus berbeda.');
        }

        return DB::transaction(function () use ($origin, $originLines, $destination, $destinationLines, $description, $transactionType, $source, $createdBy) {
            $origin = $this->lockOffice($origin);
            $destination = $this->lockOffice($destination);
            $originLines = $this->normalizeLines($origin, $originLines, allowInterOffice: false);
            $destinationLines = $this->normalizeLines($destination, $destinationLines, allowInterOffice: false);

            $originNet = $this->net($originLines);
            $destinationNet = $this->net($destinationLines);

            if (bccomp(bcadd($originNet, $destinationNet, 2), '0', 2) !== 0) {
                throw new DomainException('Jurnal antar kantor tidak seimbang: selisih kantor asal Rp '.$this->format($originNet)
                    .' dan kantor tujuan Rp '.$this->format($destinationNet).'.');
            }

            if (bccomp($originNet, '0', 2) === 0) {
                throw new DomainException('Transaksi antar kantor harus memindahkan nilai antar kantor.');
            }

            // Setiap kaki berisi kantor buku dan barisnya; RAK ditambahkan sebelum ditulis.
            if ($origin->type === 'head_office' || $destination->type === 'head_office') {
                $originLines[] = $this->interOfficeLine($origin, $destination, $originNet);
                $destinationLines[] = $this->interOfficeLine($destination, $origin, $destinationNet);
                $legs = [[$origin, $originLines], [$destination, $destinationLines]];
            } else {
                $headOffice = $this->lockOffice($this->interOfficeAccountService->headOffice());
                $originLines[] = $this->interOfficeLine($origin, $headOffice, $originNet);
                $destinationLines[] = $this->interOfficeLine($destination, $headOffice, $destinationNet);
                $headOfficeLines = [
                    $this->interOfficeLine($headOffice, $origin, bcmul($originNet, '-1', 2)),
                    $this->interOfficeLine($headOffice, $destination, bcmul($destinationNet, '-1', 2)),
                ];
                $legs = [[$origin, $originLines], [$headOffice, $headOfficeLines], [$destination, $destinationLines]];
            }

            $attributes = $this->journalAttributes($description, $transactionType, $source, $createdBy)
                + ['inter_office_group' => (string) Str::uuid()];

            foreach ($legs as [$legOffice, $legLines]) {
                $this->assertBalanced($this->normalizeLines($legOffice, $legLines, allowInterOffice: true));
            }

            $journals = array_map(
                fn (array $leg) => $this->writeJournal($leg[0], $leg[1], $attributes),
                $legs
            );

            return $journals[0];
        });
    }

    /**
     * @return array{allowed: bool, message: string|null}
     */
    public function canReverse(Journal $journal): array
    {
        if ($journal->status !== 'posted') {
            return ['allowed' => false, 'message' => 'Hanya jurnal berstatus diposting yang dapat dibalik.'];
        }

        if ($journal->reversal_of_id !== null) {
            return ['allowed' => false, 'message' => 'Jurnal pembalik tidak dapat dibalik lagi.'];
        }

        $isReversed = Journal::withoutGlobalScope(OfficeScope::class)
            ->whereIn('reversal_of_id', $this->journalGroup($journal)->pluck('id'))
            ->exists();

        if ($isReversed) {
            return ['allowed' => false, 'message' => "Jurnal {$journal->number} sudah dibalik."];
        }

        return ['allowed' => true, 'message' => null];
    }

    /**
     * Bentuk jurnal pembalik yang menukar debit dan kredit pada tanggal buku aktif kantor (ATR-03, AKT-04).
     * Untuk transaksi antar kantor, semua jurnal dalam grupnya ikut dibalik.
     *
     * @return Journal Jurnal pembalik untuk $journal.
     */
    public function reverse(Journal $journal, string $reason, ?User $createdBy = null): Journal
    {
        return DB::transaction(function () use ($journal, $reason, $createdBy) {
            $originalJournals = $this->journalGroup($journal, lock: true);
            $check = $this->canReverse($journal);

            if (! $check['allowed']) {
                throw new DomainException($check['message']);
            }

            $interOfficeGroup = $journal->inter_office_group ? (string) Str::uuid() : null;
            $requestedReversal = null;

            foreach ($originalJournals as $originalJournal) {
                $office = $this->lockOffice($originalJournal->office);

                $lines = JournalDetail::withoutGlobalScope(OfficeScope::class)
                    ->where('journal_id', $originalJournal->id)
                    ->orderBy('id')
                    ->get()
                    ->map(fn (JournalDetail $detail) => [
                        'account_id' => $detail->account_id,
                        'debit' => $detail->credit,
                        'credit' => $detail->debit,
                        'description' => $detail->description,
                    ])
                    ->all();

                $lines = $this->normalizeLines($office, $lines, allowInterOffice: true);
                $this->assertBalanced($lines);

                $reversal = $this->writeJournal($office, $lines, [
                    ...$this->journalAttributes(
                        Str::limit("Pembalikan {$originalJournal->number}: {$reason}", 250),
                        'reversal',
                        null,
                        $createdBy
                    ),
                    'source_type' => $originalJournal->source_type,
                    'source_id' => $originalJournal->source_id,
                    'reversal_of_id' => $originalJournal->id,
                    'inter_office_group' => $interOfficeGroup,
                ]);

                if ($originalJournal->is($journal)) {
                    $requestedReversal = $reversal;
                }
            }

            return $requestedReversal;
        });
    }

    private function findTemplate(string $transactionType, ?string $productCode): JournalTemplate
    {
        return JournalTemplate::with('lines')
            ->where('transaction_type', $transactionType)
            ->where('is_active', true)
            ->where(fn ($query) => $query->where('product_code', $productCode)->orWhereNull('product_code'))
            ->orderByRaw('product_code IS NULL')
            ->first()
            ?? throw new DomainException('Template jurnal untuk transaksi '
                .(Journal::TRANSACTION_TYPES[$transactionType] ?? $transactionType)
                .($productCode ? " produk {$productCode}" : '').' belum tersedia.');
    }

    /**
     * @return Collection<int, Journal>
     */
    private function journalGroup(Journal $journal, bool $lock = false): Collection
    {
        return Journal::withoutGlobalScope(OfficeScope::class)
            ->when(
                $journal->inter_office_group,
                fn ($query) => $query->where('inter_office_group', $journal->inter_office_group),
                fn ($query) => $query->whereKey($journal->getKey())
            )
            ->when($lock, fn ($query) => $query->lockForUpdate())
            ->orderBy('id')
            ->get();
    }

    /**
     * Ambil ulang kantor dari database agar tanggal buku yang dipakai adalah yang aktif saat posting (ATR-04).
     */
    private function lockOffice(Office $office): Office
    {
        $lockedOffice = Office::withoutGlobalScope(OfficeScope::class)
            ->whereKey($office->getKey())
            ->sharedLock()
            ->firstOrFail();

        if (! $lockedOffice->is_active) {
            throw new DomainException("Kantor {$lockedOffice->name} tidak aktif.");
        }

        return $lockedOffice;
    }

    /**
     * @param  array<int, array<string, mixed>>  $lines
     * @return array<int, array{account_id: int, debit: string, credit: string, description: string|null}>
     */
    private function normalizeLines(Office $office, array $lines, bool $allowInterOffice): array
    {
        $accounts = Account::whereIn('id', array_column($lines, 'account_id'))->get()->keyBy('id');
        $normalizedLines = [];

        foreach ($lines as $line) {
            $account = $accounts->get($line['account_id'] ?? null)
                ?? throw new DomainException('Akun jurnal tidak ditemukan di bagan akun.');

            $this->assertAccountUsable($account, $office, $allowInterOffice);

            $debit = $this->money($line['debit'] ?? '0');
            $credit = $this->money($line['credit'] ?? '0');

            if (bccomp($debit, '0', 2) < 0 || bccomp($credit, '0', 2) < 0) {
                throw new DomainException('Nominal jurnal tidak boleh negatif.');
            }

            if ((bccomp($debit, '0', 2) === 0) === (bccomp($credit, '0', 2) === 0)) {
                throw new DomainException("Baris akun {$account->code} harus berisi debit atau kredit saja.");
            }

            $normalizedLines[] = [
                'account_id' => $account->id,
                'debit' => $debit,
                'credit' => $credit,
                'description' => $line['description'] ?? null,
            ];
        }

        return $normalizedLines;
    }

    private function assertAccountUsable(Account $account, Office $office, bool $allowInterOffice): void
    {
        $accountName = "{$account->code} {$account->name}";

        if (! $account->is_postable) {
            throw new DomainException("Akun {$accountName} adalah akun induk dan tidak dapat diposting.");
        }

        if (! $account->is_active) {
            throw new DomainException("Akun {$accountName} tidak aktif.");
        }

        if ($account->is_head_office_only && $office->type !== 'head_office') {
            throw new DomainException("Akun {$accountName} hanya boleh dipakai kantor pusat.");
        }

        if ($account->isInterOffice() && ! $allowInterOffice) {
            throw new DomainException("Akun {$accountName} hanya dibentuk otomatis oleh transaksi antar kantor.");
        }

        if ($account->isInterOffice() && (int) $account->counterpart_office_id === (int) $office->id) {
            throw new DomainException("Kantor {$office->name} tidak dapat memakai akun RAK untuk dirinya sendiri.");
        }
    }

    /**
     * @param  array<int, array{debit: string, credit: string}>  $lines
     */
    private function assertBalanced(array $lines): void
    {
        if (count($lines) < 2) {
            throw new DomainException('Jurnal minimal terdiri dari dua baris.');
        }

        $totalDebit = $this->sum($lines, 'debit');
        $totalCredit = $this->sum($lines, 'credit');

        if (bccomp($totalDebit, $totalCredit, 2) !== 0) {
            throw new DomainException('Jurnal tidak seimbang: debit Rp '.$this->format($totalDebit)
                .' dan kredit Rp '.$this->format($totalCredit).'.');
        }
    }

    /**
     * Baris akun RAK yang menutup selisih debit dikurangi kredit ($net) di buku $bookOffice.
     *
     * @return array{account_id: int, debit: string, credit: string, description: string}
     */
    private function interOfficeLine(Office $bookOffice, Office $counterpartOffice, string $net): array
    {
        $account = $this->interOfficeAccountService->accountFor($bookOffice, $counterpartOffice);
        $isCredit = bccomp($net, '0', 2) > 0;

        return [
            'account_id' => $account->id,
            'debit' => $isCredit ? '0.00' : bcmul($net, '-1', 2),
            'credit' => $isCredit ? $net : '0.00',
            'description' => "RAK {$counterpartOffice->code} — {$counterpartOffice->name}",
        ];
    }

    /**
     * @param  array<int, array{account_id: int, debit: string, credit: string, description: string|null}>  $lines
     * @param  array<string, mixed>  $attributes
     */
    private function writeJournal(Office $office, array $lines, array $attributes): Journal
    {
        $bookDate = $office->book_date->toDateString();

        $journal = Journal::create([
            ...$attributes,
            'office_id' => $office->id,
            'number' => $this->documentNumberService->next('journal', $office, $office->book_date),
            'book_date' => $bookDate,
            'total_amount' => $this->sum($lines, 'debit'),
            'status' => 'posted',
        ]);

        $journal->details()->createMany(array_map(fn (array $line) => [
            'account_id' => $line['account_id'],
            'office_id' => $office->id,
            'book_date' => $bookDate,
            'debit' => $line['debit'],
            'credit' => $line['credit'],
            'description' => $line['description'],
        ], $lines));

        return $journal;
    }

    /**
     * @return array<string, mixed>
     */
    private function journalAttributes(string $description, ?string $transactionType, ?Model $source, ?User $createdBy): array
    {
        return [
            'description' => $description,
            'transaction_type' => $transactionType,
            'source_type' => $source?->getMorphClass(),
            'source_id' => $source?->getKey(),
            'created_by' => ($createdBy ?? Auth::user())?->getKey(),
        ];
    }

    /**
     * Normalisasi nominal ke string 2 desimal dan tolak nominal yang belum dibulatkan ke rupiah penuh (ATR-08).
     */
    private function money(mixed $value): string
    {
        $value = (string) $value;

        if (! preg_match('/^-?\d+(\.\d+)?$/', $value)) {
            throw new DomainException("Nominal jurnal \"{$value}\" bukan angka yang valid.");
        }

        if (bccomp($value, bcadd($value, '0', 0), 10) !== 0) {
            throw new DomainException('Nominal Rp '.$value.' harus dibulatkan ke rupiah penuh.');
        }

        return bcadd($value, '0', 2);
    }

    /**
     * @param  array<int, array{debit: string, credit: string}>  $lines
     */
    private function sum(array $lines, string $side): string
    {
        return array_reduce($lines, fn (string $total, array $line) => bcadd($total, $line[$side], 2), '0.00');
    }

    /**
     * @param  array<int, array{debit: string, credit: string}>  $lines
     */
    private function net(array $lines): string
    {
        return bcsub($this->sum($lines, 'debit'), $this->sum($lines, 'credit'), 2);
    }

    private function format(string $amount): string
    {
        return number_format($amount, 0, ',', '.');
    }
}
