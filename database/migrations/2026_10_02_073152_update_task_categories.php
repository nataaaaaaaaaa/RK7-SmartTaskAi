<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('tasks')->where('category', 'Kerja')->update(['category' => 'Operasional']);
        DB::table('tasks')->whereIn('category', ['Kuliah', 'Pribadi'])->update(['category' => 'Lainnya']);
    }

    public function down(): void
    {
        // Data lama tidak bisa dikembalikan secara otomatis.
    }
};