<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\PropertyTypeResource;
use App\Models\PropertyType;
use App\Http\Controllers\Controller;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PropertyTypeController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $types = PropertyType::query()
            ->where('is_active', true)
            ->with(['features' => fn ($query) => $query
                ->where('property_features.is_active', true)
                ->orderBy('property_type_feature.sort_order')])
            ->orderBy('sort_order')
            ->orderBy('name_ar')
            ->get();

        return PropertyTypeResource::collection($types);
    }
}
