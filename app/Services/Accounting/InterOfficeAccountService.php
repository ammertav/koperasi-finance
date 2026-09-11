<?php

namespace App\Services\Accounting;

use App\Models\Account;
use App\Models\Office;
use App\Models\Scopes\OfficeScope;
use DomainException;

class InterOfficeAccountService
{
    public function headOffice(): Office
    {
        return Office::withoutGlobalScope(OfficeScope::class)->where('type', 'head_office')->first()
            ?? throw new DomainException('Kantor pusat belum terdaftar.');
    }

    /**
     * Akun RAK di buku $bookOffice untuk transaksi dengan $counterpartOffice, dibuat otomatis bila belum ada (RAK-01).
     * RAK hanya ada untuk pasangan pusat–cabang; transaksi antar dua cabang dilewatkan kantor pusat.
     */
    public function accountFor(Office $bookOffice, Office $counterpartOffice): Account
    {
        $isHeadOfficeBook = $bookOffice->type === 'head_office';
        $isHeadOfficeCounterpart = $counterpartOffice->type === 'head_office';

        if ($isHeadOfficeBook === $isHeadOfficeCounterpart) {
            throw new DomainException('Rekening Antar Kantor hanya dibentuk untuk pasangan kantor pusat dan cabang.');
        }

        return $this->findOrCreate($counterpartOffice);
    }

    /**
     * Pastikan pasangan akun RAK untuk satu cabang sudah ada: "RAK {cabang}" di buku pusat dan "RAK Kantor Pusat" di buku cabang.
     */
    public function ensureAccountsFor(Office $branch): void
    {
        $headOffice = $this->headOffice();

        $this->accountFor($headOffice, $branch);
        $this->accountFor($branch, $headOffice);
    }

    private function findOrCreate(Office $counterpartOffice): Account
    {
        $parent = Account::where('code', config('accounting.inter_office_parent_code'))->first()
            ?? throw new DomainException('Akun induk Rekening Antar Kantor belum ada di bagan akun.');

        return Account::firstOrCreate(['code' => $parent->code.'.'.$counterpartOffice->code], [
            'parent_id' => $parent->id,
            'counterpart_office_id' => $counterpartOffice->id,
            'name' => 'RAK '.$counterpartOffice->name,
            'type' => $parent->type,
            'normal_balance' => $parent->normal_balance,
            'level' => $parent->level + 1,
            'is_postable' => true,
            // Akun "RAK {cabang}" hanya dipakai di buku kantor pusat.
            'is_head_office_only' => $counterpartOffice->type !== 'head_office',
            'is_active' => true,
        ]);
    }
}
