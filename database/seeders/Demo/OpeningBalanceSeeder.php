<?php

namespace Database\Seeders\Demo;

use App\Models\Journal;
use App\Models\Office;
use App\Models\User;
use App\Services\Accounting\JournalService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OpeningBalanceSeeder extends Seeder
{
    /**
     * Saldo awal bank kantor pusat dan dropping dana awal ke setiap cabang, diposting lewat JournalService
     * sehingga jurnal RAK terbentuk di kedua kantor (RAK-02). Nominal di config/demo.php.
     */
    public function run(JournalService $journalService): void
    {
        if (Journal::where('transaction_type', 'opening_balance')->exists()) {
            return;
        }

        $headOffice = Office::where('type', 'head_office')->firstOrFail();
        $accountingHead = User::where('email', 'kak@ksp.test')->firstOrFail();
        $branches = Office::where('type', 'branch')->orderBy('code')->get();

        DB::transaction(function () use ($journalService, $headOffice, $accountingHead, $branches) {
            $journalService->postFromTemplate(
                transactionType: 'opening_balance',
                productCode: null,
                origin: $headOffice,
                destination: null,
                amounts: ['amount' => config('demo.opening_balance.head_office_bank')],
                description: 'Saldo awal bank kantor pusat',
                createdBy: $accountingHead,
            );

            foreach ($branches as $branch) {
                $journalService->postFromTemplate(
                    transactionType: 'fund_dropping',
                    productCode: null,
                    origin: $headOffice,
                    destination: $branch,
                    amounts: ['amount' => config('demo.opening_balance.branch_dropping')],
                    description: "Dropping dana awal ke {$branch->name}",
                    createdBy: $accountingHead,
                );
            }
        });
    }
}
