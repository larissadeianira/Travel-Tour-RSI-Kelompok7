@extends('layouts.panel')
@section('title', 'Jadwal Tour Driver - Travel Tour')

@section('panel-content')
<div class="tt-header">
    <div>
        <h1 class="tt-title">Jadwal Tour Saya</h1>
        <p class="tt-subtitle">Driver: <strong>{{ $driver->nama }}</strong> · No. SIM: {{ $driver->nomor_sim }}</p>
    </div>
</div>

<div class="tt-table-wrap">
    <table class="tt-table white-rows">
        <thead>
            <tr><th>Kode</th><th>Paket Travel</th><th>Tujuan</th><th>Periode Tour</th><th>Kendaraan</th><th>Status</th><th>Aksi</th></tr>
        </thead>
        <tbody>
            @forelse ($jadwals as $item)
                <tr>
                    <td><strong>{{ $item->pemesanan?->kode_pemesanan }}</strong></td>
                    <td>{{ $item->pemesanan?->paket?->nama_paket }}</td>
                    <td>{{ $item->pemesanan?->paket?->tujuan }}</td>
                    <td>{{ $item->tanggal_mulai->format('d M') }} – {{ $item->tanggal_selesai->format('d M Y') }}</td>
                    <td>{{ $item->kendaraan?->plat_nomor }} ({{ $item->kendaraan?->merek }})</td>
                    <td><span class="tt-badge s-{{ $item->status }}">{{ ucfirst($item->status) }}</span></td>
                    <td><a class="tt-btn tt-btn-sm" href="{{ route('driver.jadwal.show', $item) }}">Detail →</a></td>
                </tr>
            @empty
                <tr><td colspan="7" class="tt-empty">Jadwal tour tidak tersedia.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
{{ $jadwals->links() }}
@endsection