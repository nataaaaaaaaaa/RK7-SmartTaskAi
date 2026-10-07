<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class TaskController extends Controller
{
    private function rules(): array
    {
        return [
            'title'       => 'required|string|max:120',
            'description' => 'nullable|string|max:500',
            'category'    => 'nullable|in:' . implode(',', config('tasks.categories')),
            'priority'    => 'required|in:1,2,3',
            'deadline'    => 'nullable|date',
            'task_date'   => 'nullable|date',
        ];
    }

    // Pastikan tugas milik user yang sedang login
    private function own(Task $task): Task
    {
        abort_unless($task->user_id === auth()->id(), 403);
        return $task;
    }

    public function index(Request $request)
    {
        $request->validate(['date' => 'nullable|date']);

        $date    = $request->filled('date') ? Carbon::parse($request->date)->startOfDay() : today();
        $isToday = $date->isToday();

        // To-do untuk tanggal yang dipilih (default: hari ini)
        $tasks = Task::where('user_id', auth()->id())
            ->whereDate('task_date', $date->toDateString())
            ->orderBy('is_completed')
            ->orderByDesc('priority')
            ->orderBy('created_at')
            ->get();

        // Tugas hari sebelumnya yang belum selesai (hanya saat melihat hari ini)
        $carried = $isToday
            ? Task::where('user_id', auth()->id())
                ->where('is_completed', false)
                ->whereDate('task_date', '<', $date->toDateString())
                ->orderBy('task_date')
                ->get()
            : collect();

        return view('dashboard', compact('tasks', 'carried', 'date', 'isToday'));
    }

    // Saran kategori & prioritas: pakai Claude jika ANTHROPIC_API_KEY diisi, jika tidak pakai aturan sederhana
    public function suggest(Request $request)
    {
        $data = $request->validate(['title' => 'required|string|max:120', 'deadline' => 'nullable|date']);

        if ($key = config('services.anthropic.key')) {
            try {
                $res = Http::withHeaders(['x-api-key' => $key, 'anthropic-version' => '2023-06-01'])
                    ->timeout(15)
                    ->post('https://api.anthropic.com/v1/messages', [
                        'model'      => 'claude-haiku-4-5-20251001',
                        'max_tokens' => 150,
                        'system'     => 'Kamu asisten manajemen tugas. Balas HANYA JSON tanpa teks lain: {"category":"Kuliah|Kerja|Pribadi|Lainnya","priority":1|2|3,"reason":"alasan singkat dalam bahasa Indonesia"}. priority 3=urgent, 2=normal, 1=santai.',
                        'messages'   => [[
                            'role'    => 'user',
                            'content' => "Tugas: {$data['title']}\nTenggat: " . ($data['deadline'] ?? 'tidak ada') . "\nHari ini: " . now()->toDateString(),
                        ]],
                    ]);

                if ($res->successful()) {
                    $json = json_decode(trim(preg_replace('/```(json)?/', '', $res->json('content.0.text', ''))), true);
                    if (is_array($json)
                        && in_array($json['category'] ?? null, ['Kuliah', 'Kerja', 'Pribadi', 'Lainnya'], true)
                        && in_array((int) ($json['priority'] ?? 0), [1, 2, 3], true)) {
                        return response()->json([
                            'category' => $json['category'],
                            'priority' => (int) $json['priority'],
                            'reason'   => $json['reason'] ?? '',
                            'source'   => 'ai',
                        ]);
                    }
                }
            } catch (\Throwable $e) {
                report($e); // gagal? lanjut ke aturan sederhana
            }
        }

        return response()->json($this->ruleSuggestion($data['title'], $data['deadline'] ?? null) + ['source' => 'rules']);
    }

    private function ruleSuggestion(string $title, ?string $deadline): array
    {
        $t   = mb_strtolower($title);
        $has = fn(array $words) => collect($words)->contains(fn($w) => str_contains($t, $w));

        $category = match (true) {
            $has(['kuliah', 'kuis', 'ujian', 'uas', 'uts', 'skripsi', 'dosen', 'praktikum', 'makalah', 'tugas']) => 'Kuliah',
            $has(['meeting', 'rapat', 'klien', 'laporan', 'proyek', 'presentasi', 'invoice'])                    => 'Kerja',
            $has(['beli', 'belanja', 'olahraga', 'keluarga', 'dokter', 'bayar', 'servis'])                       => 'Pribadi',
            default                                                                                              => 'Lainnya',
        };

        $days     = $deadline ? now()->startOfDay()->diffInDays(Carbon::parse($deadline)->startOfDay(), false) : null;
        $priority = $days === null ? 2 : ($days <= 1 ? 3 : ($days <= 7 ? 2 : 1));
        if ($has(['urgent', 'segera', 'ujian', 'deadline'])) {
            $priority = 3;
        }

        return ['category' => $category, 'priority' => $priority, 'reason' => 'Berdasarkan kata kunci judul dan tenggat.'];
    }

    public function store(Request $request)
    {
        $data              = $request->validate($this->rules());
        $data['user_id']   = auth()->id();
        $data['task_date'] = $data['task_date'] ?? today()->toDateString();
        Task::create($data);

        return back()->with('success', 'Tugas berhasil ditambahkan.');
    }

    public function update(Request $request, Task $task)
    {
        $data = $request->validate($this->rules());
        if (empty($data['task_date'])) {
            unset($data['task_date']); // jangan hapus tanggal lama jika tidak dikirim
        }
        $this->own($task)->update($data);

        return back()->with('success', 'Tugas berhasil diperbarui.');
    }

    public function toggle(Task $task)
    {
        $this->own($task);
        $completed = ! $task->is_completed;
        $task->update([
            'is_completed' => $completed,
            'completed_at' => $completed ? now() : null,
        ]);

        return back()->with('success', $completed ? 'Tugas ditandai selesai.' : 'Tugas dikembalikan ke daftar aktif.');
    }

    public function report()
    {
        $tasks = Task::where('user_id', auth()->id())->get();

        $byCategory = $tasks->groupBy(fn($t) => $t->category ?: 'Lainnya')->map(fn($g) => [
            'total' => $g->count(),
            'done'  => $g->where('is_completed', true)->count(),
        ]);

        $byPriority = collect([3 => 'Urgent', 2 => 'Normal', 1 => 'Santai'])->map(fn($label, $p) => [
            'label' => $label,
            'total' => $tasks->where('priority', $p)->count(),
            'done'  => $tasks->where('priority', $p)->where('is_completed', true)->count(),
        ]);

        $overdue = $tasks->filter(fn($t) => !$t->is_completed && $t->deadline
            && Carbon::parse($t->deadline)->endOfDay()->isPast())->count();

        // --- Analitik 7 hari ---
        $completed = $tasks->where('is_completed', true)->filter(fn($t) => $t->completed_at);

        $weekly = collect(range(6, 0))->map(function ($i) use ($completed) {
            $day = now()->subDays($i)->startOfDay();
            return [
                'label' => $day->translatedFormat('D d'),
                'count' => $completed->filter(fn($t) => $t->completed_at->isSameDay($day))->count(),
            ];
        });

        $withDeadline = $completed->filter(fn($t) => $t->deadline);
        $onTime       = $withDeadline->filter(
            fn($t) => $t->completed_at->lte(Carbon::parse($t->deadline)->endOfDay())
        )->count();
        $onTimeRate = $withDeadline->count() ? round($onTime / $withDeadline->count() * 100) : null;

        // Streak: hari berturut-turut ada tugas selesai (hari ini boleh belum ada)
        $doneDates = $completed->map(fn($t) => $t->completed_at->toDateString())->unique();
        $streak    = 0;
        $cursor    = now()->startOfDay();
        if (! $doneDates->contains($cursor->toDateString())) {
            $cursor->subDay();
        }
        while ($doneDates->contains($cursor->toDateString())) {
            $streak++;
            $cursor->subDay();
        }

        $weekTotal = $weekly->sum('count');
        $bestDay   = $weekly->sortByDesc('count')->first();

        return view('report', compact(
            'tasks', 'byCategory', 'byPriority', 'overdue',
            'weekly', 'weekTotal', 'bestDay', 'onTimeRate', 'streak'
        ));
    }

    public function exportCsv()
    {
        $tasks  = Task::where('user_id', auth()->id())->orderBy('created_at')->get();
        $labels = [1 => 'Santai', 2 => 'Normal', 3 => 'Urgent'];

        return response()->streamDownload(function () use ($tasks, $labels) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM supaya Excel membaca UTF-8 dengan benar
            fputcsv($out, ['Judul', 'Catatan', 'Kategori', 'Prioritas', 'Tenggat', 'Status', 'Dibuat', 'Selesai pada']);
            foreach ($tasks as $t) {
                fputcsv($out, [
                    $t->title,
                    $t->description,
                    $t->category,
                    $labels[$t->priority] ?? 'Normal',
                    $t->deadline ? Carbon::parse($t->deadline)->toDateString() : '',
                    $t->is_completed ? 'Selesai' : 'Aktif',
                    $t->created_at->toDateTimeString(),
                    $t->completed_at ? $t->completed_at->toDateTimeString() : '',
                ]);
            }
            fclose($out);
        }, 'laporan-tugas-' . now()->format('Ymd') . '.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function clearCompleted()
    {
        $count = Task::where('user_id', auth()->id())->where('is_completed', true)->delete();

        return back()->with('success', "$count tugas selesai dihapus.");
    }

    public function destroy(Task $task)
    {
        $this->own($task)->delete();

        return back()->with('success', 'Tugas dihapus.');
    }
}