@extends('layouts.admin')
@section('title', 'Semua Tugas')
@section('heading', 'Semua Tugas')

@section('content')
@php
    $prioLabel  = [1 => 'Santai', 2 => 'Normal', 3 => 'Urgent'];
    $prioBorder = [1 => 'border-l-emerald-500', 2 => 'border-l-amber-400', 3 => 'border-l-red-500'];
    $prioPill   = [1 => 'bg-emerald-100 text-emerald-700', 2 => 'bg-amber-100 text-amber-700', 3 => 'bg-red-100 text-red-700'];
    $filtered   = request()->hasAny(['q', 'user_id', 'category', 'status']) && (request('q') || request('user_id') || request('category') || request('status'));
    $selUser    = request('user_id') ? $users->firstWhere('id', (int) request('user_id')) : null;
    $select     = 'rounded-full border-stone-300 bg-white py-2.5 pl-4 pr-9 text-sm focus:border-emerald-600 focus:ring-emerald-600';
    $btnMain    = 'inline-flex items-center justify-center gap-1.5 rounded-full bg-emerald-700 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-800';
    $btnSoft    = 'inline-flex items-center justify-center gap-1.5 rounded-full border border-stone-300 bg-white px-4 py-2 text-sm font-semibold text-stone-700 transition hover:bg-stone-50';
@endphp

<div class="mb-6">
    <h2 class="text-2xl font-bold">Semua Tugas</h2>
    <p class="text-sm text-stone-500">
        Tugas seluruh karyawan. Total {{ $tasks->total() }} tugas
        @if($selUser) untuk <strong class="text-stone-700">{{ $selUser->name }}</strong>@endif.
    </p>
</div>

{{-- Filter bar --}}
<form method="GET" class="mb-5 flex flex-wrap items-center gap-2">
    <div class="relative min-w-[200px] flex-1">
        <i class="bi bi-search absolute left-4 top-1/2 -translate-y-1/2 text-stone-400"></i>
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari judul tugas" aria-label="Cari judul"
               class="w-full rounded-full border-stone-300 bg-white py-2.5 pl-10 pr-4 text-sm focus:border-emerald-600 focus:ring-emerald-600">
    </div>

    <select name="user_id" aria-label="Filter karyawan" class="{{ $select }}">
        <option value="">Semua karyawan</option>
        @foreach($users as $u)<option value="{{ $u->id }}" @selected(request('user_id') == $u->id)>{{ $u->name }}</option>@endforeach
    </select>

    <select name="category" aria-label="Filter kategori" class="{{ $select }}">
        <option value="">Semua kategori</option>
        @foreach(config('tasks.categories') as $c)<option @selected(request('category') === $c)>{{ $c }}</option>@endforeach
    </select>

    <select name="status" aria-label="Filter status" class="{{ $select }}">
        <option value="">Semua status</option>
        <option value="active" @selected(request('status') === 'active')>Belum selesai</option>
        <option value="done" @selected(request('status') === 'done')>Selesai</option>
    </select>

    <button class="{{ $btnMain }}"><i class="bi bi-funnel"></i>Terapkan</button>
    @if($filtered)
        <a href="{{ route('admin.tasks') }}" class="{{ $btnSoft }}">Reset</a>
    @endif
</form>

{{-- Daftar tugas --}}
<div class="space-y-3">
    @forelse($tasks as $t)
        @php
            $dl   = $t->deadline ? \Carbon\Carbon::parse($t->deadline) : null;
            $late = $dl && !$t->is_completed && $dl->isPast() && !$dl->isToday();
        @endphp
        <article class="flex flex-wrap items-center gap-x-5 gap-y-3 rounded-2xl border border-l-4 border-stone-200 bg-white p-4 shadow-sm {{ $prioBorder[$t->priority] ?? 'border-l-stone-300' }} {{ $t->is_completed ? 'opacity-70' : '' }}">

            {{-- Judul --}}
            <div class="flex min-w-[240px] flex-1 items-start gap-3">
                <div class="mt-0.5 grid h-7 w-7 shrink-0 place-items-center rounded-full border-2 text-sm {{ $t->is_completed ? 'border-emerald-600 bg-emerald-600 text-white' : 'border-stone-300 text-transparent' }}">
                    <i class="bi bi-check-lg"></i>
                </div>
                <div class="min-w-0">
                    <div class="mb-1 flex flex-wrap items-center gap-2">
                        <span class="rounded-full border border-stone-200 bg-stone-50 px-2.5 py-0.5 text-xs font-semibold text-stone-500">{{ $t->category ?? 'Lainnya' }}</span>
                        <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $prioPill[$t->priority] ?? 'bg-stone-100 text-stone-600' }}">{{ $prioLabel[$t->priority] ?? 'Normal' }}</span>
                        @if($t->is_completed)
                            <span class="rounded-full bg-emerald-600 px-2.5 py-0.5 text-xs font-semibold text-white">Selesai</span>
                        @elseif($late)
                            <span class="rounded-full bg-stone-800 px-2.5 py-0.5 text-xs font-semibold text-white">Terlambat</span>
                        @else
                            <span class="rounded-full bg-sky-100 px-2.5 py-0.5 text-xs font-semibold text-sky-700">Berjalan</span>
                        @endif
                    </div>
                    <p class="font-semibold {{ $t->is_completed ? 'text-stone-400 line-through' : '' }}">{{ $t->title }}</p>
                    @if($t->description)<p class="mt-0.5 text-sm text-stone-500">{{ Str::limit($t->description, 120) }}</p>@endif
                </div>
            </div>

            {{-- Pemilik --}}
            <div class="flex min-w-[150px] items-center gap-2.5">
                <div class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-emerald-100 text-sm font-bold text-emerald-700">
                    {{ Str::upper(Str::substr($t->user?->name ?? '?', 0, 1)) }}
                </div>
                <div class="min-w-0">
                    <p class="truncate text-sm font-semibold">{{ $t->user?->name ?? 'Akun dihapus' }}</p>
                    <p class="truncate text-xs text-stone-500">{{ $t->user?->email }}</p>
                </div>
            </div>

            {{-- Tanggal --}}
            <div class="min-w-[130px] space-y-0.5 text-xs text-stone-500">
                <p><i class="bi bi-calendar-event mr-1.5"></i>Dikerjakan {{ $t->task_date?->translatedFormat('d M Y') ?? '-' }}</p>
                <p class="{{ $late ? 'font-semibold text-red-600' : '' }}"><i class="bi bi-alarm mr-1.5"></i>Tenggat {{ $dl?->translatedFormat('d M Y') ?? '-' }}</p>
                @if($t->is_completed && $t->completed_at)
                    <p class="text-emerald-700"><i class="bi bi-check2-circle mr-1.5"></i>Selesai {{ $t->completed_at->translatedFormat('d M, H:i') }}</p>
                @endif
            </div>

        </article>
    @empty
        <div class="rounded-2xl border border-stone-200 bg-white p-10 text-center shadow-sm">
            <i class="bi bi-card-checklist text-4xl text-stone-300"></i>
            <h3 class="mt-2 font-bold">Tidak ada tugas yang cocok</h3>
            <p class="mt-1 text-sm text-stone-500">Ubah filter atau kata kunci pencarian.</p>
        </div>
    @endforelse
</div>

<div class="mt-6">{{ $tasks->links() }}</div>
@endsection