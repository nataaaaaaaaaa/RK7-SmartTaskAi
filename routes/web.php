<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

// Halaman utama langsung ke register
Route::get('/', function () {
    return redirect('/register');
});

Route::middleware(['auth'])->group(function () {
    // Rute SmartTask AI
    Route::get('/tasks', [TaskController::class, 'index'])->name('dashboard');
    Route::post('/tasks', [TaskController::class, 'store']);
    Route::post('/tasks/suggest', [TaskController::class, 'suggest']);
    Route::delete('/tasks-completed', [TaskController::class, 'clearCompleted']);

    // Laporan (BARU)
    Route::get('/report', [TaskController::class, 'report'])->name('report');
    Route::get('/report/export', [TaskController::class, 'exportCsv'])->name('report.export');

    Route::put('/tasks/{task}', [TaskController::class, 'update']);
    Route::patch('/tasks/{task}/toggle', [TaskController::class, 'toggle']);
    Route::delete('/tasks/{task}', [TaskController::class, 'destroy']);

    // Rute Profile (wajib ada untuk Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';