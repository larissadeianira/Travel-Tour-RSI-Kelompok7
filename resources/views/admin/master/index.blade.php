@extends('layouts.panel')
@section('title', 'Data Master '.$judul.' - Travel Tour')

@section('panel-content')
<div class="tt-header">
    <div>
        <h1 class="tt-title">Data Master</h1>
        <p class="tt-subtitle">Kelola data terpusat paket travel, driver, dan armada kendaraan.</p>
    </div>
    <a class="tt-btn" href="{{ route('admin.'.$jenis.'.create') }}">+ Tambah {{ $judul }}</a>
</div>

<div class="tt-toolbar">
    <div class="tt-tabs">
        <a class="{{ $jenis==='paket' ? 'active' : '' }}" href="{{ route('admin.paket.index') }}">Paket Travel</a>
        <a class="{{ $jenis==='driver' ? 'active' : '' }}" href="{{ route('admin.driver.index') }}">Driver</a>
        <a class="{{ $jenis==='kendaraan' ? 'active' : '' }}" href="{{ route('admin.kendaraan.index') }}">Kendaraan</a>
    </div>
</div>

<form method="GET" class="tt-filter">
    <input name="cari" class="grow" value="{{ request('cari') }}" placeholder="Cari {{ strtolower($judul) }}...">
    @if ($jenis === 'paket')
        <select name="tujuan">
            <option value="">Semua Tujuan</option>
            @foreach ($tujuanList as $tujuan)
                <option value="{{ $tujuan }}" @selected(request('tujuan') === $tujuan)>{{ $tujuan }}</option>
            @endforeach
        </select>
    @else
        <select name="status">
            <option value="">Semua Status</option>
            @foreach (($jenis === 'driver' ? ['aktif'=>'Aktif','tidak_aktif'=>'Tidak Aktif'] : ['tersedia'=>'Tersedia','digunakan'=>'Digunakan','perawatan'=>'Perawatan','tidak_aktif'=>'Tidak Aktif']) as $val => $label)
                <option value="{{ $val }}" @selected(request('status') === $val)>{{ $label }}</option>
            @endforeach
        </select>
    @endif
    <button type="submit" class="tt-btn">Cari</button>
    <a class="tt-btn-outline" href="{{ route('admin.'.$jenis.'.index') }}">Reset</a>
</form>

<div class="tt-table-wrap">
    <table class="tt-table">
        <thead>
            <tr>
                <th>Foto</th>
                @if ($jenis === 'paket')
                    <th>Nama Paket</th><th>Tujuan</th><th>Berangkat</th><th>Harga</th><th>Kuota</th>
                @elseif ($jenis === 'driver')
                    <th>Nama</th><th>No. Telepon</th><th>No. SIM</th><th>Alamat</th><th>Status</th>
                @else
                    <th>Plat Nomor</th><th>Jenis</th><th>Merek</th><th>Kapasitas</th><th>Tahun</th><th>Status</th>
                @endif
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($data as $item)
                <tr>
                    <td>
                        @if ($item->foto)
                            <img class="tt-thumb" src="{{ asset('storage/'.$item->foto) }}" alt="Foto">
                        @else
                            <div class="tt-thumb" style="display:grid;place-items:center;background:var(--blue);color:#fff;">✦</div>
                        @endif
                    </td>
                    @if ($jenis === 'paket')
                        <td><strong>{{ $item->nama_paket }}</strong></td>
                        <td>{{ $item->tujuan }}</td>
                        <td>{{ $item->tanggal_berangkat?->format('d M Y') }}</td>
                        <td>Rp {{ number_format($item->harga, 0, ',', '.') }}</td>
                        <td>{{ $item->kuota }}</td>
                    @elseif ($jenis === 'driver')
                        <td><strong>{{ $item->nama }}</strong></td>
                        <td>{{ $item->nomor_telepon }}</td>
                        <td>{{ $item->nomor_sim }}</td>
                        <td style="max-width:220px;white-space:normal;">{{ $item->alamat }}</td>
                        <td><span class="tt-badge s-{{ $item->status }}">{{ str_replace('_', ' ', ucwords($item->status, '_')) }}</span></td>
                    @else
                        <td><strong>{{ $item->plat_nomor }}</strong></td>
                        <td>{{ $item->jenis }}</td>
                        <td>{{ $item->merek }}</td>
                        <td>{{ $item->kapasitas }} kursi</td>
                        <td>{{ $item->tahun_produksi }}</td>
                        <td><span class="tt-badge s-{{ $item->status }}">{{ str_replace('_', ' ', ucwords($item->status, '_')) }}</span></td>
                    @endif
                    <td>
                        <div class="tt-actions">
                            <a href="{{ route('admin.'.$jenis.'.edit', $item->id) }}" class="tt-btn tt-btn-sm">Edit</a>
                            <button type="button" class="tt-btn-danger tt-btn-sm"
                                onclick="openDeleteModal('{{ route('admin.'.$jenis.'.destroy', $item->id) }}', '{{ addslashes($item->nama_paket ?? $item->nama ?? $item->plat_nomor) }}')">
                                Hapus
                            </button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="9" class="tt-empty">Belum ada data. Klik tombol Tambah untuk mengisi.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

{{ $data->links() }}

{{-- Pop-up Konfirmasi Hapus Kustom (UI-10, UI-19, UI-23) --}}
<div id="modalDeleteMaster" class="tt-modal" role="dialog" aria-modal="true" hidden>
    <div class="tt-modal-card">
        <button type="button" class="tt-modal-close" data-modal-close="modalDeleteMaster">&times;</button>
        <h3>Konfirmasi Hapus {{ $judul }}</h3>
        <p>Apakah Anda yakin ingin menghapus data <strong id="deleteItemName"></strong>?</p>
        <p style="color:var(--danger);font-size:13px;font-weight:700;">Tindakan ini tidak dapat dibatalkan.</p>
        <div class="tt-modal-actions">
            <button type="button" class="tt-btn-outline" data-modal-close="modalDeleteMaster">Batal</button>
            <form id="deleteMasterForm" method="POST">
                @csrf @method('DELETE')
                <button type="submit" class="tt-btn-danger">Konfirmasi</button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function openDeleteModal(actionUrl, itemName) {
    document.getElementById('deleteMasterForm').action = actionUrl;
    document.getElementById('deleteItemName').textContent = itemName;
    document.getElementById('modalDeleteMaster').hidden = false;
}
</script>
@endpush