<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PropertyFeatureResource;
use App\Models\PropertyFeature;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PropertyFeatureController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $features = PropertyFeature::query()
            ->where('is_active', true)
            ->when($request->integer('property_type_id'), function ($query, $propertyTypeId) {
                $query->whereHas('propertyTypes', fn ($types) => $types->whereKey($propertyTypeId));
            })
            ->orderBy('sort_order')
            ->orderBy('name_ar')
            ->get();

        return PropertyFeatureResource::collection($features);
    }
}
