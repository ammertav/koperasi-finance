<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

/**
 * Membatasi data ke kantor pengguna yang login bila perannya hanya berlingkup kantor sendiri (ATR-01).
 */
class OfficeScope implements Scope
{
    public function __construct(private string $column = 'office_id') {}

    public function apply(Builder $builder, Model $model): void
    {
        // Saat user sesi sedang di-resolve, Auth::user() belum tersedia; tanpa cek ini terjadi rekursi.
        if (! Auth::hasUser()) {
            return;
        }

        $user = Auth::user();

        if ($user->canAccessAllOffices()) {
            return;
        }

        $builder->where($model->qualifyColumn($this->column), $user->office_id);
    }
}
