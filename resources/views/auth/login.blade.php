@extends('layouts.auth')
@section('title', 'Masuk')

@section('content')
@php
    $input = 'w-full rounded-xl border-stone-300 bg-white px-4 py-3 text-sm focus:border-emerald-600 focus:ring-emerald-600';
    $label = 'mb-1.5 block text-xs font-semibold text-stone-600';
@endphp

<div class="rounded-3xl border border-stone-200 bg-white p-7 shadow-sm sm:p-9">
    <h1 class="text-2xl font-bold">Selamat datang kembali</h1>
    <p class="mb-7 mt-1 text-sm text-stone-500">Masuk untuk mengelola tugas harian Anda.</p>

    @if(session('status'))
        <div class="mb-4 rounded-xl bg-emerald-50 px-4 py-3 text-sm text-emerald-800 ring-1 ring-emerald-200" role="status">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-4" x-data="{ show: false }">
        @csrf

        <div>
            <label for="email" class="{{ $label }}">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" class="{{ $input }}">
            @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="password" class="{{ $label }}">Kata sandi</label>
            <div class="relative">
                <input id="password" :type="show ? 'text' : 'password'" name="password" required autocomplete="current-password" class="{{ $input }} pr-11">
                <button type="button" @click="show = !show" class="absolute right-3 top-1/2 -translate-y-1/2 text-stone-400 hover:text-stone-600" aria-label="Tampilkan atau sembunyikan kata sandi">
                    <i class="bi" :class="show ? 'bi-eye-slash' : 'bi-eye'"></i>
                </button>
            </div>
            @error('password')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        <div class="flex items-center justify-between">
            <label for="remember_me" class="flex cursor-pointer items-center gap-2 text-sm text-stone-600">
                <input id="remember_me" type="checkbox" name="remember" class="rounded border-stone-300 text-emerald-700 focus:ring-emerald-600">
                Ingat saya
            </label>
            @if(Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="text-sm font-semibold text-emerald-700 hover:underline">Lupa kata sandi?</a>
            @endif
        </div>

        <button type="submit" class="w-full rounded-full bg-emerald-700 py-3 text-sm font-semibold text-white transition hover:bg-emerald-800">
            Masuk
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-stone-500">
        Belum punya akun?
        <a href="{{ route('register') }}" class="font-semibold text-emerald-700 hover:underline">Daftar</a>
    </p>
</div>
@endsection