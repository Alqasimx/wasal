<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('streets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('city_id')->constrained()->cascadeOnDelete();
            $table->foreignId('district_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name_ar');
            $table->string('name_en')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['city_id', 'district_id', 'name_ar']);
            $table->index(['district_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('streets');
    }
};
