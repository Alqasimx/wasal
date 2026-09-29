<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')
                ->nullable()
                ->unique()
                ->after('email');

            $table->string('whatsapp_phone')
                ->nullable()
                ->after('phone');

            $table->string('preferred_language', 5)
                ->default('ar')
                ->after('password');

            $table->foreignId('preferred_currency_id')
                ->nullable()
                ->after('preferred_language')
                ->constrained('currencies')
                ->nullOnDelete();

            $table->foreignId('preferred_city_id')
                ->nullable()
                ->after('preferred_currency_id')
                ->constrained('cities')
                ->nullOnDelete();

            $table->string('status')
                ->default('active')
                ->after('preferred_city_id');

            $table->timestamp('last_login_at')
                ->nullable()
                ->after('status');

            $table->softDeletes();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['preferred_currency_id']);
            $table->dropForeign(['preferred_city_id']);

            $table->dropUnique(['phone']);

            $table->dropColumn([
                'phone',
                'whatsapp_phone',
                'preferred_language',
                'preferred_currency_id',
                'preferred_city_id',
                'status',
                'last_login_at',
                'deleted_at',
            ]);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable(false)->change();
        });
    }
};