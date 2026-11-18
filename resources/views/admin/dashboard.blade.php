@extends('layouts.panel')
@section('title', 'Dashboard Admin - Travel Tour')

@section('panel-content')
<div class="tt-header">
    <div>
        <h1 class="tt-title">Dashboard Admin</h1>
        <p class="tt-subtitle">Ringkasan operasional paket travel, armada kendaraan, driver, dan penugasan.</p>
    </div>
    <a href="{{ route('admin.penugasan.index') }}" class="tt-btn-lime">Kelola Penugasan ↗</a>
</div>

<div class="tt-stats">
    <div class="tt-stat"><strong>{{ $penugasanBerjalan + $penugasanTerjadwal }}</strong><span>Penugasan Aktif</span></div>
    <div class="tt-stat"><strong>{{ $paketCount }}</strong><span>Paket Travel Tersedia</span></div>
    <div class="tt-stat"><strong>{{ $driverAktifCount }}</strong><span>Driver Aktif</span></div>
    <div class="tt-stat"><strong>{{ $tersediaCount }}</strong><span>Kendaraan Tersedia</span></div>
</div>

@php
    $circ = 2 * pi() * 48; // r=48 => ~301.59
    $denom = max(1, $penugasanTotal);
    $lenSelesai = $circ * ($penugasanSelesai / $denom);
    $lenBerjalan = $circ * ($penugasanBerjalan / $denom);
    $lenTerjadwal = $circ * ($penugasanTerjadwal / $denom);
@endphp

<div class="tt-grid2">
    <section class="tt-panel tt-panel-blue">
        <h2>Status Penugasan</h2>
        <div class="tt-donut-wrap">
            <div class="tt-donut">
                <svg viewBox="0 0 120 120">
                    <circle cx="60" cy="60" r="48" fill="none" stroke="rgba(255,255,255,.18)" stroke-width="16"/>
                    @if ($penugasanTotal > 0)
                        <circle cx="60" cy="60" r="48" fill="none" stroke="#5FD76D" stroke-width="16"
                            stroke-dasharray="{{ $lenSelesai }} {{ $circ }}" stroke-dashoffset="0"/>
                        <circle cx="60" cy="60" r="48" fill="none" stroke="#E3FF3B" stroke-width="16"
                            stroke-dasharray="{{ $lenBerjalan }} {{ $circ }}" stroke-dashoffset="-{{ $lenSelesai }}"/>
                        <circle cx="60" cy="60" r="48" fill="none" stroke="#93C5FD" stroke-width="16"
                            stroke-dasharray="{{ $lenTerjadwal }} {{ $circ }}" stroke-dashoffset="-{{ $lenSelesai + $lenBerjalan }}"/>
                    @endif
                </svg>
                <div class="tt-donut-center">
                    <b>{{ $penugasanTotal }}</b>
                    <small>Penugasan</small>
                </div>
            </div>
            <div class="tt-legend-list">
                <div class="tt-legend-item"><i class="tt-dot" style="background:#5FD76D;"></i> Selesai: <b>{{ $penugasanSelesai }}</b></div>
                <div class="tt-legend-item"><i class="tt-dot" style="background:#E3FF3B;"></i> Berjalan: <b>{{ $penugasanBerjalan }}</b></div>
                <div class="tt-legend-item"><i class="tt-dot" style="background:#93C5FD;"></i> Terjadwal: <b>{{ $penugasanTerjadwal }}</b></div>
            </div>
        </div>
    </section>

    <section class="tt-panel tt-panel-blue">
        <h2>Ketersediaan Driver &amp; Kendaraan</h2>
        <div class="tt-meter-row">
            <div class="tt-meter-label"><span>Driver Aktif</span><b>{{ $driverAktifCount }} / {{ $driverCount }}</b></div>
            <div class="tt-meter"><i style="width:{{ $driverCount ? round($driverAktifCount * 100 / $driverCount) : 0 }}%"></i></div>
        </div>
        <div class="tt-meter-row">
            <div class="tt-meter-label"><span>Kendaraan Tersedia</span><b>{{ $tersediaCount }} / {{ $kendaraanCount }}</b></div>
            <div class="tt-meter"><i style="width:{{ $kendaraanCount ? round($tersediaCount * 100 / $kendaraanCount) : 0 }}%"></i></div>
        </div>
        <div class="tt-meter-row">
            <div class="tt-meter-label"><span>Kendaraan Digunakan</span><b>{{ $digunakanCount }} / {{ $kendaraanCount }}</b></div>
            <div class="tt-meter"><i style="width:{{ $kendaraanCount ? round($digunakanCount * 100 / $kendaraanCount) : 0 }}%;background:#FFB703;"></i></div>
        </div>
        <div class="tt-meter-row" style="margin-bottom:0;">
            <div class="tt-meter-label"><span>Kendaraan Perawatan</span><b>{{ $perawatanCount }} / {{ $kendaraanCount }}</b></div>
            <div class="tt-meter"><i style="width:{{ $kendaraanCount ? round($perawatanCount * 100 / $kendaraanCount) : 0 }}%;background:#E37070;"></i></div>
        </div>
    </section>

    <section class="tt-panel tt-panel-blue">
        <h2>Paket Travel Terpopuler</h2>
        @forelse ($paketTerpopuler as $p)
            <div class="tt-kv">
                <span>{{ $p->nama_paket }}</span>
                <span class="tt-kv-pill">{{ (int) ($p->terjual ?? 0) }} Terjual</span>
            </div>
        @empty
            <p style="margin:0;">Belum ada paket wisata.</p>
        @endforelse
    </section>

    <section class="tt-panel tt-panel-blue">
        <h2>Aktivitas Terbaru</h2>
        @forelse ($aktivitas as $a)
            <div class="tt-kv">
                <div>
                    <strong style="display:block;font-size:13px;">{{ $a['judul'] }}</strong>
                    <small style="color:rgba(255,255,255,.78);">{{ $a['sub'] }}</small>
                </div>
                <small style="color:var(--lime);white-space:nowrap;">{{ $a['waktu']?->diffForHumans() }}</small>
            </div>
        @empty
            <p style="margin:0;">Belum ada aktivitas tercatat.</p>
        @endforelse
    </section>
</div>
@endsection