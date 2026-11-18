@extends('layouts.panel')
@section('title', 'Detail Pemesanan '.$pemesanan->kode_pemesanan.' - Travel Tour')

@section('panel-content')
@php
    $st = $pemesanan->status;
    $bisaDihapus = in_array($st, ['belum_terbayar', 'ditolak', 'kedaluwarsa'], true);
@endphp

<div class="tt-header">
    <div>
        <a href="{{ route('customer.pesanan.index') }}" class="tt-btn-outline tt-btn-sm">← Pesanan Saya</a>
        <h1 class="tt-title" style="margin-top:12px;">{{ $pemesanan->kode_pemesanan }}</h1>
    </div>
    <span class="tt-badge tone-{{ $pemesanan->statusTone() }}" style="font-size:13px;padding:8px 16px;">
        {{ $pemesanan->statusLabel() }}
    </span>
</div>

{{-- Progress Stepper --}}
<div class="order-steps">
    <div class="order-step done">1. Pemesanan Dibuat</div>
    <div class="order-step {{ in_array($st, ['menunggu_verifikasi_pembayaran','pembayaran_terverifikasi','diverifikasi']) ? 'done' : ($st === 'belum_terbayar' ? 'current' : 'rejected') }}">
        2. Pembayaran
    </div>
    <div class="order-step {{ in_array($st, ['pembayaran_terverifikasi','diverifikasi']) ? 'done' : ($st === 'menunggu_verifikasi_pembayaran' ? 'current' : ($st === 'ditolak' ? 'rejected' : '')) }}">
        3. Verifikasi Pembayaran
    </div>
    <div class="order-step {{ $st === 'diverifikasi' ? 'done' : ($st === 'pembayaran_terverifikasi' ? 'current' : ($st === 'ditolak' ? 'rejected' : '')) }}">
        4. Pemesanan Terverifikasi
    </div>
</div>

@if ($st === 'ditolak')
    <div class="tt-error">
        <strong>Alasan Penolakan:</strong> {{ $pemesanan->alasan_penolakan ?: 'Tidak memenuhi ketentuan verifikasi.' }}
    </div>
@elseif ($st === 'kedaluwarsa')
    <div class="tt-error">Batas waktu pembayaran telah berakhir. Kuota pesanan otomatis dilepas kembali.</div>
@endif

<div class="tt-grid2">
    <div class="tt-panel">
        <h2>Ringkasan Pemesanan</h2>
        <div class="kv-list">
            <div class="kv-row"><span>Paket Wisata</span><strong>{{ $pemesanan->paket?->nama_paket }}</strong></div>
            <div class="kv-row"><span>Tujuan</span><strong>{{ $pemesanan->paket?->tujuan }}</strong></div>
            <div class="kv-row"><span>Tanggal Berangkat</span><strong>{{ $pemesanan->tanggal_berangkat->translatedFormat('d F Y') }}</strong></div>
            <div class="kv-row"><span>Jumlah Peserta</span><strong>{{ $pemesanan->jumlah_peserta }} Orang</strong></div>
            <div class="kv-row"><span>Email Pemesan</span><strong>{{ $pemesanan->email_pemesan }}</strong></div>
            <div class="kv-row"><span>No. HP Pemesan</span><strong>{{ $pemesanan->telepon_pemesan }}</strong></div>
            <div class="kv-row"><span>No. Invoice</span><strong>{{ $pemesanan->pembayaran?->nomor_invoice }}</strong></div>
            <div class="kv-row"><span>Batas Pembayaran</span><strong>{{ $pemesanan->batas_waktu_pembayaran->format('d M Y H:i') }} WIB</strong></div>
        </div>

        <h3 style="margin:20px 0 10px;">Daftar Penumpang</h3>
        <ul style="margin:0;padding-left:20px;color:#334155;">
            @foreach ($pemesanan->penumpang as $orang)
                <li><strong>{{ $orang->nama_lengkap }}</strong> (Lahir: {{ $orang->tanggal_lahir->format('d/m/Y') }})</li>
            @endforeach
        </ul>
    </div>

    <div style="display:flex;flex-direction:column;gap:18px;">
        <div class="tt-panel tt-panel-blue">
            <h2>Rincian Biaya</h2>
            <div class="kv-row" style="border-color:rgba(255,255,255,.2);">
                <span style="color:rgba(255,255,255,.85);">{{ $pemesanan->paket?->nama_paket }} &times; {{ $pemesanan->jumlah_peserta }}</span>
                <strong style="color:#fff;">Rp {{ number_format($pemesanan->harga_satuan * $pemesanan->jumlah_peserta, 0, ',', '.') }}</strong>
            </div>
            <div class="kv-row" style="border-color:rgba(255,255,255,.2);">
                <span style="color:rgba(255,255,255,.85);">Biaya Admin</span>
                <strong style="color:#fff;">Rp {{ number_format($pemesanan->biaya_admin, 0, ',', '.') }}</strong>
            </div>
            <div class="cost-total">
                <span>Total Bayar</span>
                <span>Rp {{ number_format($pemesanan->total_pembayaran, 0, ',', '.') }}</span>
            </div>
        </div>

        <div class="tt-panel">
            <h2>Tindakan Pesanan</h2>
            @if ($st === 'belum_terbayar')
                <div style="display:grid;gap:10px;">
                    <a href="{{ route('customer.pesanan.bayar', $pemesanan) }}" class="tt-btn" style="padding:13px;">Lanjutkan ke Pembayaran (QRIS) ↗</a>
                    <a href="{{ route('customer.pesanan.edit', $pemesanan) }}" class="tt-btn-outline">Edit Data Penumpang</a>
                    <button type="button" class="tt-btn-danger" data-modal-open="modalHapusPesanan">Hapus Pemesanan</button>
                </div>
            @elseif ($st === 'diverifikasi')
                @if ($pemesanan->link_grup_wa)
                    <a href="{{ $pemesanan->link_grup_wa }}" target="_blank" rel="noopener noreferrer" class="tt-btn-wa">
                        Gabung Grup WhatsApp Perjalanan ↗
                    </a>
                @endif
                @if ($pemesanan->penugasan)
                    <div style="margin-top:16px;padding:14px;background:#F8FAFC;border-radius:12px;border:1px solid var(--border);">
                        <strong style="display:block;margin-bottom:6px;">Info Keberangkatan</strong>
                        <small style="display:block;color:var(--muted);">Driver: <b>{{ $pemesanan->penugasan->driver?->nama }}</b></small>
                        <small style="display:block;color:var(--muted);">Kendaraan: <b>{{ $pemesanan->penugasan->kendaraan?->merek }} ({{ $pemesanan->penugasan->kendaraan?->plat_nomor }})</b></small>
                        <small style="display:block;color:var(--muted);">Titik Kumpul: <b>{{ $pemesanan->penugasan->titik_kumpul }} ({{ substr($pemesanan->penugasan->jam_kumpul, 0, 5) }} WIB)</b></small>
                    </div>
                @endif
                <p style="text-align:center;font-weight:700;color:var(--blue-deep);margin:16px 0 0;">Happy Holiday &amp; Thank You! ✦</p>
            @elseif ($bisaDihapus)
                <p style="color:var(--muted);margin-top:0;">Pesanan ini tidak aktif. Anda dapat menghapusnya dari daftar pesanan.</p>
                <button type="button" class="tt-btn-danger" style="width:100%;" data-modal-open="modalHapusPesanan">Hapus Pemesanan</button>
            @else
                <p style="color:var(--muted);margin:0;">Bukti pembayaran Anda sedang dalam proses pemeriksaan oleh Staff Travel.</p>
            @endif
        </div>
    </div>
</div>

@if ($bisaDihapus)
    <div id="modalHapusPesanan" class="tt-modal" role="dialog" aria-modal="true" hidden>
        <div class="tt-modal-card">
            <button type="button" class="tt-modal-close" data-modal-close="modalHapusPesanan">&times;</button>
            <h3>Konfirmasi Hapus Pemesanan</h3>
            <p>Apakah Anda yakin ingin menghapus pemesanan <strong>{{ $pemesanan->kode_pemesanan }}</strong> ({{ $pemesanan->paket?->nama_paket }})?</p>
            <p style="color:var(--danger);font-size:13px;font-weight:700;">Tindakan ini tidak dapat dibatalkan dan kuota akan dilepas kembali.</p>
            <div class="tt-modal-actions">
                <button type="button" class="tt-btn-outline" data-modal-close="modalHapusPesanan">Batal</button>
                <form method="POST" action="{{ route('customer.pesanan.destroy', $pemesanan) }}">
                    @csrf @method('DELETE')
                    <button type="submit" class="tt-btn-danger">Konfirmasi Hapus</button>
                </form>
            </div>
        </div>
    </div>
@endif
@endsection