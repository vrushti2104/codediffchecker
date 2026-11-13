<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Entitlement extends Model
{
    protected $fillable = ['user_id', 'active_lifetime_user_id', 'type', 'status', 'starts_at', 'ends_at', 'granted_by', 'revoked_by', 'revoked_at', 'revoke_reason'];

    protected function casts(): array
    {
        return ['starts_at' => 'datetime', 'ends_at' => 'datetime', 'revoked_at' => 'datetime'];
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function grantor(): BelongsTo { return $this->belongsTo(User::class, 'granted_by'); }
    public function revoker(): BelongsTo { return $this->belongsTo(User::class, 'revoked_by'); }
}
