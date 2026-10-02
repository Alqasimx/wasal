<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('related_type')->nullable();
            $table->unsignedBigInteger('related_id')->nullable();
            $table->foreignId('assigned_to_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by_user_id')->constrained('users')->restrictOnDelete();
            $table->dateTime('due_at')->nullable();
            $table->string('recurrence', 20)->default('once');
            $table->string('status', 20)->default('pending');
            $table->unsignedInteger('notify_before_minutes')->default(1440);
            $table->dateTime('notified_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'due_at']);
            $table->index(['assigned_to_user_id', 'due_at']);
            $table->index(['related_type', 'related_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
