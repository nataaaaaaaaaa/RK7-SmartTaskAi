<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index()
    {
        // Mengambil tugas milik user yang sedang login, diurutkan berdasarkan prioritas dan deadline
        $tasks = auth()->user()->tasks()->orderBy('priority', 'desc')->orderBy('deadline', 'asc')->get();

        // Hitung statistik untuk Dashboard
        $totalTasks = $tasks->count();
        $completedTasks = $tasks->where('is_completed', true)->count();
        $pendingTasks = $totalTasks - $completedTasks;
        
        // Skor produktivitas (0-100)
        $productivityScore = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;
        
        // Rata-rata energi (fitur AI)
        $avgEnergyDone = $tasks->where('is_completed', true)->avg('energy_level') ?? 0;
        $avgEnergyPending = $tasks->where('is_completed', false)->avg('energy_level') ?? 0;

        return view('tasks.index', compact(
            'tasks', 'completedTasks', 'pendingTasks', 
            'productivityScore', 'avgEnergyDone', 'avgEnergyPending'
        ));
    }

    public function store(Request $request)
    {
        // Validasi input dari form
        $request->validate([
            'title' => 'required|max:255',
            'deadline' => 'nullable|date',
            'category' => 'required',
            'priority' => 'required',
            'energy_level' => 'nullable|integer'
        ]);

        // Simpan data ke database
        auth()->user()->tasks()->create([
            'title' => $request->title,
            'priority' => $request->priority,
            'category' => $request->category, // Pastikan ini ada
            'energy_level' => $request->energy_level ?? 2, // Default 2 jika kosong
            'deadline' => $request->deadline,
            'is_completed' => false // Set awal belum selesai
        ]);

        return redirect('/tasks')->with('success', 'Tugas berhasil ditambahkan!');
    }

    public function complete(Task $task)
    {
        // Keamanan: Cek apakah tugas ini benar milik user yang sedang login
        if ($task->user_id !== auth()->id()) abort(403);

        $task->update(['is_completed' => true]);
        
        // Bonus: Sistem XP (Pengalaman)
        $currentXp = session('user_xp', 0);
        session(['user_xp' => $currentXp + 10]);

        return redirect('/tasks')->with('success', 'Mantap! Tugas selesai.');
    }

    public function destroy(Task $task)
    {
        if ($task->user_id !== auth()->id()) abort(403);
        
        $task->delete();
        return redirect('/tasks')->with('info', 'Tugas telah dihapus.');
    }
}