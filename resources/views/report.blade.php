@extends('layouts.main')
@section('title', 'Laporan')
@section('heading', 'Laporan Kegiatan')

@section('content')
@php
    $statusChip = [
        'submitted' => ['bg-emerald-100 text-emerald-700', 'Terkirim tepat waktu'],
        'late'      => ['bg-red-100 text-red-700',         'Terlambat'],
        'draft'     => ['bg-amber-100 text-amber-700',     'Draf belum dikirim'],
        'missing'   => ['bg-stone-200 text-stone-600',     'Belum mengisi'],
    ];
    $tiles = [
        ['Tepat waktu',    $stats['submitted'], 'bi-check2-circle',  'bg-emerald-100 text-emerald-700'],
        ['Terlambat',      $stats['late'],      'bi-alarm',          'bg-red-100 text-red-700'],
        ['Draf',           $stats['draft'],     'bi-pencil-square',  'bg-amber-100 text-amber-700'],
        ['Belum mengisi',  $stats['missing'],   'bi-slash-circle',   'bg-stone-200 text-stone-600'],
    ];
    $query   = ['from' => $from->toDateString(), 'to' => $to->toDateString()];
    $btnMain = 'inline-flex items-center justify-center gap-1.5 rounded-full bg-emerald-700 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-800';
    $btnSoft = 'inline-flex items-center justify-center gap-1.5 rounded-full border border-stone-300 bg-white px-4 py-2 text-sm font-semibold text-stone-700 transition hover:bg-stone-50';
    $chip    = 'rounded-full border border-stone-300 bg-white px-3 py-1 text-xs font-semibold text-stone-600 hover:bg-stone-50';
@endphp

<div x-data="{ img: null }">

    {{-- Judul + aksi --}}
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-2xl font-bold">Laporan Kegiatan Harian</h2>
            <p class="text-sm text-stone-500">{{ Auth::user()->name }} · {{ $from->translatedFormat('d M Y') }} sampai {{ $to->translatedFormat('d M Y') }}</p>
        </div>
        <div class="flex gap-2 print:hidden">
            <a href="{{ route('report.export', $query) }}" class="{{ $btnSoft }}"><i class="bi bi-file-earmark-spreadsheet"></i>Unduh CSV</a>
            <button type="button" onclick="window.print()" class="{{ $btnMain }}"><i class="bi bi-printer"></i>Cetak / PDF</button>
        </div>
    </div>

    {{-- Filter tanggal --}}
    <form method="GET" action="{{ route('report') }}" class="mb-6 rounded-2xl border border-stone-200 bg-white p-4 shadow-sm print:hidden">
        <div class="flex flex-wrap items-end gap-3">
            <div>
                <label for="from" class="mb-1 block text-xs font-semibold text-stone-600">Dari tanggal</label>
                <input id="from" type="date" name="from" value="{{ $from->toDateString() }}" max="{{ $current->toDateString() }}" class="rounded-xl border-stone-300 bg-white text-sm focus:border-emerald-600 focus:ring-emerald-600">
            </div>
            <div>
                <label for="to" class="mb-1 block text-xs font-semibold text-stone-600">Sampai tanggal</label>
                <input id="to" type="date" name="to" value="{{ $to->toDateString() }}" max="{{ $current->toDateString() }}" class="rounded-xl border-stone-300 bg-white text-sm focus:border-emerald-600 focus:ring-emerald-600">
            </div>
            <button class="{{ $btnMain }}"><i class="bi bi-funnel"></i>Terapkan</button>

            <div class="flex flex-wrap gap-2 sm:ml-auto">
                <a href="{{ route('report', ['from' => $current->copy()->subDays(6)->toDateString(), 'to' => $current->toDateString()]) }}" class="{{ $chip }}">7 hari</a>
                <a href="{{ route('report', ['from' => $current->copy()->subDays(29)->toDateString(), 'to' => $current->toDateString()]) }}" class="{{ $chip }}">30 hari</a>
                <a href="{{ route('report', ['from' => $current->copy()->startOfMonth()->toDateString(), 'to' => $current->toDateString()]) }}" class="{{ $chip }}">Bulan ini</a>
            </div>
        </div>
    </form>

    {{-- Ringkasan --}}
    <section class="mb-4 grid grid-cols-2 gap-3 lg:grid-cols-4">
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

    <section class="mb-6 rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
        <div class="mb-2 flex items-center justify-between">
            <h3 class="font-bold">Kepatuhan pengisian</h3>
            <span class="text-xl font-bold text-emerald-700">{{ $stats['compliance'] }}%</span>
        </div>
        <div class="h-3 overflow-hidden rounded-full bg-stone-100" role="progressbar" aria-valuenow="{{ $stats['compliance'] }}" aria-valuemin="0" aria-valuemax="100">
            <div class="h-full rounded-full bg-emerald-600" style="width: {{ $stats['compliance'] }}%"></div>
        </div>
        <p class="mt-2 text-xs text-stone-500">
            {{ $stats['submitted'] + $stats['late'] }} dari {{ $stats['workdays'] }} hari kerja sudah mengirim laporan.
            Total {{ $stats['activities'] }} kegiatan dan {{ $stats['photos'] }} foto pada periode ini.
        </p>
    </section>

    {{-- Riwayat per hari --}}
    <div class="space-y-4">
        @foreach($days as $day)
            @php
                $report = $day['report'];
                $status = $day['status'];
            @endphp
            @continue($status === 'off')

            <article class="flex gap-4 rounded-2xl border border-stone-200 bg-white p-4 shadow-sm {{ $status === 'missing' ? 'opacity-70' : '' }}">
                {{-- Tanggal --}}
                <div class="w-14 shrink-0 text-center">
                    <p class="text-2xl font-bold leading-none">{{ $day['date']->format('d') }}</p>
                    <p class="mt-1 text-[11px] font-semibold uppercase text-stone-500">{{ $day['date']->translatedFormat('M Y') }}</p>
                    <p class="text-[11px] text-stone-400">{{ $day['date']->translatedFormat('D') }}</p>
                </div>

                <div class="min-w-0 flex-1">
                    <div class="mb-2 flex flex-wrap items-center gap-2">
                        <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $statusChip[$status][0] }}">{{ $statusChip[$status][1] }}</span>
                        @if($report?->submitted_at)
                            <span class="text-xs text-stone-500"><i class="bi bi-clock mr-1"></i>Dikirim {{ $report->submitted_at->translatedFormat('d M, H.i') }}</span>
                        @endif
                    </div>

                    @if($report && $report->activities->isNotEmpty())
                        <ol class="space-y-4">
                            @foreach($report->activities as $a)
                                <li>
                                    <p class="font-semibold"><span class="mr-1.5 text-stone-400">{{ $loop->iteration }}.</span>{{ $a->title }}</p>
                                    @if($a->description)<p class="mt-0.5 whitespace-pre-line text-sm text-stone-600">{{ $a->description }}</p>@endif
                                    @if($a->photos->isNotEmpty())
                                        <div class="mt-2 grid grid-cols-4 gap-2 sm:grid-cols-6 print:grid-cols-6">
                                            @foreach($a->photos as $photo)
                                                <button type="button" @click="img = @js($photo->url)" class="overflow-hidden rounded-lg" aria-label="Lihat foto">
                                                    <img src="{{ $photo->url }}" alt="Foto kegiatan" loading="lazy" class="aspect-square w-full object-cover">
                                                </button>
                                            @endforeach
                                        </div>
                                    @endif
                                </li>
                            @endforeach
                        </ol>
                    @else
                        <p class="text-sm text-stone-500">Tidak ada laporan kegiatan pada hari ini.</p>
                    @endif
                </div>

                @if($status === 'draft' || $status === 'missing')
                    <a href="{{ route('dashboard', ['date' => $day['date']->toDateString()]) }}" class="hidden self-start text-xs font-semibold text-emerald-700 hover:underline sm:block print:hidden">Isi / kirim</a>
                @endif
            </article>
        @endforeach
    </div>

    {{-- Pratinjau foto ukuran penuh --}}
    <div x-show="img" x-cloak @keydown.escape.window="img = null" @click="img = null"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4 print:hidden">
        <img :src="img" alt="Foto kegiatan" class="max-h-[90vh] max-w-full rounded-xl">
        <button type="button" class="absolute right-5 top-5 text-2xl text-white" aria-label="Tutup"><i class="bi bi-x-lg"></i></button>
    </div>
</div>
@endsection