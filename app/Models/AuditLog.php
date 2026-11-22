<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    /**
     * Audit log is strictly append-only (SRS NFR-DAT-04, NFR-AUD-01 & ADR-07).
     * No updated_at column is maintained.
     */
    public const UPDATED_AT = null;
    public $timestamps = false;

    protected $table = 'audit_logs';

    protected $guarded = [];

    protected $casts = [
        'details' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Actor / User performing the action
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id_akun');
    }

    /**
     * Helper to append an audit log entry
     */
    public static function record(
        string $action,
        ?string $description = null,
        ?Model $auditable = null,
        array $details = []
    ): self {
        return self::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'auditable_type' => $auditable ? get_class($auditable) : null,
            'auditable_id' => $auditable ? $auditable->getKey() : null,
            'description' => $description,
            'details' => $details,
            'ip_address' => request()->ip(),
        ]);
    }
}
