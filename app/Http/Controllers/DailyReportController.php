<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\DailyReport;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DailyReportController extends Controller
{
    public function index(Request $request)
    {
        $request->validate(['date' => 'nullable|date']);

        $current = DailyReport::currentDate();
        $date    = $request->filled('date') ? Carbon::parse($request->date)->startOfDay() : $current->copy();
        if ($date->greaterThan($current)) {
            $date = $current->copy();
        }

        $report = DailyReport::with('activities.photos')
            ->where('user_id', auth()->id())
            ->whereDate('report_date', $date->toDateString())
            ->first();

        // Laporan hari-hari sebelumnya yang masih draf (belum dikirim)
        $pending = DailyReport::withCount('activities')
            ->where('user_id', auth()->id())
            ->whereNull('submitted_at')
            ->whereDate('report_date', '<', $current->toDateString())
            ->orderBy('report_date')
            ->get();

        $deadline = $date->copy()->addDay()->setTime((int) config('report.cutoff_hour'), 0);
        $overdue  = now()->greaterThan($deadline);
        $canEdit  = ! ($report && $report->submitted_at && $overdue);

        return view('dashboard', compact('date', 'current', 'report', 'pending', 'deadline', 'overdue', 'canEdit'));
    }

    public function store(Request $request): RedirectResponse
    {
        $max = (int) config('report.max_photos');
        $kb  = (int) config('report.max_photo_kb');

        $data = $request->validate([
            'date'        => ['required', 'date'],
            'title'       => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:1000'],
            'photos'      => ['required', 'array', 'min:1', 'max:' . $max],
            'photos.*'    => ['image', 'mimes:jpg,jpeg,png,webp', 'max:' . $kb],
        ], [
            'title.required'    => 'Judul kegiatan wajib diisi.',
            'photos.required'   => 'Foto kegiatan wajib dilampirkan.',
            'photos.min'        => 'Lampirkan minimal satu foto.',
            'photos.max'        => "Maksimal {$max} foto per kegiatan.",
            'photos.*.image'    => 'File yang diunggah harus berupa gambar.',
            'photos.*.mimes'    => 'Format foto harus JPG, PNG, atau WEBP.',
            'photos.*.max'      => 'Ukuran tiap foto maksimal ' . round($kb / 1024) . ' MB.',
            'photos.*.uploaded' => 'Foto gagal diunggah. Ukurannya melebihi batas server.',
        ]);

        $date = Carbon::parse($data['date'])->startOfDay();
        abort_if($date->greaterThan(DailyReport::currentDate()), 422);

        $report = DailyReport::firstOrCreate([
            'user_id'     => auth()->id(),
            'report_date' => $date->toDateString(),
        ]);

        if (! $this->canEdit($report)) {
            return back()->withErrors('Laporan ini sudah melewati batas kirim dan tidak dapat diubah.');
        }

        DB::transaction(function () use ($report, $data, $request) {
            $activity = $report->activities()->create([
                'title'       => $data['title'],
                'description' => $data['description'] ?? null,
            ]);

            foreach ($request->file('photos') as $file) {
                $activity->photos()->create([
                    'path' => $file->store('activity-photos/' . now()->format('Y/m'), 'public'),
                ]);
            }
        });

        return redirect()
            ->route('dashboard', ['date' => $date->toDateString()])
            ->with('success', 'Kegiatan berhasil ditambahkan.');
    }

    public function destroyActivity(Activity $activity): RedirectResponse
    {
        $report = $activity->report;
        abort_unless($report->user_id === auth()->id(), 403);

        if (! $this->canEdit($report)) {
            return back()->withErrors('Laporan ini sudah melewati batas kirim dan tidak dapat diubah.');
        }

        foreach ($activity->photos as $photo) {
            Storage::disk('public')->delete($photo->path);
        }
        $activity->delete();

        return back()->with('success', 'Kegiatan dihapus.');
    }

    public function submit(Request $request): RedirectResponse
    {
        $data = $request->validate(['date' => ['required', 'date']]);

        $report = DailyReport::withCount('activities')
            ->where('user_id', auth()->id())
            ->whereDate('report_date', Carbon::parse($data['date'])->toDateString())
            ->first();

        if (! $report || $report->activities_count === 0) {
            return back()->withErrors('Tambahkan minimal satu kegiatan sebelum mengirim laporan.');
        }
        if ($report->submitted_at) {
            return back()->withErrors('Laporan ini sudah dikirim.');
        }

        $report->update([
            'submitted_at' => now(),
            'is_late'      => $report->isOverdue(),
        ]);

        return back()->with('success', $report->is_late
            ? 'Laporan terkirim, tetapi ditandai terlambat karena melewati batas kirim.'
            : 'Laporan harian berhasil dikirim.');
    }

    // ---------- Laporan riwayat kegiatan ----------
    private function resolveRange(Request $request): array
    {
        $data    = $request->validate(['from' => ['nullable', 'date'], 'to' => ['nullable', 'date']]);
        $current = DailyReport::currentDate();

        $to = isset($data['to']) ? Carbon::parse($data['to'])->startOfDay() : $current->copy();
        if ($to->greaterThan($current)) {
            $to = $current->copy();
        }

        $from = isset($data['from']) ? Carbon::parse($data['from'])->startOfDay() : $to->copy()->subDays(6);
        if ($from->greaterThan($to)) {
            $from = $to->copy();
        }
        if ($from->diffInDays($to) > 61) {
            $from = $to->copy()->subDays(61); // maksimal sekitar 2 bulan sekali tampil
        }

        return [$from, $to, $current];
    }

    private function buildDays(Carbon $from, Carbon $to)
    {
        $reports = DailyReport::with('activities.photos')
            ->where('user_id', auth()->id())
            ->whereBetween('report_date', [$from->toDateString(), $to->toDateString()])
            ->get()
            ->keyBy(fn($r) => $r->report_date->toDateString());

        $workDays = config('report.work_days');
        $days     = collect();

        for ($d = $to->copy(); $d->greaterThanOrEqualTo($from); $d->subDay()) {
            $report  = $reports->get($d->toDateString());
            $workday = in_array($d->dayOfWeekIso, $workDays);

            if ($report && $report->submitted_at) {
                $status = $report->is_late ? 'late' : 'submitted';
            } elseif ($report && $report->activities->isNotEmpty()) {
                $status = 'draft';
            } else {
                $status = $workday ? 'missing' : 'off';
            }

            $days->push(['date' => $d->copy(), 'workday' => $workday, 'report' => $report, 'status' => $status]);
        }

        return $days;
    }

    public function history(Request $request)
    {
        [$from, $to, $current] = $this->resolveRange($request);
        $days = $this->buildDays($from, $to);

        $workdays  = $days->where('workday', true)->count();
        $submitted = $days->where('status', 'submitted')->count();
        $late      = $days->where('status', 'late')->count();

        $stats = [
            'submitted'  => $submitted,
            'late'       => $late,
            'draft'      => $days->where('status', 'draft')->count(),
            'missing'    => $days->where('status', 'missing')->count(),
            'workdays'   => $workdays,
            'compliance' => $workdays ? min(100, round(($submitted + $late) / $workdays * 100)) : 0,
            'activities' => $days->sum(fn($x) => $x['report'] ? $x['report']->activities->count() : 0),
            'photos'     => $days->sum(fn($x) => $x['report'] ? $x['report']->activities->sum(fn($a) => $a->photos->count()) : 0),
        ];

        return view('report', compact('days', 'stats', 'from', 'to', 'current'));
    }

    public function exportCsv(Request $request)
    {
        [$from, $to] = $this->resolveRange($request);
        $days   = $this->buildDays($from, $to)->reverse();
        $labels = ['submitted' => 'Terkirim tepat waktu', 'late' => 'Terlambat', 'draft' => 'Draf belum dikirim', 'missing' => 'Belum mengisi', 'off' => 'Libur'];

        return response()->streamDownload(function () use ($days, $labels) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Tanggal', 'Status laporan', 'Dikirim pada', 'Kegiatan', 'Keterangan', 'Jumlah foto', 'Tautan foto']);

            foreach ($days as $day) {
                $report = $day['report'];

                if (! $report || $report->activities->isEmpty()) {
                    if ($day['status'] !== 'off') {
                        fputcsv($out, [$day['date']->toDateString(), $labels[$day['status']], '', '', '', '', '']);
                    }
                    continue;
                }

                foreach ($report->activities as $a) {
                    fputcsv($out, [
                        $day['date']->toDateString(),
                        $labels[$day['status']],
                        $report->submitted_at?->format('Y-m-d H:i'),
                        $a->title,
                        $a->description,
                        $a->photos->count(),
                        $a->photos->map(fn($p) => $p->url)->implode(' '),
                    ]);
                }
            }
            fclose($out);
        }, 'laporan-kegiatan-' . now()->format('Ymd') . '.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    // Laporan yang sudah dikirim terkunci setelah batas kirim lewat
    private function canEdit(DailyReport $report): bool
    {
        return ! ($report->submitted_at && $report->isOverdue());
    }
}
