<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\JournalTemplate;
use App\Models\JournalTemplateLine;
use Illuminate\Database\Seeder;

class JournalTemplateSeeder extends Seeder
{
    /**
     * Template jurnal otomatis per jenis transaksi dan produk (AKT-02). Kode produk simpanan: SP (pokok),
     * SW (wajib), SS (sukarela); pinjaman: PUM (umum), PUS (usaha). Baris: [sisi, kode akun, amount_key, office_role].
     * office_role destination dipakai bila transaksi terjadi di kantor lain dari pemilik rekening (RAK-02).
     *
     * @var array<int, array{0: string, 1: string, 2: string|null, 3: string, 4: array<int, array{0: string, 1: string, 2: string, 3: string}>}>
     */
    private const TEMPLATES = [
        ['OPENING_BALANCE', 'opening_balance', null, 'Saldo awal bank', [
            ['debit', '1.1.03', 'amount', 'origin'],
            ['credit', '3.2.01', 'amount', 'origin'],
        ]],
        ['FUND_DROPPING', 'fund_dropping', null, 'Dropping dana dari pusat ke kas brankas cabang', [
            ['debit', '1.1.02', 'amount', 'destination'],
            ['credit', '1.1.03', 'amount', 'origin'],
        ]],
        ['VAULT_TO_TELLER', 'vault_to_teller', null, 'Pengambilan kas brankas oleh teller', [
            ['debit', '1.1.01', 'amount', 'origin'],
            ['credit', '1.1.02', 'amount', 'origin'],
        ]],
        ['TELLER_TO_VAULT', 'teller_to_vault', null, 'Penyetoran kas teller ke brankas', [
            ['debit', '1.1.02', 'amount', 'origin'],
            ['credit', '1.1.01', 'amount', 'origin'],
        ]],
        ['CASH_SHORTAGE', 'cash_shortage', null, 'Selisih kurang kas teller', [
            ['debit', '5.3.01', 'amount', 'origin'],
            ['credit', '1.1.01', 'amount', 'origin'],
        ]],
        ['CASH_OVERAGE', 'cash_overage', null, 'Selisih lebih kas teller', [
            ['debit', '1.1.01', 'amount', 'origin'],
            ['credit', '4.3.01', 'amount', 'origin'],
        ]],
        ['SAVINGS_DEPOSIT_SP', 'savings_deposit', 'SP', 'Setoran simpanan pokok', [
            ['debit', '1.1.01', 'amount', 'origin'],
            ['credit', '3.1.01', 'amount', 'destination'],
        ]],
        ['SAVINGS_DEPOSIT_SW', 'savings_deposit', 'SW', 'Setoran simpanan wajib', [
            ['debit', '1.1.01', 'amount', 'origin'],
            ['credit', '3.1.02', 'amount', 'destination'],
        ]],
        ['SAVINGS_DEPOSIT_SS', 'savings_deposit', 'SS', 'Setoran simpanan sukarela', [
            ['debit', '1.1.01', 'amount', 'origin'],
            ['credit', '2.1.01', 'amount', 'destination'],
        ]],
        ['SAVINGS_WITHDRAWAL_SS', 'savings_withdrawal', 'SS', 'Penarikan simpanan sukarela', [
            ['debit', '2.1.01', 'amount', 'destination'],
            ['credit', '1.1.01', 'amount', 'origin'],
        ]],
        ['LOAN_DISBURSEMENT_PUM', 'loan_disbursement', 'PUM', 'Pencairan Pinjaman Umum dengan potongan provisi dan administrasi', [
            ['debit', '1.2.01', 'principal', 'destination'],
            ['credit', '1.1.01', 'net_disbursement', 'origin'],
            ['credit', '4.2.01', 'provision_fee', 'destination'],
            ['credit', '4.2.02', 'admin_fee', 'destination'],
        ]],
        ['LOAN_DISBURSEMENT_PUS', 'loan_disbursement', 'PUS', 'Pencairan Pinjaman Usaha dengan potongan provisi dan administrasi', [
            ['debit', '1.2.02', 'principal', 'destination'],
            ['credit', '1.1.01', 'net_disbursement', 'origin'],
            ['credit', '4.2.01', 'provision_fee', 'destination'],
            ['credit', '4.2.02', 'admin_fee', 'destination'],
        ]],
        ['LOAN_INSTALLMENT_PUM', 'loan_installment', 'PUM', 'Angsuran Pinjaman Umum', [
            ['debit', '1.1.01', 'total_payment', 'origin'],
            ['credit', '1.2.01', 'principal', 'destination'],
            ['credit', '4.1.01', 'interest', 'destination'],
            ['credit', '4.2.03', 'penalty', 'destination'],
        ]],
        ['LOAN_INSTALLMENT_PUS', 'loan_installment', 'PUS', 'Angsuran Pinjaman Usaha', [
            ['debit', '1.1.01', 'total_payment', 'origin'],
            ['credit', '1.2.02', 'principal', 'destination'],
            ['credit', '4.1.02', 'interest', 'destination'],
            ['credit', '4.2.03', 'penalty', 'destination'],
        ]],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (self::TEMPLATES as [$code, $transactionType, $productCode, $name, $lines]) {
            $template = JournalTemplate::updateOrCreate(['code' => $code], [
                'transaction_type' => $transactionType,
                'product_code' => $productCode,
                'name' => $name,
                'is_active' => true,
            ]);

            JournalTemplateLine::where('journal_template_id', $template->id)->delete();

            foreach ($lines as $index => [$side, $accountCode, $amountKey, $officeRole]) {
                JournalTemplateLine::create([
                    'journal_template_id' => $template->id,
                    'account_id' => Account::where('code', $accountCode)->firstOrFail()->id,
                    'side' => $side,
                    'amount_key' => $amountKey,
                    'office_role' => $officeRole,
                    'sort_order' => $index + 1,
                ]);
            }
        }
    }
}
