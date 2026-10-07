@extends('layouts.admin')
@section('title', 'Karyawan')
@section('heading', 'Daftar Karyawan')

@section('content')
@php
    $input   = 'w-full rounded-xl border-stone-300 bg-white px-3.5 py-2.5 text-sm focus:border-emerald-600 focus:ring-emerald-600';
    $btnMain = 'inline-flex items-center justify-center gap-1.5 rounded-full bg-emerald-700 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-800';
    $btnSoft = 'inline-flex items-center justify-center gap-1.5 rounded-full border border-stone-300 bg-white px-4 py-2 text-sm font-semibold text-stone-700 transition hover:bg-stone-50';
@endphp

<div class="mb-6">
    <h2 class="text-2xl font-bold">Daftar Karyawan</h2>
    <p class="text-sm text-stone-500">Seluruh akun beserta kontak dan progres tugas masing-masing. Total {{ $users->total() }} akun.</p>
</div>

{{-- Filter bar --}}
<form method="GET" class="mb-5 flex flex-wrap items-center gap-2">
    <div class="relative min-w-[220px] flex-1">
        <i class="bi bi-search absolute left-4 top-1/2 -translate-y-1/2 text-stone-400"></i>
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari nama, email, atau nomor telepon" aria-label="Cari karyawan"
               class="w-full rounded-full border-stone-300 bg-white py-2.5 pl-10 pr-4 text-sm focus:border-emerald-600 focus:ring-emerald-600">
    </div>
    <select name="role" aria-label="Filter role" class="rounded-full border-stone-300 bg-white py-2.5 pl-4 pr-9 text-sm focus:border-emerald-600 focus:ring-emerald-600">
        <option value="">Semua role</option>
        <option value="admin" @selected(request('role') === 'admin')>Admin</option>
        <option value="user" @selected(request('role') === 'user')>Karyawan</option>
    </select>
    <button class="{{ $btnMain }}"><i class="bi bi-funnel"></i>Terapkan</button>
    @if(request('q') || request('role'))
        <a href="{{ route('admin.users') }}" class="{{ $btnSoft }}">Reset</a>
    @endif
</form>

{{-- Daftar karyawan --}}
<div class="space-y-3">
    @forelse($users as $u)
        @php $pct = $u->tasks_count ? round($u->done_count / $u->tasks_count * 100) : 0; @endphp
        <article class="flex flex-wrap items-center gap-x-6 gap-y-4 rounded-2xl border border-stone-200 bg-white p-4 shadow-sm">

            {{-- Identitas --}}
            <div class="flex min-w-[240px] flex-1 items-center gap-3">
                <div class="grid h-12 w-12 shrink-0 place-items-center rounded-full bg-emerald-100 text-lg font-bold text-emerald-700">
                    {{ Str::upper(Str::substr($u->name, 0, 1)) }}
                </div>
                <div class="min-w-0">
                    <p class="flex items-center gap-2 font-semibold">
                        <span class="truncate">{{ $u->name }}</span>
                        @if($u->id === auth()->id())<span class="rounded-full bg-stone-800 px-2 py-0.5 text-[10px] font-semibold text-white">Anda</span>@endif
                    </p>
                    <p class="truncate text-xs text-stone-500"><i class="bi bi-envelope mr-1"></i>{{ $u->email }}</p>
                    <p class="truncate text-xs text-stone-500">
                        <i class="bi bi-telephone mr-1"></i>
                        @if($u->phone)
                            <a href="tel:{{ $u->phone }}" class="hover:text-emerald-700 hover:underline">{{ $u->phone }}</a>
                        @else
                            <span class="text-stone-400">Belum diisi</span>
                        @endif
                    </p>
                </div>
            </div>

            {{-- Progres tugas --}}
            <div class="w-full min-w-[160px] sm:w-48">
                <div class="mb-1 flex justify-between text-xs">
                    <span class="text-stone-500">Tugas selesai</span>
                    <span class="font-semibold">{{ $u->done_count }}/{{ $u->tasks_count }}</span>
                </div>
                <div class="h-2 overflow-hidden rounded-full bg-stone-100" role="progressbar" aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100">
                    <div class="h-full rounded-full bg-emerald-600" style="width: {{ $pct }}%"></div>
                </div>
                <p class="mt-1 text-[11px] text-stone-400">Bergabung {{ $u->created_at->translatedFormat('d M Y') }}</p>
            </div>

            {{-- Role --}}
            <div>
                @if($u->id === auth()->id())
                    <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">{{ $u->role === 'admin' ? 'Admin' : 'Karyawan' }}</span>
                @else
                    <form method="POST" action="{{ route('admin.users.role', $u) }}">
                        @csrf @method('PATCH')
                        <select name="role" onchange="this.form.submit()" aria-label="Ubah role {{ $u->name }}"
                                class="rounded-full border-stone-300 bg-white py-1.5 pl-3 pr-8 text-xs font-semibold focus:border-emerald-600 focus:ring-emerald-600">
                            <option value="user" @selected($u->role === 'user')>Karyawan</option>
                            <option value="admin" @selected($u->role === 'admin')>Admin</option>
                        </select>
                    </form>
                @endif
            </div>

            {{-- Aksi --}}
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.tasks', ['user_id' => $u->id]) }}" class="{{ $btnSoft }} !px-3 !py-1.5" title="Lihat tugas" aria-label="Lihat tugas {{ $u->name }}"><i class="bi bi-list-task"></i>Tugas</a>
                @if($u->id !== auth()->id())
                    <form method="POST" action="{{ route('admin.users.destroy', $u) }}"
                          onsubmit="return confirm(@js('Hapus ' . $u->name . ' beserta semua tugasnya? Tidak bisa dibatalkan.'))">
                        @csrf @method('DELETE')
                        <button class="grid h-9 w-9 place-items-center rounded-full border border-red-200 text-red-600 transition hover:bg-red-50" title="Hapus akun" aria-label="Hapus akun {{ $u->name }}"><i class="bi bi-trash3"></i></button>
                    </form>
                @endif
            </div>
        </article>
    @empty
        <div class="rounded-2xl border border-stone-200 bg-white p-10 text-center shadow-sm">
            <i class="bi bi-people text-4xl text-stone-300"></i>
            <h3 class="mt-2 font-bold">Tidak ada karyawan yang cocok</h3>
            <p class="mt-1 text-sm text-stone-500">Ubah kata kunci atau filter pencarian.</p>
        </div>
    @endforelse
</div>

<div class="mt-6">{{ $users->links() }}</div>
@endsection