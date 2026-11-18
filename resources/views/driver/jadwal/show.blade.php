@extends('layouts.panel')
@section('title', 'Detail Jadwal Tour - Travel Tour')

@section('panel-content')
@php
    $order = $penugasan->pemesanan;
    $paket = $order?->paket;
    $cust = $order?->pemesan;
    $car = $penugasan->kendaraan;
@endphp

<div class="tt-header">
    <div>
        <a href="{{ route('driver.jadwal.index') }}" class="tt-btn-outline tt-btn-sm">← Kembali ke Jadwal Tour</a>
        <h1 class="tt-title" style="margin-top:12px;">Detail Jadwal Tour</h1>
    </div>
    <span class="tt-badge s-{{ $penugasan->status }}" style="font-size:13px;padding:8px 14px;">{{ ucfirst($penugasan->status) }}</span>
</div>

<div class="driver-detail-grid">
    <section class="tt-panel tt-panel-blue">
        <h2>Informasi Tour</h2>
        <div class="driver-info-row"><span>Kode Pesanan</span><strong>{{ $order?->kode_pemesanan }}</strong></div>
        <div class="driver-info-row"><span>Paket Wisata</span><strong>{{ $paket?->nama_paket }}</strong></div>
        <div class="driver-info-row"><span>Tujuan</span><strong>{{ $paket?->tujuan }}</strong></div>
        <div class="driver-info-row"><span>Periode Tour</span><strong>{{ $penugasan->tanggal_mulai->format('d M Y') }} – {{ $penugasan->tanggal_selesai->format('d M Y') }}</strong></div>
    </section>

    <section class="tt-panel tt-panel-blue">
        <h2>Data Driver</h2>
        <div class="driver-info-row"><span>Nama Driver</span><strong>{{ $driver->nama }}</strong></div>
        <div class="driver-info-row"><span>No. Telepon</span><strong>{{ $driver->nomor_telepon }}</strong></div>
        <div class="driver-info-row"><span>No. SIM</span><strong>{{ $driver->nomor_sim }}</strong></div>
    </section>

    <section class="tt-panel tt-panel-blue">
        <h2>Data Pemesan</h2>
        <div class="driver-info-row"><span>Nama Pelanggan</span><strong>{{ $cust?->name ?? '-' }}</strong></div>
        <div class="driver-info-row"><span>No. Telepon</span><strong>{{ $order?->telepon_pemesan ?? '-' }}</strong></div>
        <div class="driver-info-row"><span>Email</span><strong>{{ $order?->email_pemesan ?? '-' }}</strong></div>
        <div class="driver-info-row"><span>Jumlah Peserta</span><strong>{{ $order?->jumlah_peserta }} orang</strong></div>
    </section>

    <section class="tt-panel tt-panel-blue">
        <h2>Kendaraan Bertugas</h2>
        <div class="driver-info-row"><span>Plat Nomor</span><strong>{{ $car?->plat_nomor }}</strong></div>
        <div class="driver-info-row"><span>Merek &amp; Jenis</span><strong>{{ $car?->merek }} ({{ $car?->jenis }})</strong></div>
        <div class="driver-info-row"><span>Kapasitas</span><strong>{{ $car?->kapasitas }} kursi</strong></div>
    </section>
</div>

<section class="tt-panel" style="margin-top:18px;">
    <h2>Detail Perjalanan &amp; Titik Kumpul</h2>
    <div class="kv-list">
        <div class="kv-row"><span>Titik Kumpul</span><strong>{{ $penugasan->titik_kumpul }}</strong></div>
        <div class="kv-row"><span>Jam Kumpul</span><strong>{{ substr($penugasan->jam_kumpul, 0, 5) }} WIB</strong></div>
        <div class="kv-row"><span>Durasi Paket</span><strong>{{ $paket?->durasi_hari }} Hari</strong></div>
        <div class="kv-row"><span>Catatan Admin</span><strong>{{ $penugasan->catatan ?: '-' }}</strong></div>
    </div>
</section>
@endsection