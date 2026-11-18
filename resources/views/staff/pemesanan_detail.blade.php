@extends('layouts.panel')
@section('title', 'Detail Verifikasi Pemesanan - Travel Tour')

@section('panel-content')
@php $terverif = $pemesanan->status === 'pembayaran_terverifikasi'; @endphp

<div class="tt-header">
    <div>
        <a href="{{ route('staff.pemesanan.index') }}" class="tt-btn-outline tt-btn-sm">← Kembali ke Verifikasi Pemesanan</a>
        <h1 class="tt-title" style="margin-top:12px;">Verifikasi Pemesanan — {{ $pemesanan->kode_pemesanan }}</h1>
    </div>
    <span class="tt-badge tone-{{ $pemesanan->statusTone() }}">{{ $pemesanan->statusLabel() }}</span>
</div>

<div class="tt-grid2">
    <div class="tt-panel">
        <h2>Data Pemesanan &amp; Penumpang</h2>
        <div class="kv-list" style="margin-bottom:18px;">
            <div class="kv-row"><span>Paket Wisata</span><strong>{{ $pemesanan->paket?->nama_paket }}</strong></div>
            <div class="kv-row"><span>Jadwal Berangkat</span><strong>{{ $pemesanan->tanggal_berangkat->translatedFormat('d F Y') }}</strong></div>
            <div class="kv-row"><span>Jumlah Peserta</span><strong>{{ $pemesanan->jumlah_peserta }} Orang</strong></div>
            <div class="kv-row"><span>Nama Customer</span><strong>{{ $pemesanan->pemesan?->name }}</strong></div>
            <div class="kv-row"><span>Email Pemesan</span><strong>{{ $pemesanan->email_pemesan }}</strong></div>
            <div class="kv-row"><span>No. HP Pemesan</span><strong>{{ $pemesanan->telepon_pemesan }}</strong></div>
            <div class="kv-row"><span>Total Bayar</span><strong>Rp {{ number_format($pemesanan->total_pembayaran, 0, ',', '.') }}</strong></div>
        </div>
        <h3 style="margin:0 0 8px;">Manifes Penumpang</h3>
        <ul style="margin:0;padding-left:20px;">
            @foreach ($pemesanan->penumpang as $orang)
                <li>{{ $orang->nama_lengkap }} ({{ $orang->tanggal_lahir->format('d/m/Y') }})</li>
            @endforeach
        </ul>
    </div>

    <div class="tt-panel">
        <h2>Keputusan Verifikasi Pemesanan</h2>
        @if ($pemesanan->status === 'diverifikasi')
            <div class="tt-notice">Pemesanan ini sudah diverifikasi. Link Grup WA: <a href="{{ $pemesanan->link_grup_wa }}" target="_blank" style="text-decoration:underline;">{{ $pemesanan->link_grup_wa }}</a></div>
        @elseif ($pemesanan->status === 'ditolak')
            <div class="tt-error">Pemesanan ditolak: {{ $pemesanan->alasan_penolakan }}</div>
        @else
            <form method="POST" action="{{ route('staff.pesanan', $pemesanan) }}">
                @csrf
                <div class="tt-field">
                    <label for="link_grup_wa">Link Group WhatsApp (Wajib untuk Verifikasi)</label>
                    <input id="link_grup_wa" name="link_grup_wa" type="url" value="{{ old('link_grup_wa', $pemesanan->link_grup_wa) }}" placeholder="https://chat.whatsapp.com/..." @disabled(!$terverif)>
                </div>
                @unless ($terverif)
                    <div class="tt-warning">Tombol Verifikasi Pemesanan aktif setelah pembayaran berstatus <strong>Terverifikasi</strong>.</div>
                @endunless
                <div style="display:flex;gap:10px;flex-wrap:wrap;">
                    <button type="button" class="tt-btn-danger" data-modal-open="modalTolakPesanan">Tolak Pemesanan</button>
                    <button type="submit" name="keputusan" value="diverifikasi" class="tt-btn" @disabled(!$terverif)>✓ Verifikasi Pemesanan</button>
                </div>
            </form>
        @endif
    </div>
</div>

<div id="modalTolakPesanan" class="tt-modal" role="dialog" aria-modal="true" hidden>
    <div class="tt-modal-card">
        <button type="button" class="tt-modal-close" data-modal-close="modalTolakPesanan">&times;</button>
        <h3>Apakah Anda Yakin <span style="color:var(--danger);">Menolak Pemesanan?</span></h3>
        <form method="POST" action="{{ route('staff.pesanan', $pemesanan) }}">
            @csrf
            <input type="hidden" name="keputusan" value="ditolak">
            <div class="tt-field">
                <label for="alasanTolak">Alasan Penolakan</label>
                <textarea id="alasanTolak" name="alasan" rows="3" required placeholder="Tuliskan alasan penolakan yang jelas untuk customer..."></textarea>
            </div>
            <div class="tt-modal-actions">
                <button type="button" class="tt-btn-outline" data-modal-close="modalTolakPesanan">&lt; Kembali</button>
                <button type="submit" class="tt-btn-danger">Iya, Saya Menolak</button>
            </div>
        </form>
    </div>
</div>
@endsection