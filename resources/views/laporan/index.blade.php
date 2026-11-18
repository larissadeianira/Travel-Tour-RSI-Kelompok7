@extends('layouts.panel')
@section('title', 'Laporan Transaksi - Travel Tour')

@section('panel-content')
<div class="tt-header">
    <div>
        <span class="eyebrow">{{ strtoupper($mode) }} / LAPORAN TRANSAKSI</span>
        <h1 class="tt-title">Laporan Transaksi</h1>
        <p class="tt-subtitle">Rekapitulasi transaksi dengan pembayaran terverifikasi berdasarkan periode.</p>
    </div>
</div>

<form method="GET" action="{{ $mode === 'staff' ? route('staff.laporan.index') : route('manager.laporan.index') }}" class="lap-filter">
    <label>Jenis Periode
        <select name="jenis" id="jenisPeriode">
            <option value="harian" @selected($jenis === 'harian')>Harian</option>
            <option value="mingguan" @selected($jenis === 'mingguan')>Mingguan</option>
            <option value="bulanan" @selected($jenis === 'bulanan')>Bulanan</option>
            <option value="custom" @selected($jenis === 'custom')>Custom</option>
        </select>
    </label>
    <label>Tanggal Awal <input type="date" id="tglAwal" name="dari" value="{{ $dari }}" required></label>
    <label>Tanggal Akhir <input type="date" id="tglAkhir" name="sampai" value="{{ $sampai }}" required></label>
    <button type="submit" class="tt-btn">Tampilkan</button>
</form>

<div class="tt-stats tt-stats-3">
    <div class="tt-stat"><strong>{{ number_format($jumlah, 0, ',', '.') }}</strong><span>Jumlah Pemesanan Lunas</span></div>
    <div class="tt-stat"><strong>{{ number_format($totalPeserta, 0, ',', '.') }}</strong><span>Total Peserta</span></div>
    <div class="tt-stat"><strong>Rp {{ number_format($total, 0, ',', '.') }}</strong><span>Total Transaksi</span></div>
</div>

<div class="lap-toolbar">
    <h2>Daftar Transaksi</h2>
    <div class="lap-buttons">
        @if ($jumlah > 0 && $mode === 'staff')
            <form action="{{ route('staff.laporan.kirim') }}" method="POST">
                @csrf
                <input type="hidden" name="jenis" value="{{ $jenis }}">
                <input type="hidden" name="dari" value="{{ $dari }}">
                <input type="hidden" name="sampai" value="{{ $sampai }}">
                <button type="submit" class="tt-btn-lime">Kirim Laporan ke Manager ↗</button>
            </form>
        @elseif ($jumlah > 0 && $mode === 'manager')
            <a class="tt-btn" href="{{ route('manager.laporan.pdf', ['jenis'=>$jenis, 'dari'=>$dari, 'sampai'=>$sampai]) }}">Ekspor PDF ↓</a>
            <a class="tt-btn-lime" href="{{ route('manager.laporan.excel', ['jenis'=>$jenis, 'dari'=>$dari, 'sampai'=>$sampai]) }}">Ekspor Excel ↓</a>
        @endif
    </div>
</div>

<div class="tt-table-wrap">
    <table class="tt-table">
        <thead>
            <tr>
                <th>No</th><th>Tanggal</th><th>Kode</th><th>Customer</th><th>Paket</th><th>Peserta</th><th>Metode</th><th>Invoice</th><th>Nominal</th><th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($transaksi as $index => $p)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $p->tanggal_bayar?->format('d/m/Y') }}</td>
                    <td><strong>{{ $p->pemesanan?->kode_pemesanan }}</strong></td>
                    <td>{{ $p->pemesanan?->pemesan?->name }}</td>
                    <td>{{ $p->pemesanan?->paket?->nama_paket }}</td>
                    <td>{{ $p->pemesanan?->jumlah_peserta }}</td>
                    <td>{{ $p->metode_pembayaran ?? 'QRIS' }}</td>
                    <td>{{ $p->nomor_invoice }}</td>
                    <td>Rp {{ number_format($p->nominal, 0, ',', '.') }}</td>
                    <td><span class="tt-badge s-terverifikasi">Lunas</span></td>
                </tr>
            @empty
                <tr><td colspan="10" class="tt-empty">Laporan transaksi tidak tersedia pada periode ini.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@if ($jumlah > 0)
    <div class="lap-total-bar">
        <span>Total Transaksi Periode ({{ $dari }} s.d. {{ $sampai }})</span>
        <span>Rp {{ number_format($total, 0, ',', '.') }}</span>
    </div>
@endif

@if ($mode === 'manager')
    <section class="tt-panel" style="margin-top:28px;">
        <h2>Riwayat Laporan Dikirim Staff</h2>
        <div class="tt-table-wrap">
            <table class="tt-table white-rows">
                <thead>
                    <tr><th>Waktu Kirim</th><th>Nama Staff</th><th>Periode</th><th>Jumlah Transaksi</th><th>Total Saat Dikirim</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                    @forelse ($laporanTerkirim as $item)
                        <tr>
                            <td>{{ $item->created_at->format('d/m/Y H:i') }}</td>
                            <td>{{ $item->pengirim?->name }}</td>
                            <td>{{ $item->periode_awal->format('d/m/Y') }} – {{ $item->periode_akhir->format('d/m/Y') }}</td>
                            <td>{{ $item->jumlah_transaksi }} transaksi</td>
                            <td>Rp {{ number_format($item->total_nominal, 0, ',', '.') }}</td>
                            <td>
                                <a class="tt-btn tt-btn-sm" href="{{ route('manager.laporan.index', ['jenis'=>'custom', 'dari'=>$item->periode_awal->format('Y-m-d'), 'sampai'=>$item->periode_akhir->format('Y-m-d')]) }}">
                                    Tinjau →
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="tt-empty">Belum ada laporan yang dikirim Staff.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endif
@endsection

@push('scripts')
<script>
document.getElementById('jenisPeriode').addEventListener('change', function () {
    const end = new Date();
    const start = new Date();
    if (this.value === 'harian') {
        // hari ini
    } else if (this.value === 'mingguan') {
        start.setDate(end.getDate() - 6);
    } else if (this.value === 'bulanan') {
        start.setDate(1);
    } else {
        return;
    }
    const fmt = d => `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
    document.getElementById('tglAwal').value = fmt(start);
    document.getElementById('tglAkhir').value = fmt(end);
});
</script>
@endpush