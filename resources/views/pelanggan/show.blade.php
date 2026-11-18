@extends('layouts.panel')
@section('title', 'Detail Pelanggan - Travel Tour')

@section('panel-content')
@php
    $lengkap = $pelanggan->nomor_identitas && $pelanggan->dokumen_identitas;
    $isPdf = $pelanggan->dokumen_identitas && str_ends_with(strtolower($pelanggan->dokumen_identitas), '.pdf');
@endphp

<div class="tt-header">
    <div>
        <a href="{{ route('pelanggan.index') }}" class="tt-btn-outline tt-btn-sm">← Kembali ke Data Pelanggan</a>
        <h1 class="tt-title" style="margin-top:12px;">Detail Data Pelanggan</h1>
    </div>
    <span class="tt-badge {{ $lengkap ? 'tone-success' : 'tone-warning' }}">{{ $lengkap ? 'Identitas Lengkap' : 'Belum Lengkap' }}</span>
</div>

<div class="tt-grid2" style="margin-bottom:20px;">
    <div class="tt-panel">
        <h2>Profil &amp; Identitas</h2>
        <div class="kv-list">
            <div class="kv-row"><span>Nama Lengkap</span><strong>{{ $pelanggan->name }}</strong></div>
            <div class="kv-row"><span>Email</span><strong>{{ $pelanggan->email }}</strong></div>
            <div class="kv-row"><span>Nomor HP</span><strong>{{ $pelanggan->nomor_telepon ?: 'Belum diisi' }}</strong></div>
            <div class="kv-row"><span>Nomor Identitas</span><strong>{{ $pelanggan->nomor_identitas ?: 'Belum diisi' }}</strong></div>
            <div class="kv-row"><span>Kelengkapan Berkas</span><strong>{{ $lengkap ? 'Lengkap / Terverifikasi' : 'Belum Lengkap' }}</strong></div>
        </div>
    </div>

    <div class="tt-panel">
        <h2>Dokumen Identitas (Privat)</h2>
        @if ($pelanggan->dokumen_identitas)
            @if ($isPdf)
                <a href="{{ route('pelanggan.dokumen', $pelanggan->id) }}" target="_blank" class="tt-btn">Lihat Dokumen Identitas (PDF) ↗</a>
            @else
                <a href="{{ route('pelanggan.dokumen', $pelanggan->id) }}" target="_blank">
                    <img src="{{ route('pelanggan.dokumen', $pelanggan->id) }}" alt="Dokumen identitas {{ $pelanggan->name }}" style="width:100%;max-height:240px;object-fit:contain;border-radius:12px;border:1px solid var(--border);background:#F8FAFC;">
                </a>
            @endif
        @else
            <p style="color:var(--muted);margin:0;">Dokumen identitas belum diunggah oleh pelanggan.</p>
        @endif
    </div>
</div>

<div class="tt-panel">
    <h2>Riwayat Pemesanan Pelanggan</h2>
    <div class="tt-table-wrap">
        <table class="tt-table white-rows">
            <thead>
                <tr><th>Kode</th><th>Paket Travel</th><th>Keberangkatan</th><th>Peserta</th><th>Total</th><th>Status</th></tr>
            </thead>
            <tbody>
                @forelse ($pelanggan->pemesanan as $r)
                    <tr>
                        <td><strong>{{ $r->kode_pemesanan }}</strong></td>
                        <td>{{ $r->paket?->nama_paket }}</td>
                        <td>{{ $r->tanggal_berangkat?->translatedFormat('d M Y') }}</td>
                        <td>{{ $r->jumlah_peserta }} orang</td>
                        <td>Rp {{ number_format($r->total_pembayaran, 0, ',', '.') }}</td>
                        <td><span class="tt-badge tone-{{ $r->statusTone() }}">{{ $r->statusLabel() }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="tt-empty">Pelanggan ini belum memiliki riwayat pemesanan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection