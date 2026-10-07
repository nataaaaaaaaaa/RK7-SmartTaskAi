@extends('layouts.auth')
@section('title', 'Lupa Kata Sandi')

@section('content')
@php
    $input = 'w-full rounded-xl border-stone-300 bg-white px-4 py-3 text-sm focus:border-emerald-600 focus:ring-emerald-600';
    $label = 'mb-1.5 block text-xs font-semibold text-stone-600';
@endphp

<div class="rounded-3xl border border-stone-200 bg-white p-7 shadow-sm sm:p-9">
    <div class="mb-5 grid h-12 w-12 place-items-center rounded-2xl bg-emerald-100 text-xl text-emerald-700">
        <i class="bi bi-key"></i>
    </div>
    <h1 class="text-2xl font-bold">Lupa kata sandi?</h1>
    <p class="mb-7 mt-1 text-sm text-stone-500">Masukkan email yang terdaftar, dan kami kirimkan tautan untuk membuat kata sandi baru.</p>

    @if(session('status'))
        <div class="mb-4 rounded-xl bg-emerald-50 px-4 py-3 text-sm text-emerald-800 ring-1 ring-emerald-200" role="status">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf

        <div>
            <label for="email" class="{{ $label }}">Email terdaftar</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus class="{{ $input }}">
            @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        <button type="submit" class="w-full rounded-full bg-emerald-700 py-3 text-sm font-semibold text-white transition hover:bg-emerald-800">
            Kirim tautan reset
        </button>
    </form>

    <p class="mt-6 text-center text-sm">
        <a href="{{ route('login') }}" class="font-semibold text-emerald-700 hover:underline"><i class="bi bi-arrow-left mr-1"></i>Kembali ke halaman masuk</a>
    </p>
</div>
@endsection