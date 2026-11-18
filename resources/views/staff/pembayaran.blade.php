@extends('layouts.panel')
@section('title', 'Verifikasi Pembayaran - Travel Tour')

@section('panel-content')
<div class="tt-header">
    <div>
        <h1 class="tt-title">Verifikasi Pembayaran</h1>
        <p class="tt-subtitle">Periksa bukti transfer customer dan konfirmasi pelunasan sebelum verifikasi pemesanan.</p>
    </div>
</div>

<form method="GET" class="tt-filter">
    <input name="cari" class="grow" value="{{ request('cari') }}" placeholder="Cari kode pesanan atau nama customer...">
    <select name="status" onchange="this.form.submit()">
        <option value="">Semua Status Pembayaran</option>
        <option value="menunggu_verifikasi" @selected(request('status') === 'menunggu_verifikasi')>Menunggu Verifikasi</option>
        <option value="terverifikasi" @selected(request('status') === 'terverifikasi')>Terverifikasi</option>
        <option value="ditolak" @selected(request('status') === 'ditolak')>Ditolak</option>
        <option value="belum_dibayar" @selected(request('status') === 'belum_dibayar')>Belum Dibayar</option>
    </select>
    <button type="submit" class="tt-btn">Cari</button>
    <a href="{{ route('staff.pembayaran.index') }}" class="tt-btn-outline">Reset</a>
</form>

<div class="tt-table-wrap">
    <table class="tt-table white-rows">
        <thead>
            <tr><th>Kode</th><th>Customer</th><th>Invoice</th><th>Total</th><th>Status Pembayaran</th><th>Aksi</th></tr>
        </thead>
        <tbody>
            @forelse ($pesanans as $p)
                @php $pay = $p->pembayaran; @endphp
                <tr>
                    <td><strong>{{ $p->kode_pemesanan }}</strong></td>
                    <td>{{ $p->pemesan?->name }}</td>
                    <td>{{ $pay?->nomor_invoice }}</td>
                    <td>Rp {{ number_format($p->total_pembayaran, 0, ',', '.') }}</td>
                    <td><span class="tt-badge s-{{ $pay?->status ?? 'belum_dibayar' }}">{{ $pay?->statusLabel() ?? 'Belum Dibayar' }}</span></td>
                    <td>
                        <button type="button" class="tt-btn tt-btn-sm" data-modal-open="modalBayar-{{ $p->id }}">Lihat Bukti</button>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="tt-empty">Belum ada data pembayaran.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
{{ $pesanans->links() }}

@foreach ($pesanans as $p)
    @php
        $pay = $p->pembayaran;
        $siap = $p->status === 'menunggu_verifikasi_pembayaran' && $pay && $pay->status === 'menunggu_verifikasi';
        $isPdf = $pay && str_ends_with(strtolower((string) $pay->bukti_pembayaran), '.pdf');
    @endphp
    <div id="modalBayar-{{ $p->id }}" class="tt-modal" role="dialog" aria-modal="true" hidden>
        <div class="tt-modal-card" style="width:min(560px,100%);">
            <button type="button" class="tt-modal-close" data-modal-close="modalBayar-{{ $p->id }}">&times;</button>
            <h3>Detail Pembayaran — {{ $p->kode_pemesanan }}</h3>
            <p style="margin:0 0 14px;color:var(--muted);">
                {{ $p->pemesan?->name }} · Invoice <b>{{ $pay?->nomor_invoice }}</b> · <b>Rp {{ number_format($p->total_pembayaran, 0, ',', '.') }}</b>
            </p>

            @if ($pay && $pay->bukti_pembayaran)
                <div style="background:#F8FAFC;border:1px solid var(--border);border-radius:12px;padding:12px;text-align:center;margin-bottom:16px;">
                    @if ($isPdf)
                        <a href="{{ route('staff.bukti', $p) }}" target="_blank" class="tt-btn">Buka File Bukti (PDF) ↗</a>
                    @else
                        <img src="{{ route('staff.bukti', $p) }}" alt="Bukti {{ $p->kode_pemesanan }}" style="max-width:100%;max-height:320px;border-radius:8px;">
                    @endif
                </div>
            @else
                <div class="tt-warning">Customer belum mengunggah bukti pembayaran.</div>
            @endif

            @if ($siap)
                <form method="POST" action="{{ route('staff.pembayaran', $p) }}" style="margin-bottom:10px;">
                    @csrf
                    <input type="hidden" name="keputusan" value="terverifikasi">
                    <button type="submit" class="tt-btn-lime" style="width:100%;padding:12px;">✓ Verifikasi Pembayaran (Konfirmasi Pelunasan)</button>
                </form>

                <details style="margin-top:8px;">
                    <summary style="cursor:pointer;color:var(--danger);font-weight:700;font-size:13px;">Tolak Pembayaran...</summary>
                    <form method="POST" action="{{ route('staff.pembayaran', $p) }}" style="margin-top:10px;">
                        @csrf
                        <input type="hidden" name="keputusan" value="ditolak">
                        <div class="tt-field">
                            <label>Alasan Penolakan</label>
                            <textarea name="alasan" rows="2" required placeholder="Contoh: Nominal tidak sesuai atau bukti buram"></textarea>
                        </div>
                        <button type="submit" class="tt-btn-danger" style="width:100%;">Tolak Pembayaran</button>
                    </form>
                </details>
            @endif
        </div>
    </div>
@endforeach
@endsection