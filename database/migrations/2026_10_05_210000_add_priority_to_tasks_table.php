<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table): void {
            $table->string('priority', 20)->default('normal')->after('status');
            $table->index(['status', 'priority', 'due_at']);
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table): void {
            $table->dropIndex(['status', 'priority', 'due_at']);
            $table->dropColumn('priority');
        });
    }
};
