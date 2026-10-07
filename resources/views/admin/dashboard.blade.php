@extends('layouts.admin')
@section('title', 'Ringkasan')
@section('heading', 'Ringkasan Aktivitas')

@section('content')
@php
    $max       = max(1, $weekly->max('count'));
    $prioTotal = max(1, $byPriority->sum('total'));
    $prioColor = [3 => 'bg-red-500', 2 => 'bg-amber-400', 1 => 'bg-emerald-500'];
    $statTiles = [
        ['Karyawan',      $stats['users'],   $stats['admins'] . ' admin · +' . $stats['newUsers'] . ' minggu ini', 'bi-people',          'bg-emerald-100 text-emerald-700'],
        ['Total tugas',   $stats['tasks'],   $stats['active'] . ' belum selesai',                                    'bi-card-checklist',  'bg-sky-100 text-sky-700'],
        ['Selesai',       $stats['done'],    $stats['percent'] . '% dari total',                                     'bi-check2-circle',   'bg-green-100 text-green-700'],
        ['Terlambat',     $stats['overdue'], 'seluruh karyawan',                                                     'bi-alarm',           'bg-red-100 text-red-700'],
    ];
@endphp

<div class="mb-6">
    <h2 class="text-2xl font-bold">Ringkasan Aktivitas</h2>
    <p class="text-sm text-stone-500">Gambaran keseluruhan aktivitas to-do seluruh karyawan.</p>
</div>

{{-- Statistik --}}
<section class="mb-6 grid grid-cols-2 gap-3 lg:grid-cols-4">
    @foreach($statTiles as [$lbl, $val, $note, $icon, $tone])
        <div class="rounded-2xl border border-stone-200 bg-white p-4 shadow-sm">
            <div class="mb-3 flex items-center justify-between">
                <p class="text-xs font-semibold text-stone-500">{{ $lbl }}</p>
                <div class="grid h-9 w-9 place-items-center rounded-xl text-lg {{ $tone }}"><i class="bi {{ $icon }}"></i></div>
            </div>
            <p class="text-3xl font-bold leading-none">{{ $val }}</p>
            <p class="mt-2 text-xs text-stone-500">{{ $note }}</p>
        </div>
    @endforeach
</section>

{{-- Penyelesaian keseluruhan --}}
<section class="mb-6 rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
    <div class="mb-2 flex items-center justify-between">
        <h3 class="font-bold">Penyelesaian keseluruhan</h3>
        <span class="text-xl font-bold text-emerald-700">{{ $stats['percent'] }}%</span>
    </div>
    <div class="h-3 overflow-hidden rounded-full bg-stone-100" role="progressbar" aria-valuenow="{{ $stats['percent'] }}" aria-valuemin="0" aria-valuemax="100">
        <div class="h-full rounded-full bg-emerald-600" style="width: {{ $stats['percent'] }}%"></div>
    </div>
    <p class="mt-2 text-xs text-stone-500">{{ $stats['done'] }} dari {{ $stats['tasks'] }} tugas telah diselesaikan oleh seluruh karyawan.</p>
</section>

<div class="mb-6 grid gap-4 lg:grid-cols-3">
    {{-- Grafik 7 hari --}}
    <section class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm lg:col-span-2">
        <h3 class="mb-4 font-bold">Tugas selesai 7 hari terakhir</h3>
        <div class="flex h-44 items-end gap-2 sm:gap-3">
            @foreach($weekly as $d)
                <div class="flex flex-1 flex-col items-center justify-end gap-1">
                    <span class="text-xs font-semibold">{{ $d['count'] }}</span>
                    <div class="min-h-[4px] w-full rounded-t-lg {{ $d['count'] ? 'bg-emerald-600' : 'bg-stone-200' }}" style="height: {{ $d['count'] / $max * 110 }}px"></div>
                    <span class="text-[11px] text-stone-500">{{ $d['label'] }}</span>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Per prioritas (stacked bar) + per kategori --}}
    <section class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
        <h3 class="mb-3 font-bold">Sebaran prioritas</h3>
        <div class="mb-3 flex h-3 overflow-hidden rounded-full bg-stone-100">
            @foreach($byPriority as $p => $row)
                <div class="{{ $prioColor[$p] ?? 'bg-stone-300' }}" style="width: {{ $row['total'] / $prioTotal * 100 }}%"></div>
            @endforeach
        </div>
        <div class="mb-5 flex flex-wrap gap-x-4 gap-y-1 text-xs text-stone-500">
            @foreach($byPriority as $p => $row)
                <span class="flex items-center gap-1.5"><span class="h-2 w-2 rounded-full {{ $prioColor[$p] ?? 'bg-stone-300' }}"></span>{{ $row['label'] }}: <strong class="text-stone-700">{{ $row['total'] }}</strong></span>
            @endforeach
        </div>

        <h3 class="mb-3 font-bold">Per kategori</h3>
        <div class="space-y-3">
            @forelse($byCategory as $c)
                @php $pct = $c->total ? round($c->done / $c->total * 100) : 0; @endphp
                <div>
                    <div class="mb-1 flex justify-between text-xs"><span>{{ $c->category }}</span><span class="text-stone-500">{{ $c->done }}/{{ $c->total }}</span></div>
                    <div class="h-2 overflow-hidden rounded-full bg-stone-100"><div class="h-full rounded-full bg-emerald-600" style="width: {{ $pct }}%"></div></div>
                </div>
            @empty
                <p class="text-xs text-stone-500">Belum ada data.</p>
            @endforelse
        </div>
    </section>
</div>

<div class="grid gap-4 lg:grid-cols-2">
    {{-- Karyawan paling aktif --}}
    <section class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
        <h3 class="mb-4 font-bold">Karyawan dengan tugas terbanyak</h3>
        <div class="space-y-4">
            @forelse($topUsers as $u)
                @php $pct = $u->tasks_count ? round($u->done_count / $u->tasks_count * 100) : 0; @endphp
                <div class="flex items-center gap-3">
                    <div class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-emerald-100 text-sm font-bold text-emerald-700">{{ Str::upper(Str::substr($u->name, 0, 1)) }}</div>
                    <div class="min-w-0 flex-1">
                        <div class="mb-1 flex justify-between text-sm"><span class="truncate font-semibold">{{ $u->name }}</span><span class="text-xs text-stone-500">{{ $u->done_count }}/{{ $u->tasks_count }} selesai</span></div>
                        <div class="h-2 overflow-hidden rounded-full bg-stone-100"><div class="h-full rounded-full bg-emerald-600" style="width: {{ $pct }}%"></div></div>
                    </div>
                </div>
            @empty
                <p class="text-sm text-stone-500">Belum ada karyawan.</p>
            @endforelse
        </div>
    </section>

    {{-- Karyawan terbaru --}}
    <section class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
        <div class="mb-4 flex items-center justify-between">
            <h3 class="font-bold">Karyawan terbaru</h3>
            <a href="{{ route('admin.users') }}" class="text-sm font-semibold text-emerald-700 hover:underline">Lihat semua</a>
        </div>
        <ul class="divide-y divide-stone-100">
            @foreach($recentUsers as $u)
                <li class="flex items-center justify-between gap-3 py-2.5 text-sm">
                    <div class="flex min-w-0 items-center gap-3">
                        <div class="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-stone-100 text-xs font-bold text-stone-600">{{ Str::upper(Str::substr($u->name, 0, 1)) }}</div>
                        <div class="min-w-0">
                            <p class="truncate font-semibold">{{ $u->name }}</p>
                            <p class="truncate text-xs text-stone-500">{{ $u->email }}</p>
                        </div>
                    </div>
                    <span class="shrink-0 text-xs text-stone-500">{{ $u->created_at->diffForHumans() }}</span>
                </li>
            @endforeach
        </ul>
    </section>
</div>
@endsection