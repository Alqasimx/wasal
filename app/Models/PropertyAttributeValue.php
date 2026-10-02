<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PropertyAttributeValue extends Model
{
    protected $fillable = ['attributable_type', 'attributable_id', 'property_feature_id', 'value_text', 'value_number', 'value_boolean', 'value_json'];

    protected function casts(): array
    {
        return ['value_number' => 'decimal:6', 'value_boolean' => 'boolean', 'value_json' => 'array'];
    }

    public function attributable(): MorphTo { return $this->morphTo(); }
    public function feature(): BelongsTo { return $this->belongsTo(PropertyFeature::class, 'property_feature_id'); }
}
