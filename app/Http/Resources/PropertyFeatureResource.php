<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PropertyFeatureResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name_ar' => $this->name_ar,
            'name_en' => $this->name_en,
            'slug' => $this->slug,
            'data_type' => $this->data_type,
            'options' => $this->options,
            'is_filterable' => (bool) $this->is_filterable,
            'is_required' => $this->whenPivotLoaded('property_type_feature', fn () => (bool) $this->pivot->is_required),
        ];
    }
}
