@extends('layouts.panel')
@section('title', 'Pesanan Saya - Travel Tour')

@section('panel-content')
<div class="tt-header">
    <div>
        <h1 class="tt-title">Pesanan Saya</h1>
        <p class="tt-subtitle">Pantau status pemesanan, lakukan pembayaran, atau ubah data penumpang sebelum dibayar.</p>
    </div>
    <a href="{{ route('travel.katalog') }}" class="tt-btn-lime">+ Pilih Paket Wisata</a>
</div>

<div class="tt-table-wrap">
    <table class="tt-table white-rows">
        <thead>
            <tr>
                <th>Kode</th>
                <th>Paket Wisata</th>
                <th>Berangkat</th>
                <th>Peserta</th>
                <th>Total</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($pesanans as $p)
                <tr>
                    <td><strong>{{ $p->kode_pemesanan }}</strong></td>
                    <td>{{ $p->paket?->nama_paket }}</td>
                    <td>{{ $p->tanggal_berangkat->translatedFormat('d M Y') }}</td>
                    <td>{{ $p->jumlah_peserta }} orang</td>
                    <td>Rp {{ number_format($p->total_pembayaran, 0, ',', '.') }}</td>
                    <td><span class="tt-badge tone-{{ $p->statusTone() }}">{{ $p->statusLabel() }}</span></td>
                    <td>
                        <div class="tt-actions">
                            @if ($p->status === 'belum_terbayar')
                                <a href="{{ route('customer.pesanan.bayar', $p) }}" class="tt-btn tt-btn-sm">Bayar</a>
                            @endif
                            <a href="{{ route('customer.pesanan.show', $p) }}" class="tt-btn-outline tt-btn-sm">Detail →</a>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="tt-empty">Belum ada pemesanan. Silakan pilih paket wisata pada katalog.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{ $pesanans->links() }}
@endsection