<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('property_type_feature', function (Blueprint $table) {
            $table->foreignId('property_type_id')->constrained()->cascadeOnDelete();
            $table->foreignId('property_feature_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_required')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->primary(['property_type_id', 'property_feature_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_type_feature');
    }
};
