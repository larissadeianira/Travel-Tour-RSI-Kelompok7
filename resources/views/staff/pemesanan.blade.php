@extends('layouts.panel')
@section('title', 'Verifikasi Pemesanan - Travel Tour')

@section('panel-content')
<div class="tt-header">
    <div>
        <h1 class="tt-title">Verifikasi Pemesanan</h1>
        <p class="tt-subtitle">Konfirmasi pemesanan yang pembayarannya telah terverifikasi dan sertakan link grup WhatsApp.</p>
    </div>
</div>

<form method="GET" class="tt-filter">
    <input name="cari" class="grow" value="{{ request('cari') }}" placeholder="Cari kode pesanan atau nama customer...">
    <select name="status" onchange="this.form.submit()">
        <option value="">Semua Status</option>
        <option value="pembayaran_terverifikasi" @selected(request('status') === 'pembayaran_terverifikasi')>Pembayaran Terverifikasi (Siap Konfirmasi)</option>
        <option value="menunggu_verifikasi_pembayaran" @selected(request('status') === 'menunggu_verifikasi_pembayaran')>Menunggu Cek Pembayaran</option>
        <option value="diverifikasi" @selected(request('status') === 'diverifikasi')>Diverifikasi</option>
        <option value="ditolak" @selected(request('status') === 'ditolak')>Ditolak</option>
    </select>
    <button type="submit" class="tt-btn">Cari</button>
    <a href="{{ route('staff.pemesanan.index') }}" class="tt-btn-outline">Reset</a>
</form>

<div class="tt-table-wrap">
    <table class="tt-table white-rows">
        <thead>
            <tr><th>Kode</th><th>Customer</th><th>Paket Travel</th><th>Berangkat</th><th>Status</th><th>Aksi</th></tr>
        </thead>
        <tbody>
            @forelse ($pesanans as $p)
                <tr>
                    <td><strong>{{ $p->kode_pemesanan }}</strong></td>
                    <td>{{ $p->pemesan?->name }}</td>
                    <td>{{ $p->paket?->nama_paket }} ({{ $p->jumlah_peserta }} org)</td>
                    <td>{{ $p->tanggal_berangkat->format('d M Y') }}</td>
                    <td><span class="tt-badge tone-{{ $p->statusTone() }}">{{ $p->statusLabel() }}</span></td>
                    <td>
                        <a href="{{ route('staff.pemesanan.show', $p) }}" class="{{ $p->status === 'pembayaran_terverifikasi' ? 'tt-btn' : 'tt-btn-outline' }} tt-btn-sm">
                            {{ $p->status === 'pembayaran_terverifikasi' ? 'Konfirmasi' : 'Lihat' }}
                        </a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="tt-empty">Belum ada data pemesanan.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
{{ $pesanans->links() }}
@endsection