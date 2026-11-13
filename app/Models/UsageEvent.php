<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UsageEvent extends Model
{
    public $timestamps = false;
    protected $fillable = ['user_id', 'action', 'units', 'metadata', 'occurred_at'];

    protected function casts(): array
    {
        return ['metadata' => 'array', 'occurred_at' => 'datetime'];
    }
}
