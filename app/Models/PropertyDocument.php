<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyDocument extends Model
{
    public const STATUS_ACTIVE = 'active';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_ARCHIVED = 'archived';

    protected $fillable = [
        'property_id','property_unit_id','tenancy_id',
        'property_management_agreement_id','title','document_type','file_path',
        'issued_at','expires_at','status','notes','created_by_user_id',
    ];

    protected function casts(): array
    {
        return ['issued_at' => 'date', 'expires_at' => 'date'];
    }

    public function property(): BelongsTo { return $this->belongsTo(Property::class); }
    public function unit(): BelongsTo { return $this->belongsTo(PropertyUnit::class, 'property_unit_id'); }
    public function tenancy(): BelongsTo { return $this->belongsTo(Tenancy::class); }
    public function agreement(): BelongsTo { return $this->belongsTo(PropertyManagementAgreement::class, 'property_management_agreement_id'); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by_user_id'); }
}
