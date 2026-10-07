<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'To Do List') - To Do List</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        [x-cloak] {
            display: none !important
        }
    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-stone-100 font-sans text-stone-800 antialiased">

    @php
    $nav = [
    ['label' => 'Kegiatan harian', 'short' => 'Kegiatan', 'icon' => 'bi-camera', 'route' => 'dashboard', 'match' => 'dashboard'],
    ['label' => 'Laporan', 'short' => 'Laporan', 'icon' => 'bi-bar-chart-line', 'route' => 'report', 'match' => 'report*'],
    ['label' => 'Profil', 'short' => 'Profil', 'icon' => 'bi-person', 'route' => 'profile.edit', 'match' => 'profile.*'],
    ];
    if (Auth::user()->isAdmin()) {
    $nav[] = ['label' => 'Panel admin', 'short' => 'Admin', 'icon' => 'bi-shield-lock', 'route' => 'admin.dashboard', 'match' => 'admin.*'];
    }
    @endphp

    {{-- Sidebar (desktop) --}}
    <aside class="fixed inset-y-0 left-0 z-30 hidden w-64 flex-col bg-emerald-900 p-4 text-white lg:flex print:hidden">
        <a href="{{ route('dashboard') }}" class="mb-6 flex items-center gap-3 px-2 text-lg font-bold">
            <span class="grid h-10 w-10 place-items-center rounded-xl bg-white/15"><i class="bi bi-check2-square"></i></span>
            To Do List
        </a>

        <p class="px-3 pb-2 text-[11px] font-semibold uppercase tracking-widest text-white/40">Menu</p>
        <nav class="space-y-1">
            @foreach($nav as $item)
            <a href="{{ route($item['route']) }}"
                class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-medium transition {{ request()->routeIs($item['match']) ? 'bg-white/15 text-white' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">
                <i class="bi {{ $item['icon'] }} text-lg"></i>{{ $item['label'] }}
            </a>
            @endforeach
        </nav>

        <div class="mt-auto">
            <div class="mb-2 flex items-center gap-3 rounded-2xl bg-white/10 p-3">
                <div class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-amber-400 font-bold text-amber-950">
                    {{ Str::upper(Str::substr(Auth::user()->name, 0, 1)) }}
                </div>
                <div class="min-w-0">
                    <p class="truncate text-sm font-semibold">{{ Auth::user()->name }}</p>
                    <p class="text-xs text-white/60">{{ Auth::user()->isAdmin() ? 'Administrator' : 'Karyawan' }}</p>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="flex w-full items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-medium text-white/70 transition hover:bg-white/10 hover:text-white">
                    <i class="bi bi-box-arrow-right text-lg"></i>Keluar
                </button>
            </form>
        </div>
    </aside>

    <div class="min-h-screen pb-24 lg:pb-0 lg:pl-64 print:pb-0 print:pl-0">

        {{-- Top bar --}}
        <header class="sticky top-0 z-20 flex items-center justify-between border-b border-stone-200 bg-stone-100/90 px-4 py-3 backdrop-blur lg:px-8 print:hidden">
            <div>
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2 font-bold text-emerald-800 lg:hidden">
                    <i class="bi bi-check2-square"></i>To Do List
                </a>
                <h1 class="hidden text-lg font-bold lg:block">@yield('heading', 'To Do List')</h1>
            </div>

            <div class="flex items-center gap-3">
                <span class="hidden text-sm text-stone-500 lg:inline">{{ now()->translatedFormat('l, d F Y') }}</span>

                {{-- Menu akun (ponsel) --}}
                <div x-data="{ open: false }" class="relative lg:hidden">
                    <button type="button" @click="open = !open" aria-label="Menu akun"
                        class="grid h-9 w-9 place-items-center rounded-full bg-emerald-700 text-sm font-bold text-white">
                        {{ Str::upper(Str::substr(Auth::user()->name, 0, 1)) }}
                    </button>
                    <div x-show="open" x-cloak @click.outside="open = false"
                        class="absolute right-0 mt-2 w-52 rounded-xl bg-white py-1 text-sm shadow-lg ring-1 ring-black/5">
                        <p class="truncate px-4 py-2 text-xs text-stone-500">{{ Auth::user()->name }}</p>
                        <a href="{{ route('profile.edit') }}" class="block px-4 py-2 hover:bg-stone-50">Profil</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="block w-full px-4 py-2 text-left text-red-600 hover:bg-stone-50">Keluar</button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-6xl px-4 py-6 lg:px-8">
            @yield('content')
        </main>
    </div>

    {{-- Bottom navigation (ponsel) --}}
    <nav class="fixed inset-x-0 bottom-0 z-40 flex border-t border-stone-200 bg-white pb-[env(safe-area-inset-bottom)] shadow-[0_-6px_20px_rgba(0,0,0,0.05)] lg:hidden print:hidden" aria-label="Navigasi utama">
        @foreach($nav as $item)
        <a href="{{ route($item['route']) }}"
            class="flex flex-1 flex-col items-center gap-0.5 py-2 text-[11px] font-semibold {{ request()->routeIs($item['match']) ? 'text-emerald-700' : 'text-stone-500' }}">
            <i class="bi {{ $item['icon'] }} text-xl"></i>{{ $item['short'] }}
        </a>
        @endforeach
    </nav>

    @stack('scripts')
</body>

</html>