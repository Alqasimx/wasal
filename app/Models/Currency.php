<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Currency extends Model
{
    protected $fillable = [
        'code',
        'iso_code',
        'name_ar',
        'name_en',
        'symbol',
        'is_active',
        'exchange_rate',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'exchange_rate' => 'decimal:6',
        ];
    }

    public function banks(): HasMany
    {
        return $this->hasMany(Bank::class);
    }
}