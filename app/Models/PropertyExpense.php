<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyExpense extends Model
{
    public const STATUS_UNPAID = 'unpaid';
    public const STATUS_PARTIAL = 'partial';
    public const STATUS_PAID = 'paid';

    protected $fillable = [
        'property_id',
        'property_unit_id',
        'property_management_agreement_id',
        'maintenance_request_id',
        'property_vendor_id',
        'category',
        'description',
        'amount',
        'paid_amount',
        'currency_id',
        'incurred_at',
        'cost_bearer',
        'payment_status',
        'reference_number',
        'attachment_path',
        'notes',
        'created_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'incurred_at' => 'date',
        ];
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(PropertyUnit::class, 'property_unit_id');
    }

    public function agreement(): BelongsTo
    {
        return $this->belongsTo(PropertyManagementAgreement::class, 'property_management_agreement_id');
    }

    public function maintenanceRequest(): BelongsTo
    {
        return $this->belongsTo(MaintenanceRequest::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(PropertyVendor::class, 'property_vendor_id');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
