<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('property_requests', function (Blueprint $table) {
            $table->id();
            $table->string('reference_number')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('source', 20)->default('admin');
            $table->string('purpose', 20);
            $table->foreignId('property_type_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('city_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('district_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('neighborhood_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('min_price', 18, 2)->nullable();
            $table->decimal('max_price', 18, 2)->nullable();
            $table->foreignId('currency_id')->nullable()->constrained()->nullOnDelete();
            $table->json('requirements_json')->nullable();
            $table->boolean('wants_field_search')->default(false);
            $table->foreignId('assigned_to_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 30)->default('new');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['status', 'purpose']);
            $table->index(['city_id', 'district_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_requests');
    }
};
