@extends('layouts.auth')
@section('title', 'Atur Ulang Kata Sandi')

@section('content')
@php
    $input = 'w-full rounded-xl border-stone-300 bg-white px-4 py-3 text-sm focus:border-emerald-600 focus:ring-emerald-600';
    $label = 'mb-1.5 block text-xs font-semibold text-stone-600';
@endphp

<div class="rounded-3xl border border-stone-200 bg-white p-7 shadow-sm sm:p-9">
    <div class="mb-5 grid h-12 w-12 place-items-center rounded-2xl bg-emerald-100 text-xl text-emerald-700">
        <i class="bi bi-shield-lock"></i>
    </div>
    <h1 class="text-2xl font-bold">Atur ulang kata sandi</h1>
    <p class="mb-7 mt-1 text-sm text-stone-500">Buat kata sandi baru untuk akun Anda.</p>

    <form method="POST" action="{{ route('password.store') }}" class="space-y-4" x-data="{ show: false }">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div>
            <label for="email" class="{{ $label }}">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username" class="{{ $input }}">
            @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="password" class="{{ $label }}">Kata sandi baru</label>
            <div class="relative">
                <input id="password" :type="show ? 'text' : 'password'" name="password" required autocomplete="new-password" class="{{ $input }} pr-11">
                <button type="button" @click="show = !show" class="absolute right-3 top-1/2 -translate-y-1/2 text-stone-400 hover:text-stone-600" aria-label="Tampilkan atau sembunyikan kata sandi">
                    <i class="bi" :class="show ? 'bi-eye-slash' : 'bi-eye'"></i>
                </button>
            </div>
            @error('password')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="password_confirmation" class="{{ $label }}">Ulangi kata sandi baru</label>
            <input id="password_confirmation" :type="show ? 'text' : 'password'" name="password_confirmation" required autocomplete="new-password" class="{{ $input }}">
            @error('password_confirmation')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        <button type="submit" class="w-full rounded-full bg-emerald-700 py-3 text-sm font-semibold text-white transition hover:bg-emerald-800">
            Simpan kata sandi baru
        </button>
    </form>
</div>
@endsection