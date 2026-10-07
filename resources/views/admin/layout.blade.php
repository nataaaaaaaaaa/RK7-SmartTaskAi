<!DOCTYPE html>
<html lang="id" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin') - To Do List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('css/todo.css') }}" rel="stylesheet">
    <script>try{document.documentElement.setAttribute('data-bs-theme', localStorage.getItem('theme')||'light')}catch(e){}</script>
</head>
<body>
<header class="topbar mb-4">
    <div class="container d-flex flex-wrap justify-content-between align-items-center py-3 gap-2">
        <a class="brand fs-5" href="{{ route('admin.dashboard') }}">
            <i class="bi bi-check2-square me-2"></i>To Do List
            <span class="badge text-bg-secondary rounded-1 ms-2" style="font-size:.65rem;">Admin</span>
        </a>

        <ul class="nav nav-pills">
            <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">Ringkasan</a></li>
            <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.users*') ? 'active' : '' }}" href="{{ route('admin.users') }}">Karyawan</a></li>
            <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.tasks*') ? 'active' : '' }}" href="{{ route('admin.tasks') }}">Semua Tugas</a></li>
        </ul>

        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('admin.export') }}" class="btn btn-outline-brand btn-sm"><i class="bi bi-download me-1"></i>Ekspor CSV</a>
            <a href="{{ route('dashboard') }}" class="btn btn-outline-brand btn-sm"><i class="bi bi-arrow-left me-1"></i>Ke aplikasi</a>
            <button id="themeToggle" class="btn btn-light btn-sm" type="button" aria-label="Ganti tema"><i class="bi bi-moon-stars"></i></button>
        </div>
    </div>
</header>

<main class="container pb-5">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0 small">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    @yield('content')
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const root = document.documentElement;
    const themeBtn = document.getElementById('themeToggle');
    const setTheme = (t) => {
        root.setAttribute('data-bs-theme', t);
        themeBtn.innerHTML = t === 'dark' ? '<i class="bi bi-sun"></i>' : '<i class="bi bi-moon-stars"></i>';
        try { localStorage.setItem('theme', t); } catch (e) {}
    };
    setTheme(root.getAttribute('data-bs-theme'));
    themeBtn.addEventListener('click', () => setTheme(root.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark'));
</script>
</body>
</html>