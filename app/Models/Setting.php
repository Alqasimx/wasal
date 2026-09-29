<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Setting extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'key',
        'value_json',
        'group',
        'is_public',
        'updated_by_user_id',
        'updated_at',
    ];

    protected function casts(): array
    {
        return [
            'value_json' => 'array',
            'is_public' => 'boolean',
            'updated_at' => 'datetime',
        ];
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_user_id');
    }
}