<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PropertyAttributeValueResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'key' => $this->feature?->key,
            'name_ar' => $this->feature?->name_ar,
            'data_type' => $this->feature?->data_type,
            'value' => $this->value_json ?? $this->value_text ?? $this->value_number ?? $this->value_boolean,
        ];
    }
}
