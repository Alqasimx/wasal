<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
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
}