<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Masuk') - To Do List</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>[x-cloak]{display:none!important}</style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-stone-100 font-sans text-stone-800 antialiased">

<div class="grid min-h-screen lg:grid-cols-2">

    {{-- Panel kiri (desktop) --}}
    <aside class="relative hidden overflow-hidden bg-gradient-to-br from-emerald-700 to-emerald-900 p-12 text-white lg:flex lg:flex-col lg:justify-between">
        <div class="absolute -right-24 -top-24 h-80 w-80 rounded-full bg-white/10"></div>
        <div class="absolute -bottom-32 left-20 h-72 w-72 rounded-full bg-amber-300/15"></div>

        <div class="relative flex items-center gap-3 text-xl font-bold">
            <span class="grid h-11 w-11 place-items-center rounded-xl bg-white/15"><i class="bi bi-check2-square"></i></span>
            To Do List
        </div>

        <div class="relative max-w-md">
            <h2 class="mb-4 text-4xl font-bold leading-tight">Catat, kerjakan, dan selesaikan tugas harian.</h2>
            <p class="mb-8 text-white/80">Satu tempat untuk merencanakan pekerjaan setiap hari, lengkap dengan progres yang jelas.</p>

            <ul class="space-y-4 text-sm">
                <li class="flex items-center gap-3"><span class="grid h-9 w-9 place-items-center rounded-lg bg-white/15"><i class="bi bi-list-check"></i></span>Daftar tugas harian yang terstruktur</li>
                <li class="flex items-center gap-3"><span class="grid h-9 w-9 place-items-center rounded-lg bg-white/15"><i class="bi bi-graph-up-arrow"></i></span>Progres dan laporan produktivitas</li>
                <li class="flex items-center gap-3"><span class="grid h-9 w-9 place-items-center rounded-lg bg-white/15"><i class="bi bi-shield-check"></i></span>Aktivitas terpantau oleh administrator</li>
            </ul>
        </div>

        <p class="relative text-xs text-white/50">&copy; {{ date('Y') }} To Do List</p>
    </aside>

    {{-- Panel form --}}
    <main class="flex items-center justify-center px-5 py-10">
        <div class="w-full max-w-md">
            <div class="mb-8 flex items-center justify-center gap-2 text-lg font-bold text-emerald-800 lg:hidden">
                <span class="grid h-10 w-10 place-items-center rounded-xl bg-emerald-700 text-white"><i class="bi bi-check2-square"></i></span>
                To Do List
            </div>

            @yield('content')
        </div>
    </main>
</div>

@stack('scripts')
</body>
</html>