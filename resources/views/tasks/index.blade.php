<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartTask AI - Pro Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
        body { 
            background-color: #f0f2f5; 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            color: #1e293b;
        }
        .navbar { 
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); 
            padding: 1rem 0;
            box-shadow: 0 4px 20px rgba(79, 70, 229, 0.2);
        }
        .card { 
            border: none; 
            border-radius: 20px; 
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05);
        }
        .stat-card {
            background: #fff;
            border-left: 6px solid #4f46e5;
            transition: 0.3s;
        }
        .stat-card:hover { transform: translateY(-5px); }
        
        .task-item {
            background: #fff;
            border-radius: 15px;
            margin-bottom: 15px;
            transition: 0.2s;
            border-left: 6px solid #e2e8f0;
        }
        .task-item:hover { box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        
        /* Prioritas Colors */
        .prio-3 { border-left-color: #ef4444; } /* High */
        .prio-2 { border-left-color: #f59e0b; } /* Medium */
        .prio-1 { border-left-color: #10b981; } /* Low */
        
        .task-done { background: #f8fafc; opacity: 0.7; }
        .text-done { text-decoration: line-through; color: #94a3b8; }
        
        .btn-gradient {
            background: linear-gradient(90deg, #4f46e5 0%, #7c3aed 100%);
            color: white; border: none; font-weight: 600;
        }
        .btn-gradient:hover { color: white; opacity: 0.9; }
        
        .category-badge {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 5px 10px;
            border-radius: 30px;
            background: rgba(79, 70, 229, 0.1);
            color: #4f46e5;
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold fs-4" href="#"><i class="bi bi-robot me-2"></i>SmartTask AI</a>
            <div class="dropdown">
                <button class="btn btn-light dropdown-toggle rounded-pill px-4 shadow-sm fw-bold" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-person-circle me-1"></i> {{ Auth::user()->name }}
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 mt-2">
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger fw-bold"><i class="bi bi-box-arrow-right me-2"></i>Logout</button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container pb-5">
        <div class="row mb-4 text-center">
            <div class="col-md-4 mb-3">
                <div class="card stat-card p-3 border-primary">
                    <h6 class="text-muted small">Total Pekerjaan</h6>
                    <h3 class="fw-bold m-0">{{ $tasks->count() }}</h3>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card stat-card p-3 border-warning">
                    <h6 class="text-muted small">Sedang Berjalan</h6>
                    <h3 class="fw-bold m-0 text-warning">{{ $tasks->where('is_completed', false)->count() }}</h3>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card stat-card p-3 border-success">
                    <h6 class="text-muted small">Tugas Selesai</h6>
                    <h3 class="fw-bold m-0 text-success">{{ $tasks->where('is_completed', true)->count() }}</h3>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-4 mb-4">
                <div class="card p-4 shadow-sm">
                    <h5 class="fw-bold mb-4"><i class="bi bi-patch-plus me-2 text-primary"></i>Buat Tugas Baru</h5>
                    <form action="/tasks" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Judul Tugas</label>
                            <input type="text" name="title" class="form-control form-control-lg bg-light border-0" placeholder="Apa rencanamu?" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Kategori</label>
                            <select name="category" class="form-select bg-light border-0">
                                <option value="Kuliah">🎓 Kuliah</option>
                                <option value="Kerja">💼 Kerja</option>
                                <option value="Pribadi">🏠 Pribadi</option>
                                <option value="Lainnya">✨ Lainnya</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Prioritas</label>
                            <div class="d-flex gap-2">
                                <input type="radio" class="btn-check" name="priority" id="low" value="1">
                                <label class="btn btn-outline-success btn-sm w-100" for="low">Santai</label>
                                
                                <input type="radio" class="btn-check" name="priority" id="medium" value="2" checked>
                                <label class="btn btn-outline-warning btn-sm w-100" for="medium">Normal</label>
                                
                                <input type="radio" class="btn-check" name="priority" id="high" value="3">
                                <label class="btn btn-outline-danger btn-sm w-100" for="high">Urgent</label>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label small fw-bold">Tenggat Waktu (Deadline)</label>
                            <input type="date" name="deadline" class="form-control bg-light border-0">
                        </div>

                        <button type="submit" class="btn btn-gradient btn-lg w-100 shadow">Simpan ke AI <i class="bi bi-stars ms-2"></i></button>
                    </form>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold m-0"><i class="bi bi-stack me-2"></i>Tugas Aktif</h5>
                    <div class="input-group w-50">
                        <span class="input-group-text bg-white border-0 shadow-sm"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control border-0 shadow-sm rounded-end" placeholder="Cari tugas...">
                    </div>
                </div>

                @forelse($tasks as $task)
                <div class="task-item p-3 {{ $task->is_completed ? 'task-done' : '' }} prio-{{ $task->priority }}">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            @if(!$task->is_completed)
                            <form action="/tasks/{{ $task->id }}/complete" method="POST">
                                @csrf @method('PATCH')
                                <button class="btn btn-outline-primary btn-sm rounded-circle"><i class="bi bi-check-lg"></i></button>
                            </form>
                            @else
                            <i class="bi bi-check-circle-fill text-success fs-4"></i>
                            @endif
                        </div>
                        <div class="col">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="category-badge">{{ $task->category ?? 'Umum' }}</span>
                                @if($task->deadline && \Carbon\Carbon::parse($task->deadline)->isToday())
                                    <span class="badge bg-danger rounded-pill" style="font-size: 0.6rem;">Hari Ini!</span>
                                @endif
                            </div>
                            <h6 class="mb-1 {{ $task->is_completed ? 'text-done' : 'fw-bold' }}">{{ $task->title }}</h6>
                            <div class="d-flex gap-3">
                                <small class="text-muted"><i class="bi bi-calendar-event me-1"></i> {{ $task->created_at->format('d M') }}</small>
                                @if($task->deadline)
                                <small class="{{ \Carbon\Carbon::parse($task->deadline)->isPast() ? 'text-danger fw-bold' : 'text-muted' }}">
                                    <i class="bi bi-alarm me-1"></i> {{ \Carbon\Carbon::parse($task->deadline)->format('d M Y') }}
                                </small>
                                @endif
                            </div>
                        </div>
                        <div class="col-auto text-end">
                            <form action="/tasks/{{ $task->id }}" method="POST" onsubmit="return confirm('Hapus permanen tugas ini?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-link text-danger p-0 border-0"><i class="bi bi-trash3 fs-5"></i></button>
                            </form>
                        </div>
                    </div>
                </div>
                @empty
                <div class="card p-5 text-center shadow-sm">
                    <img src="https://illustrations.popsy.co/blue/web-design.svg" class="mx-auto mb-4" style="width: 180px;">
                    <h6 class="text-muted">Semua tugas sudah beres! Silakan santai sejenak.</h6>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>