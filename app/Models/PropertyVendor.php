<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PropertyVendor extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'service_categories',
        'notes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'service_categories' => 'array',
            'is_active' => 'boolean',
        ];
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
}
