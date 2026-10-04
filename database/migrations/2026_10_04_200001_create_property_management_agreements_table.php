<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('property_management_agreements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->restrictOnDelete();
            $table->foreignId('property_owner_id')->nullable()->constrained('property_owners')->restrictOnDelete();
            $table->foreignId('assigned_manager_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by_user_id')->constrained('users')->restrictOnDelete();
            $table->date('starts_at');
            $table->date('ends_at')->nullable();
            $table->string('status', 20)->default('active');
            $table->string('management_fee_type', 20)->default('percentage');
            $table->decimal('management_fee_value', 12, 2)->default(0);
            $table->foreignId('currency_id')->nullable()->constrained()->restrictOnDelete();
            $table->json('included_services')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['property_id', 'status']);
            $table->index(['starts_at', 'ends_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_management_agreements');
    }
};
