<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - SmartTask AI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { min-height: 100vh; display: flex; align-items: center; background: linear-gradient(135deg, #4f46e5, #7c3aed); font-family: 'Plus Jakarta Sans', sans-serif; padding: 24px 0; }
        .card { border: none; border-radius: 24px; box-shadow: 0 20px 40px rgba(0,0,0,.2); }
        .form-control { background: #f1f5f9; border: 0; }
        .btn-gradient { background: linear-gradient(90deg, #4f46e5, #7c3aed); color: #fff; border: 0; font-weight: 600; }
        .btn-gradient:hover { color: #fff; opacity: .92; }
        a { color: #4f46e5; }
    </style>
</head>
<body>
<div class="container" style="max-width: 440px;">
    <div class="text-center text-white mb-4">
        <h2 class="fw-bold"><i class="bi bi-robot me-2"></i>SmartTask AI</h2>
        <p class="mb-0 opacity-75">Buat akun untuk mulai mengatur tugasmu</p>
    </div>

    <div class="card p-4">
        @if ($errors->any())
            <div class="alert alert-danger small rounded-4 border-0">
                <ul class="mb-0 ps-3">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <form method="POST" action="{{ route('register') }}">
            @csrf
            <div class="mb-3">
                <label for="name" class="form-label small fw-bold">Nama lengkap</label>
                <input id="name" type="text" name="name" value="{{ old('name') }}" class="form-control form-control-lg" required autofocus autocomplete="name">
            </div>

            <div class="mb-3">
                <label for="email" class="form-label small fw-bold">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" class="form-control form-control-lg" required autocomplete="username">
            </div>

            <div class="mb-3">
                <label for="password" class="form-label small fw-bold">Password</label>
                <input id="password" type="password" name="password" class="form-control form-control-lg" required autocomplete="new-password">
                <div class="form-text small">Minimal 8 karakter.</div>
            </div>

            <div class="mb-4">
                <label for="password_confirmation" class="form-label small fw-bold">Ulangi password</label>
                <input id="password_confirmation" type="password" name="password_confirmation" class="form-control form-control-lg" required autocomplete="new-password">
            </div>

            <button type="submit" class="btn btn-gradient btn-lg w-100 shadow">Daftar</button>
        </form>

        <p class="text-center small text-muted mt-4 mb-0">
            Sudah punya akun? <a href="{{ route('login') }}" class="fw-bold text-decoration-none">Masuk</a>
        </p>
    </div>
</div>
</body>
</html>