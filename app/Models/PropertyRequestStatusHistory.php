<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class PropertyRequestStatusHistory extends Model
{
    public const UPDATED_AT = null;
    protected $fillable = ['property_request_id','from_status','to_status','changed_by_user_id','notes'];
    public function request(): BelongsTo { return $this->belongsTo(PropertyRequest::class, 'property_request_id'); }
    public function changedBy(): BelongsTo { return $this->belongsTo(User::class, 'changed_by_user_id'); }
}
