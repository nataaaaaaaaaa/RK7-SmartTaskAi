<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') - Panel Admin</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>[x-cloak]{display:none!important}</style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-stone-100 font-sans text-stone-800 antialiased">

@php
    $nav = [
        ['label' => 'Ringkasan',   'short' => 'Ringkasan', 'icon' => 'bi-speedometer2',   'route' => 'admin.dashboard', 'match' => 'admin.dashboard'],
        ['label' => 'Aktivitas harian', 'short' => 'Aktivitas', 'icon' => 'bi-activity', 'route' => 'admin.activity', 'match' => 'admin.activity'],
        ['label' => 'Karyawan',    'short' => 'Karyawan',  'icon' => 'bi-people',         'route' => 'admin.users',     'match' => 'admin.users*'],
        ['label' => 'Semua tugas', 'short' => 'Tugas',     'icon' => 'bi-card-checklist', 'route' => 'admin.tasks',     'match' => 'admin.tasks*'],
    ];
@endphp

{{-- Sidebar (desktop) --}}
<aside class="fixed inset-y-0 left-0 z-30 hidden w-64 flex-col bg-emerald-900 p-4 text-white lg:flex">
    <a href="{{ route('admin.dashboard') }}" class="mb-6 flex items-center gap-3 px-2 text-lg font-bold">
       <span class="grid h-10 w-10 place-items-center rounded-xl bg-white/15"><i class="bi bi-shield-lock"></i></span>
        <span>Panel Admin<span class="block text-[11px] font-medium text-white/50">To Do List</span></span>
    </a>

    <p class="px-3 pb-2 text-[11px] font-semibold uppercase tracking-widest text-white/40">Pantauan</p>
    <nav class="space-y-1">
        @foreach($nav as $item)
            <a href="{{ route($item['route']) }}"
               class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-medium transition {{ request()->routeIs($item['match']) ? 'bg-white/10 text-white' : 'text-white/60 hover:bg-white/10 hover:text-white' }}">
                <i class="bi {{ $item['icon'] }} text-lg"></i>{{ $item['label'] }}
            </a>
        @endforeach
    </nav>

    <p class="px-3 pb-2 pt-6 text-[11px] font-semibold uppercase tracking-widest text-white/40">Aksi</p>
    <nav class="space-y-1">
        <a href="{{ route('admin.export') }}" class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-medium text-white/60 transition hover:bg-white/10 hover:text-white">
            <i class="bi bi-download text-lg"></i>Ekspor CSV
        </a>
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-medium text-white/60 transition hover:bg-white/10 hover:text-white">
            <i class="bi bi-arrow-left-circle text-lg"></i>Kembali ke aplikasi
        </a>
    </nav>

    <div class="mt-auto">
        <div class="mb-2 flex items-center gap-3 rounded-2xl bg-white/10 p-3">
           <div class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-amber-400 font-bold text-amber-950">
                {{ Str::upper(Str::substr(Auth::user()->name, 0, 1)) }}
            </div>
            <div class="min-w-0">
                <p class="truncate text-sm font-semibold">{{ Auth::user()->name }}</p>
                <p class="text-xs text-white/50">Administrator</p>
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="flex w-full items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-medium text-white/60 transition hover:bg-white/10 hover:text-white">
                <i class="bi bi-box-arrow-right text-lg"></i>Keluar
            </button>
        </form>
    </div>
</aside>

<div class="min-h-screen pb-24 lg:pb-0 lg:pl-64">

    {{-- Top bar --}}
    <header class="sticky top-0 z-20 flex items-center justify-between border-b border-stone-200 bg-stone-100/90 px-4 py-3 backdrop-blur lg:px-8">
        <div>
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 font-bold text-stone-800 lg:hidden">
                <i class="bi bi-shield-lock text-emerald-700"></i>Panel Admin
            </a>
            <h1 class="hidden text-lg font-bold lg:block">@yield('heading', 'Panel Admin')</h1>
        </div>

        <div class="flex items-center gap-3">
            <span class="hidden text-sm text-stone-500 lg:inline">{{ now()->translatedFormat('l, d F Y') }}</span>

            <div x-data="{ open: false }" class="relative lg:hidden">
                <button type="button" @click="open = !open" aria-label="Menu akun"
                        class="grid h-9 w-9 place-items-center rounded-full bg-stone-800 text-sm font-bold text-white">
                    {{ Str::upper(Str::substr(Auth::user()->name, 0, 1)) }}
                </button>
                <div x-show="open" x-cloak @click.outside="open = false"
                     class="absolute right-0 mt-2 w-52 rounded-xl bg-white py-1 text-sm shadow-lg ring-1 ring-black/5">
                    <p class="truncate px-4 py-2 text-xs text-stone-500">{{ Auth::user()->name }}</p>
                    <a href="{{ route('admin.export') }}" class="block px-4 py-2 hover:bg-stone-50">Ekspor CSV</a>
                    <a href="{{ route('dashboard') }}" class="block px-4 py-2 hover:bg-stone-50">Kembali ke aplikasi</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="block w-full px-4 py-2 text-left text-red-600 hover:bg-stone-50">Keluar</button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-6xl px-4 py-6 lg:px-8">
        @if(session('success'))
            <div x-data="{ show: true }" x-show="show" class="mb-4 flex items-center justify-between rounded-xl bg-emerald-50 px-4 py-3 text-sm text-emerald-800 ring-1 ring-emerald-200" role="status">
                <span><i class="bi bi-check-circle mr-2"></i>{{ session('success') }}</span>
                <button type="button" @click="show = false" class="text-emerald-700" aria-label="Tutup"><i class="bi bi-x-lg"></i></button>
            </div>
        @endif
        @if($errors->any())
            <div class="mb-4 rounded-xl bg-red-50 px-4 py-3 text-sm text-red-700 ring-1 ring-red-200">
                <ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        @yield('content')
    </main>
</div>

{{-- Bottom navigation (ponsel) --}}
<nav class="fixed inset-x-0 bottom-0 z-40 flex border-t border-stone-200 bg-white pb-[env(safe-area-inset-bottom)] shadow-[0_-6px_20px_rgba(0,0,0,0.05)] lg:hidden" aria-label="Navigasi admin">
    @foreach($nav as $item)
        <a href="{{ route($item['route']) }}"
           class="flex flex-1 flex-col items-center gap-0.5 py-2 text-[11px] font-semibold {{ request()->routeIs($item['match']) ? 'text-emerald-700' : 'text-stone-500' }}">
            <i class="bi {{ $item['icon'] }} text-xl"></i>{{ $item['short'] }}
        </a>
    @endforeach
    <a href="{{ route('dashboard') }}" class="flex flex-1 flex-col items-center gap-0.5 py-2 text-[11px] font-semibold text-stone-500">
        <i class="bi bi-arrow-left-circle text-xl"></i>Aplikasi
    </a>
</nav>

@stack('scripts')
</body>
</html>