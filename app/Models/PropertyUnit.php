<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PropertyUnit extends Model
{
    use SoftDeletes;

    public const STATUS_AVAILABLE = 'available';
    public const STATUS_OCCUPIED = 'occupied';
    public const STATUS_MAINTENANCE = 'maintenance';
    public const STATUS_UNAVAILABLE = 'unavailable';

    protected $fillable = [
        'property_id',
        'code',
        'name',
        'floor_number',
        'unit_type',
        'area',
        'bedrooms',
        'bathrooms',
        'halls',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'area' => 'decimal:2',
            'floor_number' => 'integer',
            'bedrooms' => 'integer',
            'bathrooms' => 'integer',
            'halls' => 'integer',
        ];
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function attributeValues(): MorphMany
    {
        return $this->morphMany(PropertyAttributeValue::class, 'attributable');
    }

    public function tenancies(): HasMany
    {
        return $this->hasMany(Tenancy::class);
    }

    public function currentTenancy(): HasOne
    {
        return $this->hasOne(Tenancy::class)
            ->where('status', Tenancy::STATUS_ACTIVE)
            ->whereDate('starts_at', '<=', today())
            ->where(function (Builder $query): void {
                $query
                    ->whereNull('ends_at')
                    ->orWhereDate('ends_at', '>=', today());
            })
            ->ofMany('starts_at', 'max');
    }

    public function serviceSchedules(): HasMany
    {
        return $this->hasMany(PropertyServiceSchedule::class);
    }

    public function maintenanceRequests(): HasMany
    {
        return $this->hasMany(MaintenanceRequest::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(PropertyExpense::class);
    }

    public function scopeCurrentlyManaged(Builder $query): Builder
    {
        return $query->whereHas('property.managementAgreements', function (Builder $query): void {
            $query
                ->where('status', PropertyManagementAgreement::STATUS_ACTIVE)
                ->whereDate('starts_at', '<=', today())
                ->where(function (Builder $query): void {
                    $query
                        ->whereNull('ends_at')
                        ->orWhereDate('ends_at', '>=', today());
                });
        });
    }

    public function scopeOccupied(Builder $query): Builder
    {
        return $query->whereHas('tenancies', function (Builder $query): void {
            $query
                ->where('status', Tenancy::STATUS_ACTIVE)
                ->whereDate('starts_at', '<=', today())
                ->where(function (Builder $query): void {
                    $query
                        ->whereNull('ends_at')
                        ->orWhereDate('ends_at', '>=', today());
                });
        });
    }

    public function scopeVacant(Builder $query): Builder
    {
        return $query->whereDoesntHave('tenancies', function (Builder $query): void {
            $query
                ->where('status', Tenancy::STATUS_ACTIVE)
                ->whereDate('starts_at', '<=', today())
                ->where(function (Builder $query): void {
                    $query
                        ->whereNull('ends_at')
                        ->orWhereDate('ends_at', '>=', today());
                });
        });
    }

    public function isOccupied(): bool
    {
        if ($this->relationLoaded('currentTenancy')) {
            return $this->currentTenancy !== null;
        }

        return $this->currentTenancy()->exists();
    }

    public function effectiveOperationalStatus(): string
    {
        if ($this->status === self::STATUS_OCCUPIED && ! $this->isOccupied()) {
            return self::STATUS_AVAILABLE;
        }

        return $this->status;
    }
}
