@extends('layouts.admin')
@section('title', 'Aktivitas Harian')
@section('heading', 'Aktivitas Harian')

@section('content')
@php
    $isToday   = $date->isToday();
    $prioLabel = [1 => 'Santai', 2 => 'Normal', 3 => 'Urgent'];
    $prioPill  = [1 => 'bg-emerald-100 text-emerald-700', 2 => 'bg-amber-100 text-amber-700', 3 => 'bg-red-100 text-red-700'];
    $tiles = [
        ['Karyawan',         $summary['employees'],                         'bi-people',          'bg-emerald-100 text-emerald-700'],
        ['Sudah ada tugas',  $summary['active'],                            'bi-clipboard-check', 'bg-sky-100 text-sky-700'],
        ['Belum ada tugas',  $summary['idle'],                              'bi-clipboard-x',     'bg-amber-100 text-amber-700'],
        ['Tugas selesai',    $summary['done'] . '/' . $summary['tasks'],    'bi-check2-circle',   'bg-green-100 text-green-700'],
    ];
    $btnMain = 'inline-flex items-center justify-center gap-1.5 rounded-full bg-emerald-700 px-4 py-1.5 text-sm font-semibold text-white transition hover:bg-emerald-800';
@endphp

<div class="mb-6">
    <h2 class="text-2xl font-bold">Aktivitas Harian</h2>
    <p class="text-sm text-stone-500">Pantau siapa yang sudah menyusun to-do dan sejauh mana progresnya pada tanggal tertentu.</p>
</div>

{{-- Navigasi tanggal --}}
<div class="mb-5 flex flex-wrap items-center justify-between gap-3">
    <div class="flex items-center gap-2">
        <a href="{{ route('admin.activity', ['date' => $date->copy()->subDay()->toDateString()]) }}" class="grid h-9 w-9 place-items-center rounded-full border border-stone-300 bg-white hover:bg-stone-50" aria-label="Hari sebelumnya"><i class="bi bi-chevron-left"></i></a>
        <span class="min-w-[190px] text-center text-sm font-semibold">{{ $date->translatedFormat('l, d F Y') }}</span>
        <a href="{{ route('admin.activity', ['date' => $date->copy()->addDay()->toDateString()]) }}" class="grid h-9 w-9 place-items-center rounded-full border border-stone-300 bg-white hover:bg-stone-50" aria-label="Hari berikutnya"><i class="bi bi-chevron-right"></i></a>
    </div>
    <div class="flex items-center gap-2">
        @unless($isToday)
            <a href="{{ route('admin.activity') }}" class="{{ $btnMain }}">Hari ini</a>
        @endunless
        <form method="GET" action="{{ route('admin.activity') }}">
            <input type="date" name="date" value="{{ $date->toDateString() }}" onchange="this.form.submit()" aria-label="Pilih tanggal"
                   class="rounded-full border-stone-300 bg-white px-3 py-1.5 text-sm focus:border-emerald-600 focus:ring-emerald-600">
        </form>
    </div>
</div>

{{-- Ringkasan --}}
<section class="mb-6 grid grid-cols-2 gap-3 lg:grid-cols-4">
    @foreach($tiles as [$lbl, $val, $icon, $tone])
        <div class="flex items-center gap-3 rounded-2xl border border-stone-200 bg-white p-4 shadow-sm">
            <div class="grid h-11 w-11 shrink-0 place-items-center rounded-xl text-xl {{ $tone }}"><i class="bi {{ $icon }}"></i></div>
            <div>
                <p class="text-2xl font-bold leading-none">{{ $val }}</p>
                <p class="mt-1 text-xs text-stone-500">{{ $lbl }}</p>
            </div>
        </div>
    @endforeach
</section>

<div x-data="{ f: 'all' }">
    {{-- Filter bar --}}
    <div class="mb-4 flex gap-2">
        @foreach(['all' => 'Semua', 'has' => 'Sudah ada tugas', 'none' => 'Belum ada tugas'] as $key => $text)
            <button type="button" @click="f = '{{ $key }}'"
                    :class="f === '{{ $key }}' ? 'border-emerald-700 bg-emerald-700 text-white' : 'border-stone-200 bg-white text-stone-600 hover:bg-stone-50'"
                    class="rounded-full border px-4 py-1.5 text-sm font-semibold transition">{{ $text }}</button>
        @endforeach
    </div>

    {{-- Daftar karyawan --}}
    <div class="space-y-3">
        @forelse($employees as $u)
            @php
                $t     = $u->tasks->count();
                $d     = $u->tasks->where('is_completed', true)->count();
                $pct   = $t ? round($d / $t * 100) : 0;
                $state = $t ? 'has' : 'none';
            @endphp
            <article x-data="{ open: false }" x-show="f === 'all' || f === '{{ $state }}'"
                     class="rounded-2xl border border-stone-200 bg-white p-4 shadow-sm">
                <div class="flex flex-wrap items-center gap-x-6 gap-y-3">
                    <div class="flex min-w-[220px] flex-1 items-center gap-3">
                        <div class="grid h-11 w-11 shrink-0 place-items-center rounded-full bg-emerald-100 font-bold text-emerald-700">{{ Str::upper(Str::substr($u->name, 0, 1)) }}</div>
                        <div class="min-w-0">
                            <p class="truncate font-semibold">{{ $u->name }}</p>
                            <p class="truncate text-xs text-stone-500">
                                <i class="bi bi-telephone mr-1"></i>{{ $u->phone ?: 'Nomor belum diisi' }}
                            </p>
                        </div>
                    </div>

                    <div class="w-full sm:w-56">
                        <div class="mb-1 flex items-center justify-between text-xs">
                            @if(!$t)
                                <span class="rounded-full bg-amber-100 px-2.5 py-0.5 font-semibold text-amber-700">Belum ada tugas</span>
                            @elseif($d === $t)
                                <span class="rounded-full bg-emerald-100 px-2.5 py-0.5 font-semibold text-emerald-700">Semua selesai</span>
                            @else
                                <span class="rounded-full bg-sky-100 px-2.5 py-0.5 font-semibold text-sky-700">{{ $t - $d }} berjalan</span>
                            @endif
                            <span class="font-semibold">{{ $d }}/{{ $t }}</span>
                        </div>
                        <div class="h-2 overflow-hidden rounded-full bg-stone-100" role="progressbar" aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100">
                            <div class="h-full rounded-full bg-emerald-600" style="width: {{ $pct }}%"></div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        @if($u->carried_count)
                            <span class="rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-semibold text-red-700" title="Tugas belum selesai dari hari sebelumnya">{{ $u->carried_count }} tertunda</span>
                        @endif
                        @if($t)
                            <button type="button" @click="open = !open"
                                    class="inline-flex items-center gap-1.5 rounded-full border border-stone-300 bg-white px-3 py-1.5 text-xs font-semibold text-stone-700 hover:bg-stone-50">
                                Rincian <i class="bi" :class="open ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
                            </button>
                        @endif
                    </div>
                </div>

                @if($t)
                    <ul x-show="open" x-cloak class="mt-4 divide-y divide-stone-100 border-t border-stone-100 pt-2">
                        @foreach($u->tasks as $task)
                            <li class="flex flex-wrap items-center gap-x-3 gap-y-1 py-2.5 text-sm">
                                <span class="grid h-6 w-6 shrink-0 place-items-center rounded-full border-2 text-xs {{ $task->is_completed ? 'border-emerald-600 bg-emerald-600 text-white' : 'border-stone-300 text-transparent' }}"><i class="bi bi-check-lg"></i></span>
                                <span class="min-w-0 flex-1 {{ $task->is_completed ? 'text-stone-400 line-through' : 'font-medium' }}">{{ $task->title }}</span>
                                <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $prioPill[$task->priority] ?? 'bg-stone-100 text-stone-600' }}">{{ $prioLabel[$task->priority] ?? 'Normal' }}</span>
                                @if($task->is_completed && $task->completed_at)
                                    <span class="text-xs text-emerald-700"><i class="bi bi-clock mr-1"></i>{{ $task->completed_at->translatedFormat('H:i') }}</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif
            </article>
        @empty
            <div class="rounded-2xl border border-stone-200 bg-white p-10 text-center shadow-sm">
                <i class="bi bi-people text-4xl text-stone-300"></i>
                <h3 class="mt-2 font-bold">Belum ada karyawan</h3>
                <p class="mt-1 text-sm text-stone-500">Akun karyawan akan muncul di sini setelah mereka mendaftar.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection