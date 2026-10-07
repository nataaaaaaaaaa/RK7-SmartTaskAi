@extends('layouts.auth')
@section('title', 'Daftar')

@section('content')
@php
$input = 'w-full rounded-xl border-stone-300 bg-white px-4 py-3 text-sm focus:border-emerald-600 focus:ring-emerald-600';
$label = 'mb-1.5 block text-xs font-semibold text-stone-600';
@endphp

<div class="rounded-3xl border border-stone-200 bg-white p-7 shadow-sm sm:p-9">
    <h1 class="text-2xl font-bold">Buat akun</h1>
    <p class="mb-7 mt-1 text-sm text-stone-500">Daftar untuk mulai mencatat tugas harian Anda.</p>

    <form method="POST" action="{{ route('register') }}" class="space-y-4" x-data="{ show: false }">
        @csrf

        <div>
            <label for="name" class="{{ $label }}">Nama lengkap</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" class="{{ $input }}">
            @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="email" class="{{ $label }}">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" class="{{ $input }}">
            @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="phone" class="{{ $label }}">Nomor telepon <span class="font-normal text-stone-400">(opsional)</span></label>
            <input id="phone" type="tel" name="phone" value="{{ old('phone') }}" autocomplete="tel" inputmode="tel" placeholder="Contoh: 081234567890" class="{{ $input }}">
            @error('phone')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="password" class="{{ $label }}">Kata sandi</label>
            <div class="relative">
                <input id="password" :type="show ? 'text' : 'password'" name="password" required autocomplete="new-password" class="{{ $input }} pr-11">
                <button type="button" @click="show = !show" class="absolute right-3 top-1/2 -translate-y-1/2 text-stone-400 hover:text-stone-600" aria-label="Tampilkan atau sembunyikan kata sandi">
                    <i class="bi" :class="show ? 'bi-eye-slash' : 'bi-eye'"></i>
                </button>
            </div>
            @error('password')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@else<p class="mt-1 text-xs text-stone-500">Minimal 8 karakter.</p>@enderror
        </div>

        <div>
            <label for="password_confirmation" class="{{ $label }}">Ulangi kata sandi</label>
            <input id="password_confirmation" :type="show ? 'text' : 'password'" name="password_confirmation" required autocomplete="new-password" class="{{ $input }}">
            @error('password_confirmation')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        <button type="submit" class="w-full rounded-full bg-emerald-700 py-3 text-sm font-semibold text-white transition hover:bg-emerald-800">
            Daftar
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-stone-500">
        Sudah punya akun?
        <a href="{{ route('login') }}" class="font-semibold text-emerald-700 hover:underline">Masuk</a>
    </p>
</div>
@endsection