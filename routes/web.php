<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DailyReportController;
use App\Http\Controllers\TaskController;
use App\Http\Middleware\EnsureUserIsAdmin;
use Illuminate\Support\Facades\Route;

// Halaman utama langsung ke register
Route::get('/', function () {
    return redirect('/login');
});

Route::middleware(['auth'])->group(function () {
    // Rute SmartTask AI
    Route::get('/kegiatan', [DailyReportController::class, 'index'])->name('dashboard');
    Route::post('/kegiatan', [DailyReportController::class, 'store'])->name('activities.store');
    Route::post('/kegiatan/kirim', [DailyReportController::class, 'submit'])->name('reports.submit');
    Route::delete('/kegiatan/{activity}', [DailyReportController::class, 'destroyActivity'])->name('activities.destroy');
    Route::post('/tasks', [TaskController::class, 'store']);
    Route::post('/tasks/suggest', [TaskController::class, 'suggest']);
    Route::delete('/tasks-completed', [TaskController::class, 'clearCompleted']);

    // Laporan (BARU)
    Route::get('/report', [DailyReportController::class, 'history'])->name('report');
    Route::get('/report/export', [DailyReportController::class, 'exportCsv'])->name('report.export');

    Route::put('/tasks/{task}', [TaskController::class, 'update']);
    Route::patch('/tasks/{task}/toggle', [TaskController::class, 'toggle']);
    Route::delete('/tasks/{task}', [TaskController::class, 'destroy']);

    // Rute Profile (wajib ada untuk Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
});

// Panel admin
Route::middleware(['auth', EnsureUserIsAdmin::class])
    ->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/activity', [AdminController::class, 'activity'])->name('activity');
        Route::get('/users', [AdminController::class, 'users'])->name('users');
        Route::patch('/users/{user}/role', [AdminController::class, 'updateRole'])->name('users.role');
        Route::delete('/users/{user}', [AdminController::class, 'destroyUser'])->name('users.destroy');
        Route::get('/tasks', [AdminController::class, 'tasks'])->name('tasks');
        Route::get('/export', [AdminController::class, 'exportCsv'])->name('export');
    });

require __DIR__ . '/auth.php';
