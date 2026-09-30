<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password - SmartTask AI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { min-height: 100vh; display: flex; align-items: center; background: linear-gradient(135deg, #4f46e5, #7c3aed); font-family: 'Plus Jakarta Sans', sans-serif; }
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
        <h2 class="fw-bold"><i class="bi bi-key me-2"></i>Lupa Password</h2>
        <p class="mb-0 opacity-75">Kami kirim link untuk membuat password baru</p>
    </div>

    <div class="card p-4">
        @if (session('status'))
            <div class="alert alert-success small rounded-4 border-0">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger small rounded-4 border-0">
                <ul class="mb-0 ps-3">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
            @csrf
            <div class="mb-4">
                <label for="email" class="form-label small fw-bold">Email terdaftar</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" class="form-control form-control-lg" required autofocus>
            </div>
            <button type="submit" class="btn btn-gradient btn-lg w-100 shadow">Kirim link reset</button>
        </form>

        <p class="text-center small mt-4 mb-0">
            <a href="{{ route('login') }}" class="fw-bold text-decoration-none"><i class="bi bi-arrow-left me-1"></i>Kembali ke login</a>
        </p>
    </div>
</div>
</body>
</html>