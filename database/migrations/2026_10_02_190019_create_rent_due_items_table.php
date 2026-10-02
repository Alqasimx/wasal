<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up(): void { Schema::create('rent_due_items', function (Blueprint $table) { $table->id(); $table->foreignId('tenancy_id')->constrained()->cascadeOnDelete(); $table->date('due_date'); $table->decimal('amount', 18, 2); $table->foreignId('currency_id')->constrained()->restrictOnDelete(); $table->string('status', 20)->default('due'); $table->decimal('paid_amount', 18, 2)->default(0); $table->timestamps(); $table->index(['due_date','status']); }); } public function down(): void { Schema::dropIfExists('rent_due_items'); } };
