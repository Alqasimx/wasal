<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by_user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('property_type_id')->constrained()->restrictOnDelete();
            $table->string('internal_code')->unique();
            $table->string('title_ar');
            $table->string('title_en')->nullable();
            $table->text('description_ar')->nullable();
            $table->text('description_en')->nullable();
            $table->string('status', 30)->default('draft');
            $table->foreignId('city_id')->constrained()->restrictOnDelete();
            $table->foreignId('district_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('neighborhood_id')->nullable()->constrained()->nullOnDelete();
            $table->string('street_name')->nullable();
            $table->string('public_location_text')->nullable();
            $table->text('exact_address')->nullable();
            $table->decimal('public_latitude', 10, 7)->nullable();
            $table->decimal('public_longitude', 10, 7)->nullable();
            $table->decimal('exact_latitude', 10, 7)->nullable();
            $table->decimal('exact_longitude', 10, 7)->nullable();
            $table->decimal('area', 12, 2)->nullable();
            $table->unsignedSmallInteger('floors_count')->nullable();
            $table->unsignedSmallInteger('units_count')->nullable();
            $table->unsignedSmallInteger('year_built')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['city_id', 'district_id', 'neighborhood_id']);
            $table->index(['status', 'property_type_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
