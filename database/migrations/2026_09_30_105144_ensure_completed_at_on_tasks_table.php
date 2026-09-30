<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('tasks', 'completed_at')) {
            Schema::table('tasks', function (Blueprint $table) {
                $table->timestamp('completed_at')->nullable()->after('is_completed');
            });

            // Isi data lama supaya grafik tidak kosong (perkiraan)
            DB::table('tasks')->where('is_completed', true)->update(['completed_at' => DB::raw('updated_at')]);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('tasks', 'completed_at')) {
            Schema::table('tasks', function (Blueprint $table) {
                $table->dropColumn('completed_at');
            });
        }
    }
};