<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    // ---------- Ringkasan ----------
    public function dashboard()
    {
        $totalTasks = Task::count();
        $doneTasks  = Task::where('is_completed', true)->count();

        $stats = [
            'users'    => User::count(),
            'admins'   => User::where('role', 'admin')->count(),
            'newUsers' => User::where('created_at', '>=', now()->subDays(7))->count(),
            'tasks'    => $totalTasks,
            'done'     => $doneTasks,
            'active'   => $totalTasks - $doneTasks,
            'overdue'  => Task::where('is_completed', false)->whereNotNull('deadline')->whereDate('deadline', '<', today())->count(),
            'percent'  => $totalTasks ? round($doneTasks / $totalTasks * 100) : 0,
        ];

        $byCategory = Task::selectRaw("COALESCE(category, 'Lainnya') as category, COUNT(*) as total, SUM(CASE WHEN is_completed = 1 THEN 1 ELSE 0 END) as done")
            ->groupByRaw("COALESCE(category, 'Lainnya')")
            ->get();

        $byPriority = collect([3 => 'Urgent', 2 => 'Normal', 1 => 'Santai'])->map(fn($label, $p) => [
            'label' => $label,
            'total' => Task::where('priority', $p)->count(),
        ]);

        // Tugas selesai per hari (7 hari terakhir, seluruh pengguna)
        $completed = Task::whereNotNull('completed_at')
            ->where('completed_at', '>=', now()->subDays(6)->startOfDay())
            ->pluck('completed_at');

        $weekly = collect(range(6, 0))->map(function ($i) use ($completed) {
            $day = now()->subDays($i)->startOfDay();
            return [
                'label' => $day->translatedFormat('D d'),
                'count' => $completed->filter(fn($d) => Carbon::parse($d)->isSameDay($day))->count(),
            ];
        });

        $topUsers = User::withCount([
                'tasks',
                'tasks as done_count' => fn($q) => $q->where('is_completed', true),
            ])->orderByDesc('tasks_count')->take(5)->get();

        $recentUsers = User::latest()->take(5)->get();

        return view('admin.dashboard', compact('stats', 'byCategory', 'byPriority', 'weekly', 'topUsers', 'recentUsers'));
    }

    // ---------- Aktivitas harian per karyawan ----------
    public function activity(Request $request)
    {
        $request->validate(['date' => 'nullable|date']);

        $date = $request->filled('date') ? Carbon::parse($request->date)->startOfDay() : today();

        $employees = User::where('role', 'user')
            ->with(['tasks' => fn($q) => $q->whereDate('task_date', $date->toDateString())
                ->orderBy('is_completed')->orderByDesc('priority')->orderBy('created_at')])
            ->withCount(['tasks as carried_count' => fn($q) => $q->where('is_completed', false)
                ->whereDate('task_date', '<', $date->toDateString())])
            ->orderBy('name')
            ->get();

        $summary = [
            'employees' => $employees->count(),
            'active'    => $employees->filter(fn($u) => $u->tasks->isNotEmpty())->count(),
            'idle'      => $employees->filter(fn($u) => $u->tasks->isEmpty())->count(),
            'tasks'     => $employees->sum(fn($u) => $u->tasks->count()),
            'done'      => $employees->sum(fn($u) => $u->tasks->where('is_completed', true)->count()),
        ];

        return view('admin.activity', compact('employees', 'date', 'summary'));
    }

    // ---------- Pengguna ----------
    public function users(Request $request)
    {
        $users = User::withCount([
                'tasks',
                'tasks as done_count' => fn($q) => $q->where('is_completed', true),
            ])
            ->when($request->q, fn($q, $s) => $q->where(fn($w) => $w->where('name', 'like', "%$s%")->orWhere('email', 'like', "%$s%")->orWhere('phone', 'like', "%$s%")))
            ->when($request->role, fn($q, $r) => $q->where('role', $r))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.users', compact('users'));
    }

    public function updateRole(Request $request, User $user)
    {
        $data = $request->validate(['role' => 'required|in:user,admin']);

        if ($user->id === auth()->id()) {
            return back()->withErrors('Anda tidak bisa mengubah role akun sendiri.');
        }
        if ($user->isAdmin() && $data['role'] === 'user' && User::where('role', 'admin')->count() <= 1) {
            return back()->withErrors('Minimal harus ada satu admin.');
        }

        $user->forceFill(['role' => $data['role']])->save();

        return back()->with('success', "Role {$user->name} diubah menjadi {$data['role']}.");
    }

    public function destroyUser(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->withErrors('Anda tidak bisa menghapus akun sendiri.');
        }
        if ($user->isAdmin() && User::where('role', 'admin')->count() <= 1) {
            return back()->withErrors('Admin terakhir tidak bisa dihapus.');
        }

        DB::transaction(function () use ($user) {
            Task::where('user_id', $user->id)->delete();
            $user->delete();
        });

        return back()->with('success', "Pengguna {$user->name} beserta tugasnya dihapus.");
    }

    // ---------- Semua tugas ----------
    public function tasks(Request $request)
    {
        $tasks = Task::with('user:id,name,email')
            ->when($request->q, fn($q, $s) => $q->where('title', 'like', "%$s%"))
            ->when($request->status === 'active', fn($q) => $q->where('is_completed', false))
            ->when($request->status === 'done', fn($q) => $q->where('is_completed', true))
            ->when($request->category, fn($q, $c) => $q->where('category', $c))
            ->when($request->user_id, fn($q, $u) => $q->where('user_id', $u))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $users = User::orderBy('name')->get(['id', 'name']);

        return view('admin.tasks', compact('tasks', 'users'));
    }

    // ---------- Ekspor semua tugas ----------
    public function exportCsv()
    {
        $labels = [1 => 'Santai', 2 => 'Normal', 3 => 'Urgent'];

        return response()->streamDownload(function () use ($labels) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Pengguna', 'Email', 'Judul', 'Kategori', 'Prioritas', 'Tenggat', 'Status', 'Dibuat', 'Selesai pada']);
            Task::with('user:id,name,email')->orderBy('created_at')->chunk(500, function ($chunk) use ($out, $labels) {
                foreach ($chunk as $t) {
                    fputcsv($out, [
                        $t->user?->name,
                        $t->user?->email,
                        $t->title,
                        $t->category,
                        $labels[$t->priority] ?? 'Normal',
                        $t->deadline ? Carbon::parse($t->deadline)->toDateString() : '',
                        $t->is_completed ? 'Selesai' : 'Aktif',
                        $t->created_at->toDateTimeString(),
                        $t->completed_at ? Carbon::parse($t->completed_at)->toDateTimeString() : '',
                    ]);
                }
            });
            fclose($out);
        }, 'admin-semua-tugas-' . now()->format('Ymd') . '.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}