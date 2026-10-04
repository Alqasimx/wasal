<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'whatsapp_phone',
        'password',
        'preferred_language',
        'preferred_currency_id',
        'preferred_city_id',
        'status',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        if ($this->hasRole('system_admin')) {
            return true;
        }

        return match ($panel->getId()) {
            'admin' => $this->can('panels.admin.access'),
            'real-estate' => $this->can('panels.real_estate.access'),
            'property-management' => $this->can('panels.property_management.access'),
            default => false,
        };
    }

    public function preferredCurrency(): BelongsTo
    {
        return $this->belongsTo(
            Currency::class,
            'preferred_currency_id'
        );
    }

    public function preferredCity(): BelongsTo
    {
        return $this->belongsTo(
            City::class,
            'preferred_city_id'
        );
    }

    public function otpCodes(): HasMany
    {
        return $this->hasMany(OtpCode::class);
    }

    public function authSessions(): HasMany
    {
        return $this->hasMany(AuthSession::class);
    }

    public function devices(): HasMany
    {
        return $this->hasMany(UserDevice::class);
    }

    public function assignedTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'assigned_to_user_id');
    }

    public function createdTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'created_by_user_id');
    }

    public function inAppNotifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }
}
