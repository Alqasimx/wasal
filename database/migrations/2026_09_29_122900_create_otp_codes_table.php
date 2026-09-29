<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('otp_codes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('channel', 20);
            $table->string('destination');

            $table->string('code_hash');

            $table->string('purpose', 50);

            $table->timestamp('expires_at');

            $table->timestamp('verified_at')
                ->nullable();

            $table->unsignedInteger('attempts')
                ->default(0);

            $table->timestamp('created_at')
                ->useCurrent();

            $table->index(['destination', 'purpose']);
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('otp_codes');
    }
};