<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('property_services', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name_ar');
            $table->string('name_en')->nullable();
            $table->string('category', 30)->default('maintenance');
            $table->text('description')->nullable();
            $table->string('default_frequency', 30)->default('once');
            $table->unsignedSmallInteger('default_interval')->default(1);
            $table->decimal('default_cost', 18, 2)->nullable();
            $table->foreignId('currency_id')->nullable()->constrained()->restrictOnDelete();
            $table->unsignedInteger('notify_before_minutes')->default(1440);
            $table->boolean('creates_task')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['category', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_services');
    }
};
