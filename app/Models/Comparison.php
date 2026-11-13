<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comparison extends Model
{
    protected $fillable = ['user_id', 'title', 'language', 'original', 'changed', 'settings'];

    protected function casts(): array
    {
        return ['settings' => 'array'];
    }
}
