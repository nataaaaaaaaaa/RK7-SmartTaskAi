<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Tugas - SmartTask AI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { background: #f0f2f5; font-family: 'Plus Jakarta Sans', sans-serif; color: #1e293b; }
        .card { border: none; border-radius: 20px; box-shadow: 0 10px 15px -3px rgba(0,0,0,.06); }
        .progress { height: 10px; border-radius: 10px; }
        .progress-bar { background: linear-gradient(90deg, #4f46e5, #7c3aed); }
        .btn-gradient { background: linear-gradient(90deg, #4f46e5, #7c3aed); color: #fff; border: 0; font-weight: 600; }
        .btn-gradient:hover { color: #fff; opacity: .92; }
        .bar-wrap { display:flex; align-items:flex-end; gap:10px; height:150px; }
        .bar-col  { flex:1; text-align:center; }
        .bar      { background:linear-gradient(180deg,#7c3aed,#4f46e5); border-radius:8px 8px 0 0; min-height:4px; }
        .bar.zero { background:#e2e8f0; }
        @media print {
            .no-print { display: none !important; }
            body { background: #fff; }
            .card { box-shadow: none; border: 1px solid #e2e8f0; }
        }
    </style>
</head>
<body>
@php
    $total = $tasks->count();
    $done  = $tasks->where('is_completed', true)->count();
    $rate  = $total ? round($done / $total * 100) : 0;
    $pct   = fn($d, $t) => $t ? round($d / $t * 100) : 0;
@endphp

<div class="container py-4" style="max-width: 860px;">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h3 class="fw-bold mb-0"><i class="bi bi-bar-chart-line me-2"></i>Laporan Produktivitas</h3>
            <small class="text-muted">{{ Auth::user()->name }} · dibuat {{ now()->translatedFormat('d F Y') }}</small>
        </div>
        <div class="d-flex gap-2 no-print">
            <a href="/tasks" class="btn btn-light rounded-pill shadow-sm"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
            <a href="/report/export" class="btn btn-outline-success rounded-pill"><i class="bi bi-file-earmark-spreadsheet me-1"></i>Unduh CSV</a>
            <button onclick="window.print()" class="btn btn-gradient rounded-pill"><i class="bi bi-printer me-1"></i>Cetak / PDF</button>
        </div>
    </div>

    @if($total === 0)
        <div class="card p-5 text-center">
            <i class="bi bi-inbox fs-1 text-muted mb-2"></i>
            <h6 class="fw-bold">Belum ada data untuk dilaporkan</h6>
            <p class="text-muted small mb-0">Tambahkan beberapa tugas dulu, lalu kembali ke halaman ini.</p>
        </div>
    @else
        <div class="row g-3 mb-3 text-center">
            <div class="col-6 col-md-3"><div class="card p-3"><small class="text-muted">Total tugas</small><h3 class="fw-bold m-0">{{ $total }}</h3></div></div>
            <div class="col-6 col-md-3"><div class="card p-3"><small class="text-muted">Selesai</small><h3 class="fw-bold m-0 text-success">{{ $done }}</h3></div></div>
            <div class="col-6 col-md-3"><div class="card p-3"><small class="text-muted">Belum selesai</small><h3 class="fw-bold m-0 text-warning">{{ $total - $done }}</h3></div></div>
            <div class="col-6 col-md-3"><div class="card p-3"><small class="text-muted">Terlambat</small><h3 class="fw-bold m-0 text-danger">{{ $overdue }}</h3></div></div>
        </div>

        <div class="card p-4 mb-3">
            <div class="d-flex justify-content-between fw-bold mb-2"><span>Tingkat penyelesaian</span><span>{{ $rate }}%</span></div>
            <div class="progress"><div class="progress-bar" style="width: {{ $rate }}%"></div></div>
            <p class="small text-muted mt-3 mb-0">
                @if($rate >= 80) Kerja bagus! Hampir semua tugasmu sudah selesai.
                @elseif($rate >= 50) Sudah separuh jalan. Fokus ke tugas yang tersisa, terutama yang mendekati tenggat.
                @else Masih banyak tugas terbuka. Mulai dari yang paling urgent agar tidak menumpuk.
                @endif
                @if($overdue > 0) Ada <strong>{{ $overdue }}</strong> tugas yang sudah lewat tenggat. @endif
            </p>
        </div>

        {{-- Analitik 7 hari --}}
        @php $max = max(1, $weekly->max('count')); @endphp
        <div class="card p-4 mb-3">
            <h6 class="fw-bold mb-3"><i class="bi bi-graph-up-arrow me-2"></i>Analitik 7 hari terakhir</h6>

            <div class="row g-3 text-center mb-4">
                <div class="col-4">
                    <small class="text-muted">Selesai minggu ini</small>
                    <h3 class="fw-bold m-0">{{ $weekTotal }}</h3>
                </div>
                <div class="col-4">
                    <small class="text-muted">Tepat waktu</small>
                    <h3 class="fw-bold m-0 {{ ($onTimeRate ?? 100) >= 70 ? 'text-success' : 'text-danger' }}">
                        {{ $onTimeRate !== null ? $onTimeRate . '%' : '-' }}
                    </h3>
                </div>
                <div class="col-4">
                    <small class="text-muted">Streak</small>
                    <h3 class="fw-bold m-0 text-warning">{{ $streak }} <small class="fs-6">hari</small></h3>
                </div>
            </div>

            <div class="bar-wrap">
                @foreach($weekly as $d)
                    <div class="bar-col">
                        <div class="small fw-bold">{{ $d['count'] }}</div>
                        <div class="bar {{ $d['count'] ? '' : 'zero' }}" style="height: {{ $d['count'] / $max * 100 }}px"></div>
                        <div class="small text-muted mt-1">{{ $d['label'] }}</div>
                    </div>
                @endforeach
            </div>

            <p class="small text-muted mt-3 mb-0">
                @if($weekTotal === 0)
                    Belum ada tugas yang diselesaikan dalam 7 hari terakhir. Coba selesaikan satu tugas kecil hari ini untuk memulai streak.
                @else
                    Kamu menyelesaikan <strong>{{ $weekTotal }}</strong> tugas minggu ini; hari paling produktif adalah
                    <strong>{{ $bestDay['label'] }}</strong> ({{ $bestDay['count'] }} tugas).
                    @if($onTimeRate !== null && $onTimeRate < 70) Ketepatan waktumu masih rendah, coba mulai tugas lebih awal dari tenggatnya. @endif
                    @if($streak >= 3) Streak {{ $streak }} hari, pertahankan! @endif
                @endif
            </p>
        </div>

        <div class="row g-3">
            <div class="col-md-6">
                <div class="card p-4 h-100">
                    <h6 class="fw-bold mb-3">Per kategori</h6>
                    @foreach($byCategory as $name => $c)
                        <div class="mb-3">
                            <div class="d-flex justify-content-between small"><span>{{ $name }}</span><span class="text-muted">{{ $c['done'] }}/{{ $c['total'] }}</span></div>
                            <div class="progress"><div class="progress-bar" style="width: {{ $pct($c['done'], $c['total']) }}%"></div></div>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="col-md-6">
                <div class="card p-4 h-100">
                    <h6 class="fw-bold mb-3">Per prioritas</h6>
                    @foreach($byPriority as $p)
                        <div class="mb-3">
                            <div class="d-flex justify-content-between small"><span>{{ $p['label'] }}</span><span class="text-muted">{{ $p['done'] }}/{{ $p['total'] }}</span></div>
                            <div class="progress"><div class="progress-bar" style="width: {{ $pct($p['done'], $p['total']) }}%"></div></div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>
</body>
</html>