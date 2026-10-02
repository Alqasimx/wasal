<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PropertyUnit extends Model
{
    use SoftDeletes;

    protected $fillable = ['property_id', 'code', 'name', 'floor_number', 'unit_type', 'area', 'bedrooms', 'bathrooms', 'halls', 'status'];

    protected function casts(): array
    {
        return ['area' => 'decimal:2', 'floor_number' => 'integer', 'bedrooms' => 'integer', 'bathrooms' => 'integer', 'halls' => 'integer'];
    }

    public function property(): BelongsTo { return $this->belongsTo(Property::class); }
    public function attributeValues(): MorphMany { return $this->morphMany(PropertyAttributeValue::class, 'attributable'); }

    public function tenancies(): \Illuminate\Database\Eloquent\Relations\HasMany { return $this->hasMany(Tenancy::class); }
}
