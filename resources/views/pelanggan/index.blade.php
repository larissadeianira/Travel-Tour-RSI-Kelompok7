@extends('layouts.panel')
@section('title', 'Data Pelanggan - Travel Tour')

@section('panel-content')
<div class="tt-header">
    <div>
        <h1 class="tt-title">Data Pelanggan</h1>
        <p class="tt-subtitle">Pemeriksaan profil, kelengkapan identitas, dan riwayat pemesanan pelanggan (hanya-baca).</p>
    </div>
</div>

<form method="GET" action="{{ route('pelanggan.cari') }}" class="tt-filter">
    <input name="keyword" class="grow" value="{{ $keyword }}" placeholder="Cari nama, email, atau nomor HP pelanggan...">
    <button type="submit" class="tt-btn">Cari</button>
    <a href="{{ route('pelanggan.index') }}" class="tt-btn-outline">Reset</a>
</form>

@if ($keyword !== '' && $pelanggan->isEmpty())
    <div class="tt-warning" role="status">Data Pelanggan Tidak Ditemukan.</div>
@else
    <div class="tt-table-wrap">
        <table class="tt-table white-rows">
            <thead>
                <tr><th>Nama Lengkap</th><th>Email</th><th>No. HP</th><th>Kelengkapan</th><th>Pesanan</th><th>Aksi</th></tr>
            </thead>
            <tbody>
                @forelse ($pelanggan as $orang)
                    @php $lengkap = $orang->nomor_identitas && $orang->dokumen_identitas; @endphp
                    <tr>
                        <td><strong>{{ $orang->name }}</strong></td>
                        <td>{{ $orang->email }}</td>
                        <td>{{ $orang->nomor_telepon ?: '-' }}</td>
                        <td>
                            <span class="tt-badge {{ $lengkap ? 'tone-success' : 'tone-warning' }}">
                                {{ $lengkap ? 'Lengkap' : 'Belum Lengkap' }}
                            </span>
                        </td>
                        <td>{{ $orang->pemesanan_count }} transaksi</td>
                        <td><a href="{{ route('pelanggan.detail', $orang->id) }}" class="tt-btn tt-btn-sm">Detail →</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="tt-empty">Belum ada pelanggan terdaftar.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endif
@endsection