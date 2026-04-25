<?php

use App\Http\Controllers\TaskController;
use App\Http\Controllers\ProfileController; // Tambahkan ini
use Illuminate\Support\Facades\Route;

// Halaman utama langsung ke register
Route::get('/', function () {
    return redirect('/register'); // Pakai path langsung agar lebih tegas
});

Route::middleware(['auth'])->group(function () {
    // Rute Aplikasi SmartTask AI kita
    Route::get('/tasks', [TaskController::class, 'index'])->name('dashboard');
    Route::post('/tasks', [TaskController::class, 'store']);
    Route::patch('/tasks/{task}/complete', [TaskController::class, 'complete']);
    Route::delete('/tasks/{task}', [TaskController::class, 'destroy']);

    // Rute Profile (Wajib ada agar Breeze tidak error)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    if (!Schema::hasColumn('tasks', 'deadline')) {
        Schema::table('tasks', function (Blueprint $table) {
            $table->date('deadline')->nullable();
            $table->string('category')->default('Umum');
        });
        return "Database Berhasil Diperbarui! Silakan hapus rute ini.";
    }
    return "Database sudah update.";
});

require __DIR__.'/auth.php';
