<!DOCTYPE html>
<html lang="id" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SmartTask AI - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg: #f0f2f5; --surface: #ffffff; --surface-2: #f1f5f9;
            --text: #1e293b; --muted: #64748b; --brand: #4f46e5; --brand-2: #7c3aed;
            --prio-1: #10b981; --prio-2: #f59e0b; --prio-3: #ef4444;
        }
        [data-bs-theme="dark"] {
            --bg: #0f172a; --surface: #1e293b; --surface-2: #334155;
            --text: #e2e8f0; --muted: #94a3b8; --brand: #818cf8;
        }
        body { background: var(--bg); color: var(--text); font-family: 'Plus Jakarta Sans', sans-serif; }
        .navbar { background: linear-gradient(135deg, #4f46e5, #7c3aed); box-shadow: 0 4px 20px rgba(79,70,229,.25); }
        .card { background: var(--surface); border: none; border-radius: 20px; box-shadow: 0 10px 15px -3px rgba(0,0,0,.06); }
        .bg-soft { background: var(--surface-2) !important; color: var(--text); }
        .text-muted, .form-label { color: var(--muted) !important; }
        .stat-card { border-left: 6px solid var(--brand); }
        .stat-card.warn { border-left-color: var(--prio-2); }
        .stat-card.ok { border-left-color: var(--prio-1); }
        .stat-card.late { border-left-color: var(--prio-3); }

        .progress { height: 10px; border-radius: 10px; background: var(--surface-2); }
        .progress-bar { background: linear-gradient(90deg, #4f46e5, #7c3aed); }

        .task-item { background: var(--surface); border-radius: 15px; margin-bottom: 12px; border-left: 6px solid var(--surface-2); transition: box-shadow .2s; }
        .task-item:hover { box-shadow: 0 5px 15px rgba(0,0,0,.12); }
        .prio-3 { border-left-color: var(--prio-3); }
        .prio-2 { border-left-color: var(--prio-2); }
        .prio-1 { border-left-color: var(--prio-1); }
        .task-done { opacity: .65; }
        .text-done { text-decoration: line-through; color: var(--muted); }

        .btn-gradient { background: linear-gradient(90deg, #4f46e5, #7c3aed); color: #fff; border: 0; font-weight: 600; }
        .btn-gradient:hover { color: #fff; opacity: .92; }
        .category-badge { font-size: .7rem; padding: 4px 10px; border-radius: 30px; background: rgba(129,140,248,.18); color: var(--brand); font-weight: 600; }
        .nav-pills .nav-link { color: var(--muted); border-radius: 30px; font-weight: 600; font-size: .85rem; }
        .nav-pills .nav-link.active { background: var(--brand); color: #fff; }
        :focus-visible { outline: 3px solid rgba(124,58,237,.5); outline-offset: 2px; }
        @media (prefers-reduced-motion: reduce) { * { transition: none !important; } }
    </style>
</head>
<body>

@php
    use Carbon\Carbon;
    $total     = $tasks->count();
    $done      = $tasks->where('is_completed', true)->count();
    $active    = $total - $done;
    $overdue   = $tasks->filter(fn($t) => !$t->is_completed && $t->deadline && Carbon::parse($t->deadline)->isPast() && !Carbon::parse($t->deadline)->isToday())->count();
    $percent   = $total ? round($done / $total * 100) : 0;
    $dueToday  = $tasks->filter(fn($t) => !$t->is_completed && $t->deadline && Carbon::parse($t->deadline)->isToday())->count();
    $prioLabel = [1 => 'Santai', 2 => 'Normal', 3 => 'Urgent'];
@endphp

<nav class="navbar navbar-dark mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold fs-4" href="{{ route('dashboard') }}"><i class="bi bi-robot me-2"></i>SmartTask AI</a>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('report') }}" class="btn btn-light rounded-pill shadow-sm fw-bold"><i class="bi bi-bar-chart-line me-1"></i>Laporan</a>
            <button id="themeToggle" class="btn btn-light rounded-circle shadow-sm" type="button" aria-label="Ganti tema">
                <i class="bi bi-moon-stars"></i>
            </button>
            <div class="dropdown">
                <button class="btn btn-light dropdown-toggle rounded-pill px-3 shadow-sm fw-bold" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-person-circle me-1"></i> {{ Auth::user()->name }}
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 mt-2">
                    <li>
                        <a class="dropdown-item fw-bold" href="{{ route('profile.edit') }}"><i class="bi bi-person-gear me-2"></i>Profil saya</a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger fw-bold"><i class="bi bi-box-arrow-right me-2"></i>Keluar</button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>

<div class="container pb-5">

    {{-- Notifikasi & error validasi --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger rounded-4 border-0 shadow-sm">
            <ul class="mb-0 small">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    @if($overdue || $dueToday)
        <div class="alert alert-warning rounded-4 border-0 shadow-sm d-flex align-items-center" role="status">
            <i class="bi bi-bell-fill me-2"></i>
            <div>
                @if($dueToday)<strong>{{ $dueToday }}</strong> tugas jatuh tempo hari ini @endif
                @if($dueToday && $overdue) dan @endif
                @if($overdue)<strong>{{ $overdue }}</strong> tugas sudah terlambat @endif.
            </div>
        </div>
    @endif

    {{-- Fokus hari ini --}}
    @if(isset($focus) && $focus->isNotEmpty())
        <div class="card p-4 mb-3" style="border-left: 6px solid var(--brand-2);">
            <h5 class="fw-bold mb-3"><i class="bi bi-bullseye me-2" style="color:var(--brand-2)"></i>Fokus hari ini</h5>
            <ol class="mb-0 ps-3">
                @foreach($focus as $f)
                    @php
                        $fd = $f->deadline ? Carbon::parse($f->deadline) : null;
                        $reason = $fd && $fd->isPast() && !$fd->isToday() ? 'sudah terlambat'
                            : ($fd && $fd->isToday() ? 'jatuh tempo hari ini'
                            : ($fd ? 'tenggat ' . $fd->translatedFormat('d M')
                            : 'prioritas ' . strtolower($prioLabel[$f->priority] ?? 'normal')));
                    @endphp
                    <li class="mb-1"><strong>{{ $f->title }}</strong> <small class="text-muted">· {{ $reason }}</small></li>
                @endforeach
            </ol>
        </div>
    @endif

    {{-- Statistik --}}
    <div class="row g-3 mb-3 text-center">
        <div class="col-6 col-md-3"><div class="card stat-card p-3"><h6 class="text-muted small mb-1">Total</h6><h3 class="fw-bold m-0">{{ $total }}</h3></div></div>
        <div class="col-6 col-md-3"><div class="card stat-card warn p-3"><h6 class="text-muted small mb-1">Berjalan</h6><h3 class="fw-bold m-0 text-warning">{{ $active }}</h3></div></div>
        <div class="col-6 col-md-3"><div class="card stat-card ok p-3"><h6 class="text-muted small mb-1">Selesai</h6><h3 class="fw-bold m-0 text-success">{{ $done }}</h3></div></div>
        <div class="col-6 col-md-3"><div class="card stat-card late p-3"><h6 class="text-muted small mb-1">Terlambat</h6><h3 class="fw-bold m-0 text-danger">{{ $overdue }}</h3></div></div>
    </div>

    {{-- Progres --}}
    <div class="card p-3 mb-4">
        <div class="d-flex justify-content-between small fw-bold mb-2">
            <span>Progres keseluruhan</span><span>{{ $percent }}%</span>
        </div>
        <div class="progress" role="progressbar" aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100">
            <div class="progress-bar" style="width: {{ $percent }}%"></div>
        </div>
    </div>

    <div class="row">
        {{-- Form tugas baru --}}
        <div class="col-lg-4 mb-4">
            <div class="card p-4">
                <h5 class="fw-bold mb-4"><i class="bi bi-patch-plus me-2" style="color:var(--brand)"></i>Buat tugas baru</h5>
                <form action="/tasks" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="title" class="form-label small fw-bold">Judul tugas</label>
                        <input id="title" type="text" name="title" value="{{ old('title') }}" maxlength="120"
                               class="form-control form-control-lg bg-soft border-0 @error('title') is-invalid @enderror"
                               placeholder="Apa rencanamu?" required>
                        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <button type="button" id="aiSuggest" class="btn btn-sm btn-outline-primary rounded-pill mt-2"><i class="bi bi-stars me-1"></i>Saran AI</button>
                        <div id="aiResult" class="form-text small" aria-live="polite"></div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label small fw-bold">Catatan (opsional)</label>
                        <textarea id="description" name="description" rows="2" maxlength="500" class="form-control bg-soft border-0" placeholder="Detail, link, atau hal yang perlu diingat">{{ old('description') }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label for="category" class="form-label small fw-bold">Kategori</label>
                        <select id="category" name="category" class="form-select bg-soft border-0">
                            @foreach(['Kuliah' => '🎓 Kuliah', 'Kerja' => '💼 Kerja', 'Pribadi' => '🏠 Pribadi', 'Lainnya' => '✨ Lainnya'] as $val => $label)
                                <option value="{{ $val }}" @selected(old('category') === $val)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <span class="form-label small fw-bold d-block">Prioritas</span>
                        <div class="d-flex gap-2">
                            <input type="radio" class="btn-check" name="priority" id="low" value="1" @checked(old('priority') == 1)>
                            <label class="btn btn-outline-success btn-sm w-100" for="low">Santai</label>
                            <input type="radio" class="btn-check" name="priority" id="medium" value="2" @checked(old('priority', 2) == 2)>
                            <label class="btn btn-outline-warning btn-sm w-100" for="medium">Normal</label>
                            <input type="radio" class="btn-check" name="priority" id="high" value="3" @checked(old('priority') == 3)>
                            <label class="btn btn-outline-danger btn-sm w-100" for="high">Urgent</label>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="deadline" class="form-label small fw-bold">Tenggat waktu</label>
                        <input id="deadline" type="date" name="deadline" value="{{ old('deadline') }}"
                               min="{{ now()->toDateString() }}" class="form-control bg-soft border-0">
                        <div id="prioHint" class="form-text small"></div>
                    </div>

                    <button type="submit" class="btn btn-gradient btn-lg w-100 shadow">Simpan tugas <i class="bi bi-stars ms-2"></i></button>
                </form>
            </div>
        </div>

        {{-- Daftar tugas --}}
        <div class="col-lg-8">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <ul class="nav nav-pills" id="filterTabs">
                    <li class="nav-item"><button class="nav-link active" data-filter="all" type="button">Semua</button></li>
                    <li class="nav-item"><button class="nav-link" data-filter="active" type="button">Aktif</button></li>
                    <li class="nav-item"><button class="nav-link" data-filter="done" type="button">Selesai</button></li>
                </ul>
                <div class="input-group" style="max-width: 280px;">
                    <span class="input-group-text bg-soft border-0"><i class="bi bi-search"></i></span>
                    <input id="searchInput" type="search" class="form-control bg-soft border-0" placeholder="Cari tugas..." aria-label="Cari tugas">
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
                <select id="sortSelect" class="form-select form-select-sm bg-soft border-0 w-auto" aria-label="Urutkan tugas">
                    <option value="default">Urutan bawaan</option>
                    <option value="priority">Prioritas tertinggi</option>
                    <option value="deadline">Tenggat terdekat</option>
                    <option value="newest">Terbaru dibuat</option>
                </select>
                @if($done > 0)
                    <form action="/tasks-completed" method="POST" onsubmit="return confirm('Hapus semua {{ $done }} tugas yang sudah selesai?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger rounded-pill"><i class="bi bi-trash3 me-1"></i>Hapus yang selesai ({{ $done }})</button>
                    </form>
                @endif
            </div>

            <div id="taskList">
                @foreach($tasks as $task)
                    @php
                        $deadline  = $task->deadline ? Carbon::parse($task->deadline) : null;
                        $isToday   = $deadline?->isToday();
                        $isLate    = $deadline && $deadline->isPast() && !$isToday && !$task->is_completed;
                    @endphp
                    <div class="task-item p-3 prio-{{ $task->priority }} {{ $task->is_completed ? 'task-done' : '' }}"
                         data-status="{{ $task->is_completed ? 'done' : 'active' }}"
                         data-index="{{ $loop->index }}"
                         data-prio="{{ $task->priority }}"
                         data-deadline="{{ $deadline?->toDateString() }}"
                         data-created="{{ $task->created_at->toDateTimeString() }}"
                         data-title="{{ Str::lower($task->title) }}">
                        <div class="row align-items-center g-2">
                            <div class="col-auto">
                                <form action="/tasks/{{ $task->id }}/toggle" method="POST">
                                    @csrf @method('PATCH')
                                    @if(!$task->is_completed)
                                        <button class="btn btn-outline-primary btn-sm rounded-circle" title="Tandai selesai" aria-label="Tandai selesai"><i class="bi bi-check-lg"></i></button>
                                    @else
                                        <button class="btn btn-link p-0 border-0 text-success fs-4" title="Batalkan selesai" aria-label="Batalkan selesai"><i class="bi bi-check-circle-fill"></i></button>
                                    @endif
                                </form>
                            </div>
                            <div class="col">
                                <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                    <span class="category-badge">{{ $task->category ?? 'Umum' }}</span>
                                    <span class="badge rounded-pill text-bg-{{ [1 => 'success', 2 => 'warning', 3 => 'danger'][$task->priority] ?? 'secondary' }}" style="font-size:.65rem;">
                                        {{ $prioLabel[$task->priority] ?? 'Normal' }}
                                    </span>
                                    @if($isToday && !$task->is_completed)<span class="badge bg-danger rounded-pill" style="font-size:.65rem;">Hari ini</span>@endif
                                    @if($isLate)<span class="badge bg-dark rounded-pill" style="font-size:.65rem;">Terlambat</span>@endif
                                </div>
                                <h6 class="mb-1 {{ $task->is_completed ? 'text-done' : 'fw-bold' }}">{{ $task->title }}</h6>
                                @if($task->description)<p class="small text-muted mb-1">{{ Str::limit($task->description, 140) }}</p>@endif
                                <div class="d-flex flex-wrap gap-3">
                                    <small class="text-muted"><i class="bi bi-calendar-event me-1"></i>Dibuat {{ $task->created_at->translatedFormat('d M') }}</small>
                                    @if($deadline)
                                        <small class="{{ $isLate ? 'text-danger fw-bold' : 'text-muted' }}">
                                            <i class="bi bi-alarm me-1"></i>{{ $deadline->translatedFormat('d M Y') }}
                                            @if(!$task->is_completed && !$isLate) · {{ $deadline->diffForHumans() }} @endif
                                        </small>
                                    @endif
                                </div>
                            </div>
                            <div class="col-auto d-flex align-items-center gap-3">
                                <button type="button" class="btn btn-link p-0 border-0 text-secondary btn-edit" title="Edit" aria-label="Edit tugas"
                                        data-id="{{ $task->id }}" data-title="{{ $task->title }}" data-category="{{ $task->category }}"
                                        data-priority="{{ $task->priority }}" data-deadline="{{ $deadline?->toDateString() }}" data-description="{{ $task->description }}">
                                    <i class="bi bi-pencil-square fs-5"></i>
                                </button>
                                <form action="/tasks/{{ $task->id }}" method="POST" onsubmit="return confirm('Hapus permanen tugas ini?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-link text-danger p-0 border-0" title="Hapus" aria-label="Hapus tugas"><i class="bi bi-trash3 fs-5"></i></button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Tampil jika tidak ada tugas / hasil filter kosong --}}
            <div id="emptyState" class="card p-5 text-center {{ $total ? 'd-none' : '' }}">
                <i class="bi bi-inbox fs-1 text-muted mb-2"></i>
                <h6 class="fw-bold mb-1" id="emptyTitle">{{ $total ? 'Tidak ada tugas yang cocok' : 'Belum ada tugas' }}</h6>
                <p class="text-muted small mb-0" id="emptyText">{{ $total ? 'Coba ubah kata kunci atau filter.' : 'Isi form di samping untuk menambahkan tugas pertamamu.' }}</p>
            </div>
        </div>
    </div>
</div>

{{-- Modal edit tugas --}}
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="editForm" method="POST" class="modal-content border-0 rounded-4">
            @csrf @method('PUT')
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold" id="editModalLabel">Edit tugas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="editTitle" class="form-label small fw-bold">Judul tugas</label>
                    <input id="editTitle" name="title" maxlength="120" class="form-control bg-soft border-0" required>
                </div>
                <div class="mb-3">
                    <label for="editDescription" class="form-label small fw-bold">Catatan</label>
                    <textarea id="editDescription" name="description" rows="2" maxlength="500" class="form-control bg-soft border-0"></textarea>
                </div>
                <div class="row g-3">
                    <div class="col-6">
                        <label for="editCategory" class="form-label small fw-bold">Kategori</label>
                        <select id="editCategory" name="category" class="form-select bg-soft border-0">
                            @foreach(['Kuliah', 'Kerja', 'Pribadi', 'Lainnya'] as $c)<option value="{{ $c }}">{{ $c }}</option>@endforeach
                        </select>
                    </div>
                    <div class="col-6">
                        <label for="editPriority" class="form-label small fw-bold">Prioritas</label>
                        <select id="editPriority" name="priority" class="form-select bg-soft border-0">
                            <option value="1">Santai</option><option value="2">Normal</option><option value="3">Urgent</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label for="editDeadline" class="form-label small fw-bold">Tenggat waktu</label>
                        <input id="editDeadline" type="date" name="deadline" class="form-control bg-soft border-0">
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-gradient rounded-pill px-4">Simpan perubahan</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // --- Saran prioritas dari tenggat (berhenti jika prioritas dipilih manual) ---
    const dl = document.getElementById('deadline');
    const hint = document.getElementById('prioHint');
    const prioRadios = document.querySelectorAll('input[name="priority"]');
    let manualPrio = false;
    prioRadios.forEach(r => r.addEventListener('click', () => { manualPrio = true; hint.textContent = ''; }));
    dl.addEventListener('change', () => {
        if (!dl.value || manualPrio) return;
        const today = new Date(); today.setHours(0, 0, 0, 0);
        const days = Math.round((new Date(dl.value + 'T00:00:00') - today) / 86400000);
        const level = days <= 1 ? 3 : days <= 7 ? 2 : 1;
        const name = { 1: 'Santai', 2: 'Normal', 3: 'Urgent' }[level];
        document.querySelector('input[name="priority"][value="' + level + '"]').checked = true;
        hint.textContent = 'Disarankan: ' + name + (days <= 0 ? ' (tenggat hari ini)' : ' (tenggat ' + days + ' hari lagi)');
    });

    // --- Saran AI: isi kategori & prioritas dari judul ---
    const aiBtn = document.getElementById('aiSuggest');
    const aiOut = document.getElementById('aiResult');
    aiBtn.addEventListener('click', async () => {
        const title = document.getElementById('title').value.trim();
        if (!title) { aiOut.textContent = 'Isi judul tugas dulu.'; return; }
        aiBtn.disabled = true;
        aiOut.textContent = 'Menganalisis...';
        try {
            const res = await fetch('/tasks/suggest', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ title, deadline: dl.value || null })
            });
            if (!res.ok) throw new Error();
            const s = await res.json();
            document.getElementById('category').value = s.category;
            document.querySelector('input[name="priority"][value="' + s.priority + '"]').checked = true;
            manualPrio = true;
            hint.textContent = '';
            aiOut.textContent = (s.source === 'ai' ? 'Saran AI: ' : 'Saran otomatis: ') + (s.reason || '');
        } catch (e) {
            aiOut.textContent = 'Saran gagal dimuat. Coba lagi.';
        }
        aiBtn.disabled = false;
    });

    // --- Isi modal edit dari data tugas ---
    const editModal = new bootstrap.Modal(document.getElementById('editModal'));
    document.querySelectorAll('.btn-edit').forEach(btn => {
        btn.addEventListener('click', () => {
            const d = btn.dataset;
            document.getElementById('editForm').action = '/tasks/' + d.id;
            document.getElementById('editTitle').value = d.title;
            document.getElementById('editDescription').value = d.description || '';
            document.getElementById('editCategory').value = d.category || 'Lainnya';
            document.getElementById('editPriority').value = d.priority || '2';
            document.getElementById('editDeadline').value = d.deadline || '';
            editModal.show();
        });
    });
</script>
<script>
    // --- Tema gelap/terang (tersimpan di browser) ---
    const root = document.documentElement;
    const themeBtn = document.getElementById('themeToggle');
    const setTheme = (t) => {
        root.setAttribute('data-bs-theme', t);
        themeBtn.innerHTML = t === 'dark' ? '<i class="bi bi-sun"></i>' : '<i class="bi bi-moon-stars"></i>';
        try { localStorage.setItem('theme', t); } catch (e) {}
    };
    let saved = 'light';
    try { saved = localStorage.getItem('theme') || (matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'); } catch (e) {}
    setTheme(saved);
    themeBtn.addEventListener('click', () => setTheme(root.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark'));

    // --- Pencarian + filter ---
    const items = [...document.querySelectorAll('#taskList .task-item')];
    const search = document.getElementById('searchInput');
    const empty = document.getElementById('emptyState');
    let filter = 'all';

    function applyFilters() {
        const q = search.value.trim().toLowerCase();
        let visible = 0;
        items.forEach(el => {
            const ok = (filter === 'all' || el.dataset.status === filter) && el.dataset.title.includes(q);
            el.classList.toggle('d-none', !ok);
            if (ok) visible++;
        });
        if (items.length) empty.classList.toggle('d-none', visible > 0);
    }
    const list = document.getElementById('taskList');
    const sorters = {
        default:  (a, b) => a.dataset.index - b.dataset.index,
        priority: (a, b) => b.dataset.prio - a.dataset.prio,
        deadline: (a, b) => (a.dataset.deadline || '9999').localeCompare(b.dataset.deadline || '9999'),
        newest:   (a, b) => b.dataset.created.localeCompare(a.dataset.created),
    };
    document.getElementById('sortSelect').addEventListener('change', e => {
        [...items].sort(sorters[e.target.value]).forEach(el => list.appendChild(el));
    });
    search.addEventListener('input', applyFilters);
    document.querySelectorAll('#filterTabs [data-filter]').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('#filterTabs .nav-link').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            filter = btn.dataset.filter;
            applyFilters();
        });
    });
</script>
</body>
</html>