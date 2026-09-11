<?php

namespace App\Services\Organization;

use App\Models\AuthorityLimit;
use App\Models\User;

class AuthorityService
{
    /**
     * Cek batas nominal wewenang pengguna untuk satu jenis transaksi dari matriks wewenang (ORG-06, ATR-05).
     *
     * @return array{allowed: bool, message: string|null}
     */
    public function check(User $user, string $transactionType, string $amount): array
    {
        $limits = AuthorityLimit::whereIn('role_id', $user->roles->pluck('id'))
            ->where('transaction_type', $transactionType)
            ->get();

        $label = mb_strtolower(AuthorityLimit::TRANSACTION_TYPES[$transactionType] ?? $transactionType);

        if ($limits->isEmpty()) {
            return ['allowed' => false, 'message' => "Peran Anda tidak memiliki wewenang {$label}."];
        }

        $isWithinLimit = $limits->contains(
            fn (AuthorityLimit $limit) => $limit->is_unlimited || bccomp($amount, $limit->max_amount, 2) <= 0
        );

        if ($isWithinLimit) {
            return ['allowed' => true, 'message' => null];
        }

        $highestLimit = $limits->reduce(
            fn (string $highest, AuthorityLimit $limit) => bccomp($limit->max_amount, $highest, 2) > 0 ? $limit->max_amount : $highest,
            '0.00'
        );

        return [
            'allowed' => false,
            'message' => 'Nominal Rp '.number_format($amount, 0, ',', '.')." melebihi batas wewenang {$label} Anda (Rp "
                .number_format($highestLimit, 0, ',', '.').').',
        ];
    }
}
