<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->date('task_date')->nullable()->index()->after('deadline');
        });

        // Tugas lama dianggap dikerjakan pada hari ia dibuat
        DB::table('tasks')->update(['task_date' => DB::raw('DATE(created_at)')]);
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn('task_date');
        });
    }
};