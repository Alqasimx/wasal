<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rent_payments', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_number')->unique();
            $table->foreignId('rent_due_item_id')->constrained()->restrictOnDelete();
            $table->foreignId('tenancy_id')->constrained()->restrictOnDelete();
            $table->decimal('amount', 18, 2);
            $table->foreignId('currency_id')->constrained()->restrictOnDelete();
            $table->dateTime('paid_at');
            $table->string('payment_method', 30)->default('cash');
            $table->foreignId('bank_id')->nullable()->constrained()->restrictOnDelete();
            $table->string('reference_number')->nullable();
            $table->string('attachment_path')->nullable();
            $table->text('notes')->nullable();
            $table->string('status', 20)->default('posted');
            $table->foreignId('recorded_by_user_id')->constrained('users')->restrictOnDelete();
            $table->dateTime('voided_at')->nullable();
            $table->foreignId('voided_by_user_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->text('void_reason')->nullable();
            $table->timestamps();

            $table->index(['rent_due_item_id', 'status']);
            $table->index(['paid_at', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rent_payments');
    }
};
