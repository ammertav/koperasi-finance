<?php

namespace App\Services\Accounting;

use App\Models\Account;
use App\Models\JournalDetail;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Buku besar dan neraca saldo per kantor atau konsolidasi, dihitung langsung dari jurnal terposting (AKT-05).
 * Hasilnya tidak di-cache (aturan domain #8).
 */
class LedgerService
{
    /**
     * @return array{
     *     rows: Collection<int, array{account: Account, opening_debit: string, opening_credit: string, debit: string, credit: string, closing_debit: string, closing_credit: string}>,
     *     totals: array{opening_debit: string, opening_credit: string, debit: string, credit: string, closing_debit: string, closing_credit: string}
     * }
     */
    public function trialBalance(?int $officeId, CarbonInterface $from, CarbonInterface $to): array
    {
        $fromDate = $from->toDateString();

        $balances = $this->postedDetails($officeId)
            ->where('journal_details.book_date', '<', $to->copy()->addDay()->toDateString())
            ->select('journal_details.account_id')
            ->selectRaw('SUM(CASE WHEN journal_details.book_date < ? THEN journal_details.debit - journal_details.credit ELSE 0 END) AS opening_balance', [$fromDate])
            ->selectRaw('SUM(CASE WHEN journal_details.book_date >= ? THEN journal_details.debit ELSE 0 END) AS total_debit', [$fromDate])
            ->selectRaw('SUM(CASE WHEN journal_details.book_date >= ? THEN journal_details.credit ELSE 0 END) AS total_credit', [$fromDate])
            ->groupBy('journal_details.account_id')
            ->toBase()
            ->get()
            ->keyBy('account_id');

        $accounts = Account::whereIn('id', $balances->keys())->orderBy('code')->get();

        $rows = $accounts
            ->map(function (Account $account) use ($balances) {
                $balance = $balances->get($account->id);
                $opening = $this->amount($balance->opening_balance);
                $debit = $this->amount($balance->total_debit);
                $credit = $this->amount($balance->total_credit);
                [$openingDebit, $openingCredit] = $this->splitBalance($opening);
                [$closingDebit, $closingCredit] = $this->splitBalance(bcsub(bcadd($opening, $debit, 2), $credit, 2));

                return [
                    'account' => $account,
                    'opening_debit' => $openingDebit,
                    'opening_credit' => $openingCredit,
                    'debit' => $debit,
                    'credit' => $credit,
                    'closing_debit' => $closingDebit,
                    'closing_credit' => $closingCredit,
                ];
            })
            ->reject(fn (array $row) => bccomp($row['opening_debit'], '0', 2) === 0
                && bccomp($row['opening_credit'], '0', 2) === 0
                && bccomp($row['debit'], '0', 2) === 0
                && bccomp($row['credit'], '0', 2) === 0)
            ->values();

        $totals = [];

        foreach (['opening_debit', 'opening_credit', 'debit', 'credit', 'closing_debit', 'closing_credit'] as $column) {
            $totals[$column] = $rows->reduce(fn (string $total, array $row) => bcadd($total, $row[$column], 2), '0.00');
        }

        return ['rows' => $rows, 'totals' => $totals];
    }

    /**
     * Mutasi satu akun dengan saldo berjalan. Saldo mengikuti saldo normal akun: positif berarti saldo normal.
     *
     * @return array{
     *     opening_balance: string,
     *     rows: Collection<int, array{detail: JournalDetail, balance: string}>,
     *     total_debit: string,
     *     total_credit: string,
     *     closing_balance: string
     * }
     */
    public function generalLedger(Account $account, ?int $officeId, CarbonInterface $from, CarbonInterface $to): array
    {
        $openingNet = $this->postedDetails($officeId)
            ->where('journal_details.account_id', $account->id)
            ->where('journal_details.book_date', '<', $from->toDateString())
            ->selectRaw('SUM(journal_details.debit - journal_details.credit) AS balance')
            ->toBase()
            ->first()
            ?->balance;

        $openingBalance = $this->signedForAccount($account, $this->amount($openingNet));

        $details = $this->postedDetails($officeId)
            ->where('journal_details.account_id', $account->id)
            ->where('journal_details.book_date', '>=', $from->toDateString())
            ->where('journal_details.book_date', '<', $to->copy()->addDay()->toDateString())
            ->select('journal_details.*')
            ->with(['journal', 'office'])
            ->orderBy('journal_details.book_date')
            ->orderBy('journal_details.journal_id')
            ->orderBy('journal_details.id')
            ->get();

        $balance = $openingBalance;
        $totalDebit = '0.00';
        $totalCredit = '0.00';

        $rows = $details->map(function (JournalDetail $detail) use ($account, &$balance, &$totalDebit, &$totalCredit) {
            $balance = bcadd($balance, $this->signedForAccount($account, bcsub($detail->debit, $detail->credit, 2)), 2);
            $totalDebit = bcadd($totalDebit, $detail->debit, 2);
            $totalCredit = bcadd($totalCredit, $detail->credit, 2);

            return ['detail' => $detail, 'balance' => $balance];
        });

        return [
            'opening_balance' => $openingBalance,
            'rows' => $rows,
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit,
            'closing_balance' => $balance,
        ];
    }

    /**
     * Baris jurnal terposting. Rentang tanggal memakai batas setengah terbuka (>= awal, < akhir + 1 hari)
     * agar benar untuk kolom DATE maupun nilai tanggal ber-jam di SQLite.
     *
     * @return Builder<JournalDetail>
     */
    private function postedDetails(?int $officeId): Builder
    {
        return JournalDetail::query()
            ->join('journals', 'journals.id', '=', 'journal_details.journal_id')
            ->where('journals.status', 'posted')
            ->when($officeId, fn (Builder $query) => $query->where('journal_details.office_id', $officeId));
    }

    private function signedForAccount(Account $account, string $debitMinusCredit): string
    {
        return $account->normal_balance === 'debit' ? $debitMinusCredit : bcmul($debitMinusCredit, '-1', 2);
    }

    /**
     * @return array{0: string, 1: string} [debit, kredit]
     */
    private function splitBalance(string $debitMinusCredit): array
    {
        return bccomp($debitMinusCredit, '0', 2) >= 0
            ? [$debitMinusCredit, '0.00']
            : ['0.00', bcmul($debitMinusCredit, '-1', 2)];
    }

    /**
     * Hasil SUM dari driver bisa berupa string (MySQL), integer, atau float (SQLite).
     */
    private function amount(mixed $value): string
    {
        if ($value === null) {
            return '0.00';
        }

        if (is_float($value)) {
            $value = number_format($value, 2, '.', '');
        }

        return bcadd((string) $value, '0', 2);
    }
}
