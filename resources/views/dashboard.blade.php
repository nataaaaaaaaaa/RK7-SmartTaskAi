@extends('layouts.main')
@section('title', 'Kegiatan Harian')
@section('heading', 'Kegiatan Harian')

@section('content')
@php
    $isCurrent  = $date->isSameDay($current);
    $mins       = (int) now()->diffInMinutes($deadline, false);
    $hrs        = intdiv(abs($mins), 60);
    $rem        = abs($mins) % 60;
    $status     = $report?->status ?? 'draft';
    $count      = $report ? $report->activities->count() : 0;
    $photoCount = $report ? $report->activities->sum(fn($a) => $a->photos->count()) : 0;
    $canSubmit  = $canEdit && ! $report?->submitted_at && $count > 0;
    $maxPhotos  = (int) config('report.max_photos');
    $maxMb      = round(config('report.max_photo_kb') / 1024);

    $hour      = now()->hour;
    $greet     = $hour < 11 ? 'Selamat pagi' : ($hour < 15 ? 'Selamat siang' : ($hour < 18 ? 'Selamat sore' : 'Selamat malam'));
    $firstName = Str::before(Auth::user()->name, ' ');

    $statusText = ['draft' => 'Draf', 'submitted' => 'Terkirim', 'late' => 'Terlambat'];
    $statusChip = ['draft' => 'bg-white/15 text-white', 'submitted' => 'bg-emerald-300 text-emerald-950', 'late' => 'bg-red-400 text-white'];

    $banner = match (true) {
        $status === 'submitted' => ['bg-emerald-50 text-emerald-800 ring-emerald-200', 'bi-check-circle-fill', 'Laporan terkirim tepat waktu pada ' . $report->submitted_at->translatedFormat('d M Y, H.i') . '.'],
        $status === 'late'      => ['bg-red-50 text-red-700 ring-red-200', 'bi-exclamation-circle-fill', 'Laporan terkirim terlambat pada ' . $report->submitted_at->translatedFormat('d M Y, H.i') . '.'],
        $overdue                => ['bg-red-50 text-red-700 ring-red-200', 'bi-alarm-fill', 'Batas kirim sudah lewat. Laporan yang dikirim sekarang akan ditandai terlambat.'],
        $mins <= 180            => ['bg-amber-50 text-amber-800 ring-amber-200', 'bi-alarm', "Batas kirim tinggal {$hrs} jam {$rem} menit lagi. Segera kirim laporan Anda."],
        default                 => ['bg-sky-50 text-sky-800 ring-sky-200', 'bi-info-circle', "Batas kirim masih {$hrs} jam {$rem} menit lagi."],
    };

    $input   = 'w-full rounded-xl border-stone-300 bg-white px-3.5 py-2.5 text-sm focus:border-emerald-600 focus:ring-emerald-600';
    $label   = 'mb-1 block text-xs font-semibold text-stone-600';
    $btnMain = 'inline-flex items-center justify-center gap-1.5 rounded-full bg-emerald-700 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-800';
    $btnSoft = 'inline-flex items-center justify-center gap-1.5 rounded-full border border-stone-300 bg-white px-4 py-2 text-sm font-semibold text-stone-700 transition hover:bg-stone-50';
@endphp

<div x-data="{ img: null }">

    {{-- Notifikasi --}}
    @if(session('success'))
        <div x-data="{ show: true }" x-show="show" class="mb-4 flex items-center justify-between rounded-xl bg-emerald-50 px-4 py-3 text-sm text-emerald-800 ring-1 ring-emerald-200" role="status">
            <span><i class="bi bi-check-circle mr-2"></i>{{ session('success') }}</span>
            <button type="button" @click="show = false" class="text-emerald-700" aria-label="Tutup"><i class="bi bi-x-lg"></i></button>
        </div>
    @endif
    @if($errors->any())
        <div class="mb-4 rounded-xl bg-red-50 px-4 py-3 text-sm text-red-700 ring-1 ring-red-200">
            <ul class="list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    {{-- Laporan sebelumnya yang belum dikirim --}}
    @if($isCurrent && $pending->isNotEmpty())
        <div class="mb-4 rounded-xl bg-amber-50 px-4 py-3 text-sm text-amber-900 ring-1 ring-amber-200">
            <p class="mb-2 font-semibold"><i class="bi bi-exclamation-triangle mr-2"></i>Ada laporan sebelumnya yang belum dikirim</p>
            <div class="flex flex-wrap gap-2">
                @foreach($pending as $p)
                    <a href="{{ route('dashboard', ['date' => $p->report_date->toDateString()]) }}" class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-amber-800 ring-1 ring-amber-300 hover:bg-amber-100">
                        {{ $p->report_date->translatedFormat('d M Y') }} · {{ $p->activities_count }} kegiatan
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Banner --}}
    <section class="relative mb-4 overflow-hidden rounded-3xl bg-gradient-to-br from-emerald-700 to-emerald-900 p-6 text-white shadow-lg">
        <div class="absolute -right-12 -top-12 h-56 w-56 rounded-full bg-white/10"></div>
        <div class="absolute -bottom-20 right-24 h-48 w-48 rounded-full bg-amber-300/20"></div>

        <div class="relative flex flex-wrap items-start justify-between gap-4">
            <div class="max-w-xl">
                <p class="mb-1 text-sm text-white/70">{{ $greet }}, {{ $firstName }}</p>
                <h2 class="mb-3 text-2xl font-bold">Laporan kegiatan {{ $date->translatedFormat('l, d F Y') }}</h2>
                <div class="flex flex-wrap items-center gap-2 text-xs">
                    <span class="rounded-full bg-white/15 px-3 py-1 font-semibold">{{ $count }} kegiatan</span>
                    <span class="rounded-full bg-white/15 px-3 py-1 font-semibold">{{ $photoCount }} foto</span>
                    <span class="rounded-full px-3 py-1 font-semibold {{ $statusChip[$status] }}">{{ $statusText[$status] }}</span>
                </div>
            </div>

            @if($canSubmit)
                <form method="POST" action="{{ route('reports.submit') }}" onsubmit="return confirm('Kirim laporan ini sekarang? Waktu pengiriman akan tercatat.')">
                    @csrf
                    <input type="hidden" name="date" value="{{ $date->toDateString() }}">
                    <button class="inline-flex items-center gap-2 rounded-full bg-white px-5 py-2.5 text-sm font-semibold text-emerald-800 transition hover:bg-stone-100">
                        <i class="bi bi-send"></i>Kirim laporan
                    </button>
                </form>
            @endif
        </div>

        <p class="relative mt-5 text-sm text-white/80"><i class="bi bi-clock mr-1.5"></i>Batas kirim: {{ $deadline->translatedFormat('l, d F Y, H.i') }}</p>
    </section>

    {{-- Peringatan batas waktu --}}
    <div class="mb-5 flex items-start gap-2 rounded-xl px-4 py-3 text-sm ring-1 {{ $banner[0] }}" role="status">
        <i class="bi {{ $banner[1] }} mt-0.5"></i><span>{{ $banner[2] }}</span>
    </div>

    {{-- Navigasi tanggal --}}
    <div class="mb-5 flex flex-wrap items-center justify-between gap-2">
        <div class="flex items-center gap-2">
            <a href="{{ route('dashboard', ['date' => $date->copy()->subDay()->toDateString()]) }}" class="grid h-9 w-9 place-items-center rounded-full border border-stone-300 bg-white hover:bg-stone-50" aria-label="Hari sebelumnya"><i class="bi bi-chevron-left"></i></a>
            <span class="min-w-[170px] text-center text-sm font-semibold">{{ $date->translatedFormat('l, d F Y') }}</span>
            @if($isCurrent)
                <span class="grid h-9 w-9 place-items-center rounded-full border border-stone-200 bg-stone-50 text-stone-300"><i class="bi bi-chevron-right"></i></span>
            @else
                <a href="{{ route('dashboard', ['date' => $date->copy()->addDay()->toDateString()]) }}" class="grid h-9 w-9 place-items-center rounded-full border border-stone-300 bg-white hover:bg-stone-50" aria-label="Hari berikutnya"><i class="bi bi-chevron-right"></i></a>
            @endif
        </div>
        @unless($isCurrent)
            <a href="{{ route('dashboard') }}" class="{{ $btnMain }} !py-1.5">Ke laporan berjalan</a>
        @endunless
    </div>

    {{-- Form tambah kegiatan --}}
    @if($canEdit)
        <section class="mb-6 rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
            <h3 class="mb-4 font-bold"><i class="bi bi-plus-circle mr-2 text-emerald-700"></i>Tambah kegiatan</h3>

            <form method="POST" action="{{ route('activities.store') }}" enctype="multipart/form-data" class="space-y-4" x-data="{ previews: [] }">
                @csrf
                <input type="hidden" name="date" value="{{ $date->toDateString() }}">

                <div>
                    <label for="title" class="{{ $label }}">Judul kegiatan</label>
                    <input id="title" type="text" name="title" value="{{ old('title') }}" maxlength="150" required
                           placeholder="Contoh: Kunjungan ke klien PT Maju Jaya" class="{{ $input }}">
                </div>

                <div>
                    <label for="description" class="{{ $label }}">Keterangan (opsional)</label>
                    <textarea id="description" name="description" rows="2" maxlength="1000" class="{{ $input }}" placeholder="Detail kegiatan, hasil, atau kendala">{{ old('description') }}</textarea>
                </div>

                <div>
                    <label for="photos" class="{{ $label }}">Foto kegiatan <span class="font-normal text-stone-400">(wajib, maksimal {{ $maxPhotos }} foto, {{ $maxMb }} MB per foto)</span></label>
                    <input id="photos" type="file" name="photos[]" multiple required accept="image/*"
                           @change="previews = [...$event.target.files].map(f => URL.createObjectURL(f))"
                           class="block w-full rounded-xl border border-stone-300 bg-white text-sm text-stone-600 file:mr-3 file:cursor-pointer file:border-0 file:bg-emerald-700 file:px-4 file:py-2.5 file:text-sm file:font-semibold file:text-white hover:file:bg-emerald-800">
                    <div x-show="previews.length" x-cloak class="mt-3 grid grid-cols-3 gap-2 sm:grid-cols-5">
                        <template x-for="src in previews" :key="src">
                            <img :src="src" alt="Pratinjau foto" class="aspect-square w-full rounded-xl object-cover">
                        </template>
                    </div>
                </div>

                <button type="submit" class="{{ $btnMain }}"><i class="bi bi-plus-lg"></i>Simpan kegiatan</button>
            </form>
        </section>
    @else
        <div class="mb-6 rounded-xl bg-stone-200/60 px-4 py-3 text-sm text-stone-600">
            <i class="bi bi-lock mr-2"></i>Laporan ini sudah melewati batas kirim dan tidak dapat diubah lagi.
        </div>
    @endif

    {{-- Daftar kegiatan --}}
    <h3 class="mb-3 font-bold">Daftar kegiatan</h3>
    <div class="space-y-3">
        @forelse($report?->activities ?? [] as $activity)
            <article class="rounded-2xl border border-stone-200 bg-white p-4 shadow-sm">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="font-semibold">{{ $activity->title }}</p>
                        <p class="text-xs text-stone-500"><i class="bi bi-clock mr-1"></i>Dicatat {{ $activity->created_at->translatedFormat('d M Y, H.i') }}</p>
                        @if($activity->description)<p class="mt-2 whitespace-pre-line text-sm text-stone-600">{{ $activity->description }}</p>@endif
                    </div>
                    @if($canEdit)
                        <form method="POST" action="{{ route('activities.destroy', $activity) }}" onsubmit="return confirm('Hapus kegiatan ini beserta fotonya?')">
                            @csrf @method('DELETE')
                            <button class="text-stone-400 transition hover:text-red-600" title="Hapus kegiatan" aria-label="Hapus kegiatan"><i class="bi bi-trash3 text-lg"></i></button>
                        </form>
                    @endif
                </div>

                @if($activity->photos->isNotEmpty())
                    <div class="mt-3 grid grid-cols-3 gap-2 sm:grid-cols-5">
                        @foreach($activity->photos as $photo)
                            <button type="button" @click="img = @js($photo->url)" class="overflow-hidden rounded-xl" aria-label="Lihat foto">
                                <img src="{{ $photo->url }}" alt="Foto kegiatan" loading="lazy" class="aspect-square w-full object-cover transition hover:scale-105">
                            </button>
                        @endforeach
                    </div>
                @endif
            </article>
        @empty
            <div class="rounded-2xl border border-stone-200 bg-white p-10 text-center shadow-sm">
                <i class="bi bi-camera text-4xl text-stone-300"></i>
                <h3 class="mt-2 font-bold">Belum ada kegiatan</h3>
                <p class="mt-1 text-sm text-stone-500">Catat kegiatan Anda beserta fotonya, lalu kirim laporan sebelum batas waktu.</p>
            </div>
        @endforelse
    </div>

    {{-- Tombol kirim di bawah daftar --}}
    @if($canSubmit)
        <form method="POST" action="{{ route('reports.submit') }}" class="mt-6" onsubmit="return confirm('Kirim laporan ini sekarang? Waktu pengiriman akan tercatat.')">
            @csrf
            <input type="hidden" name="date" value="{{ $date->toDateString() }}">
            <button class="{{ $btnMain }} w-full sm:w-auto"><i class="bi bi-send"></i>Kirim laporan hari ini</button>
        </form>
    @elseif($report?->submitted_at && $canEdit)
        <p class="mt-6 text-sm text-stone-500">Laporan sudah terkirim. Anda masih dapat menambah kegiatan sampai batas kirim.</p>
    @endif

    {{-- Pratinjau foto ukuran penuh --}}
    <div x-show="img" x-cloak @keydown.escape.window="img = null" @click="img = null"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4">
        <img :src="img" alt="Foto kegiatan" class="max-h-[90vh] max-w-full rounded-xl">
        <button type="button" class="absolute right-5 top-5 text-2xl text-white" aria-label="Tutup"><i class="bi bi-x-lg"></i></button>
    </div>
</div>
@endsection