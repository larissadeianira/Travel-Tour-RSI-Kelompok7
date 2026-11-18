@extends('layouts.panel')
@section('title', 'Dashboard Staff - Travel Tour')

@section('panel-content')
<div class="tt-header">
    <div>
        <h1 class="tt-title">Dashboard Staff Travel</h1>
        <p class="tt-subtitle">Kelola verifikasi pembayaran, verifikasi pemesanan, data pelanggan, dan pelaporan transaksi.</p>
    </div>
    <div style="display:flex;gap:10px;flex-wrap:wrap;">
        <a href="{{ route('staff.pembayaran.index') }}" class="tt-btn">Verifikasi Pembayaran</a>
        <a href="{{ route('staff.pemesanan.index') }}" class="tt-btn-lime">Verifikasi Pemesanan</a>
    </div>
</div>

<div class="tt-stats">
    <div class="tt-stat"><strong>{{ $menungguPembayaran }}</strong><span>Menunggu Cek Pembayaran</span></div>
    <div class="tt-stat"><strong>{{ $menungguPemesanan }}</strong><span>Siap Verifikasi Pesanan</span></div>
    <div class="tt-stat"><strong>{{ $terverifikasiCount }}</strong><span>Pesanan Terverifikasi</span></div>
    <div class="tt-stat"><strong>{{ $pelangganCount }}</strong><span>Total Pelanggan</span></div>
</div>

<div class="tt-panel">
    <h2>Aktivitas Pemesanan Terbaru</h2>
    <div class="tt-table-wrap">
        <table class="tt-table white-rows">
            <thead>
                <tr><th>Kode</th><th>Pelanggan</th><th>Paket</th><th>Total</th><th>Status</th><th>Aksi</th></tr>
            </thead>
            <tbody>
                @forelse ($pesananTerbaru as $p)
                    <tr>
                        <td><strong>{{ $p->kode_pemesanan }}</strong></td>
                        <td>{{ $p->pemesan?->name }}</td>
                        <td>{{ $p->paket?->nama_paket }}</td>
                        <td>Rp {{ number_format($p->total_pembayaran, 0, ',', '.') }}</td>
                        <td><span class="tt-badge tone-{{ $p->statusTone() }}">{{ $p->statusLabel() }}</span></td>
                        <td><a href="{{ route('staff.pemesanan.show', $p) }}" class="tt-btn-outline tt-btn-sm">Buka →</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="tt-empty">Belum ada pesanan masuk.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection