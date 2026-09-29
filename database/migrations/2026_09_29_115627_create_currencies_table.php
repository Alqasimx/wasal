<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();

            $table->string('code', 20)->unique();

            $table->string('iso_code', 10)->nullable();

            $table->string('name_ar');
            $table->string('name_en');

            $table->string('symbol', 20)->nullable();

            $table->boolean('is_active')->default(true);

            $table->decimal('exchange_rate', 18, 6)->nullable();

            $table->timestamps();

            $table->index('iso_code');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('currencies');
    }
};