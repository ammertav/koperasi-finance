<?php

namespace App\Models\Concerns;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

/**
 * Mencatat create, update, dan delete ke audit trail terpusat (ATR-09).
 */
trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(function (Model $model): void {
            $model->recordAudit('created', [], $model->auditableValues($model->getAttributes()));
        });

        static::updated(function (Model $model): void {
            $changes = $model->auditableValues($model->getChanges());

            if ($changes === []) {
                return;
            }

            $oldValues = array_intersect_key($model->auditableValues($model->getOriginal()), $changes);

            $model->recordAudit('updated', $oldValues, $changes);
        });

        static::deleted(function (Model $model): void {
            $model->recordAudit('deleted', $model->auditableValues($model->getOriginal()), []);
        });
    }

    public function auditLogs(): MorphMany
    {
        return $this->morphMany(AuditLog::class, 'auditable');
    }

    /**
     * Buang atribut tersembunyi (kata sandi, token) dan timestamp dari nilai audit.
     *
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    protected function auditableValues(array $values): array
    {
        return Arr::except($values, array_merge($this->getHidden(), ['created_at', 'updated_at']));
    }

    protected function auditOfficeId(): ?int
    {
        return $this->getAttribute('office_id') ?? Auth::user()?->office_id;
    }

    /**
     * @param  array<string, mixed>  $oldValues
     * @param  array<string, mixed>  $newValues
     */
    protected function recordAudit(string $event, array $oldValues, array $newValues): void
    {
        AuditLog::create([
            'user_id' => Auth::id(),
            'office_id' => $this->auditOfficeId(),
            'event' => $event,
            'auditable_type' => $this->getMorphClass(),
            'auditable_id' => $this->getKey(),
            'old_values' => $oldValues ?: null,
            'new_values' => $newValues ?: null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
