<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyOwner extends Model
{
    protected $fillable = ['property_id', 'user_id', 'external_owner_name', 'ownership_percentage', 'is_primary', 'valid_from', 'valid_to'];

    protected function casts(): array
    {
        return ['ownership_percentage' => 'decimal:2', 'is_primary' => 'boolean', 'valid_from' => 'date', 'valid_to' => 'date'];
    }

    public function property(): BelongsTo { return $this->belongsTo(Property::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
