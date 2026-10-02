<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('properties', 'gallery')) {
            return;
        }

        Schema::table('properties', function (Blueprint $table) {
            $table->json('gallery')->nullable()->after('year_built');
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            if (Schema::hasColumn('properties', 'gallery')) {
                $table->dropColumn('gallery');
            }
        });
    }
};
