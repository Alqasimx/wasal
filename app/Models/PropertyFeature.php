<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PropertyFeature extends Model
{
    protected $fillable = [
        'name_ar',
        'name_en',
        'slug',
        'key',
        'data_type',
        'options',
        'is_filterable',
        'is_searchable',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'options' => 'array',
            'is_filterable' => 'boolean',
            'is_searchable' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function propertyTypes(): BelongsToMany
    {
        return $this->belongsToMany(PropertyType::class, 'property_type_feature')
            ->withPivot(['is_required', 'sort_order']);
    }

    public function values(): HasMany
    {
        return $this->hasMany(PropertyAttributeValue::class);
    }
}
