<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'phone',
        'email',
        'identity_number',
        'notes',
    ];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function tenancies(): HasMany { return $this->hasMany(Tenancy::class); }
    public function maintenanceRequests(): HasMany { return $this->hasMany(MaintenanceRequest::class); }
}
