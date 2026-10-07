@extends('layouts.main')
@section('title', 'Profil')
@section('heading', 'Profil Saya')

@section('content')
@php
    $user    = Auth::user();
    $input   = 'w-full rounded-xl border-stone-300 bg-white px-3.5 py-2.5 text-sm focus:border-emerald-600 focus:ring-emerald-600';
    $label   = 'mb-1 block text-xs font-semibold text-stone-600';
    $btnMain = 'inline-flex items-center justify-center gap-1.5 rounded-full bg-emerald-700 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-800';
    $pwErr   = $errors->updatePassword;
@endphp

<div class="mx-auto max-w-2xl">

    {{-- Kartu identitas --}}
    <section class="relative mb-6 overflow-hidden rounded-3xl bg-gradient-to-br from-emerald-700 to-emerald-900 p-6 text-white shadow-lg">
        <div class="absolute -right-12 -top-12 h-48 w-48 rounded-full bg-white/10"></div>
        <div class="relative flex items-center gap-4">
            <div class="grid h-16 w-16 shrink-0 place-items-center rounded-full bg-amber-400 text-2xl font-bold text-amber-950">
                {{ Str::upper(Str::substr($user->name, 0, 1)) }}
            </div>
            <div class="min-w-0">
                <h2 class="truncate text-xl font-bold">{{ $user->name }}</h2>
                <p class="truncate text-sm text-white/80">{{ $user->email }}</p>
                @if($user->phone)
                    <p class="truncate text-sm text-white/80"><i class="bi bi-telephone mr-1"></i>{{ $user->phone }}</p>
                @endif
                <span class="mt-2 inline-block rounded-full bg-white/15 px-3 py-0.5 text-xs font-semibold">{{ $user->isAdmin() ? 'Administrator' : 'Karyawan' }}</span>
            </div>
        </div>
    </section>

    {{-- Informasi profil --}}
    <section class="mb-6 rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
        <h3 class="font-bold">Informasi profil</h3>
        <p class="mb-5 text-sm text-stone-500">Perbarui nama, email, dan nomor telepon akun Anda.</p>

        @if(session('status') === 'profile-updated')
            <div class="mb-4 rounded-xl bg-emerald-50 px-4 py-3 text-sm text-emerald-800 ring-1 ring-emerald-200" role="status">
                <i class="bi bi-check-circle mr-2"></i>Profil berhasil diperbarui.
            </div>
        @endif

        <form method="POST" action="{{ route('profile.update') }}" class="space-y-4">
            @csrf
            @method('PATCH')

            <div>
                <label for="name" class="{{ $label }}">Nama lengkap</label>
                <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}" required autocomplete="name" class="{{ $input }}">
                @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="email" class="{{ $label }}">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required autocomplete="username" class="{{ $input }}">
                @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="phone" class="{{ $label }}">Nomor telepon</label>
                <input id="phone" type="tel" name="phone" value="{{ old('phone', $user->phone) }}" autocomplete="tel" inputmode="tel" placeholder="Contoh: 081234567890" class="{{ $input }}">
                @error('phone')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <button type="submit" class="{{ $btnMain }}">Simpan perubahan</button>
        </form>
    </section>

    {{-- Ubah kata sandi --}}
    <section class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
        <h3 class="font-bold">Ubah kata sandi</h3>
        <p class="mb-5 text-sm text-stone-500">Gunakan kata sandi yang panjang dan sulit ditebak.</p>

        @if(session('status') === 'password-updated')
            <div class="mb-4 rounded-xl bg-emerald-50 px-4 py-3 text-sm text-emerald-800 ring-1 ring-emerald-200" role="status">
                <i class="bi bi-check-circle mr-2"></i>Kata sandi berhasil diperbarui.
            </div>
        @endif

        <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label for="current_password" class="{{ $label }}">Kata sandi saat ini</label>
                <input id="current_password" type="password" name="current_password" autocomplete="current-password" class="{{ $input }}">
                @if($pwErr->has('current_password'))<p class="mt-1 text-xs text-red-600">{{ $pwErr->first('current_password') }}</p>@endif
            </div>

            <div>
                <label for="new_password" class="{{ $label }}">Kata sandi baru</label>
                <input id="new_password" type="password" name="password" autocomplete="new-password" class="{{ $input }}">
                @if($pwErr->has('password'))<p class="mt-1 text-xs text-red-600">{{ $pwErr->first('password') }}</p>@endif
            </div>

            <div>
                <label for="password_confirmation" class="{{ $label }}">Ulangi kata sandi baru</label>
                <input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" class="{{ $input }}">
                @if($pwErr->has('password_confirmation'))<p class="mt-1 text-xs text-red-600">{{ $pwErr->first('password_confirmation') }}</p>@endif
            </div>

            <button type="submit" class="{{ $btnMain }}">Perbarui kata sandi</button>
        </form>
    </section>
</div>
@endsection