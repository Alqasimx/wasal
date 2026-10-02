<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Property extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'created_by_user_id',
        'property_type_id',
        'internal_code',
        'title_ar',
        'title_en',
        'description_ar',
        'description_en',
        'status',
        'city_id',
        'district_id',
        'neighborhood_id',
        'street_name',
        'public_location_text',
        'exact_address',
        'public_latitude',
        'public_longitude',
        'exact_latitude',
        'exact_longitude',
        'area',
        'floors_count',
        'units_count',
        'year_built',
        'gallery',
    ];

    protected function casts(): array
    {
        return [
            'public_latitude' => 'decimal:7',
            'public_longitude' => 'decimal:7',
            'exact_latitude' => 'decimal:7',
            'exact_longitude' => 'decimal:7',
            'area' => 'decimal:2',
            'floors_count' => 'integer',
            'units_count' => 'integer',
            'year_built' => 'integer',
            'gallery' => 'array',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function propertyType(): BelongsTo
    {
        return $this->belongsTo(PropertyType::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function neighborhood(): BelongsTo
    {
        return $this->belongsTo(Neighborhood::class);
    }

    public function listings(): HasMany
    {
        return $this->hasMany(PropertyListing::class);
    }

    public function owners(): HasMany
    {
        return $this->hasMany(PropertyOwner::class);
    }

    public function units(): HasMany
    {
        return $this->hasMany(PropertyUnit::class);
    }

    public function attributeValues(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(PropertyAttributeValue::class, 'attributable');
    }
}
