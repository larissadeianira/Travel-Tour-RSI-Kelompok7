@extends('layouts.panel')
@section('title', $judul.' - Travel Tour')

@section('panel-content')
@php $edit = $item->exists; @endphp

<div class="tt-header">
    <div>
        <a href="{{ route('admin.'.$jenis.'.index') }}" class="tt-btn-outline tt-btn-sm">← Kembali</a>
        <h1 class="tt-title" style="margin-top:12px;">{{ $judul }}</h1>
    </div>
</div>

<form class="tt-form-grid" method="POST" enctype="multipart/form-data"
      action="{{ $edit ? route('admin.'.$jenis.'.update', $item->id) : route('admin.'.$jenis.'.store') }}">
    @csrf
    @if ($edit) @method('PUT') @endif

    <div class="tt-panel">
        @if ($jenis === 'paket')
            <div class="tt-field"><label>Nama Paket</label><input required name="nama_paket" value="{{ old('nama_paket', $item->nama_paket) }}" placeholder="Bali Hemat 3D2N"></div>
            <div class="tt-inline">
                <div class="tt-field"><label>Tujuan</label><input required name="tujuan" value="{{ old('tujuan', $item->tujuan) }}" placeholder="Bali"></div>
                <div class="tt-field"><label>Durasi (hari)</label><input required type="number" min="1" name="durasi_hari" value="{{ old('durasi_hari', $item->durasi_hari) }}"></div>
            </div>
            <div class="tt-inline">
                <div class="tt-field"><label>Harga (Rp)</label><input required type="number" min="0" step="1000" name="harga" value="{{ old('harga', $item->harga) }}"></div>
                <div class="tt-field"><label>Kuota</label><input required type="number" min="1" name="kuota" value="{{ old('kuota', $item->kuota) }}"></div>
            </div>
            <div class="tt-field"><label>Jadwal Keberangkatan</label><input required type="date" name="tanggal_berangkat" value="{{ old('tanggal_berangkat', $item->tanggal_berangkat?->format('Y-m-d')) }}"></div>
            <div class="tt-field"><label>Deskripsi</label><textarea rows="3" name="deskripsi">{{ old('deskripsi', $item->deskripsi) }}</textarea></div>
            <div class="tt-field"><label>Fasilitas (pisahkan per baris)</label><textarea rows="3" name="fasilitas">{{ old('fasilitas', $item->fasilitas) }}</textarea></div>
        @elseif ($jenis === 'driver')
            <div class="tt-field"><label>Nama Lengkap</label><input required name="nama" value="{{ old('nama', $item->nama) }}" placeholder="Budi Santoso"></div>
            <div class="tt-inline">
                <div class="tt-field"><label>Nomor Telepon</label><input required name="nomor_telepon" value="{{ old('nomor_telepon', $item->nomor_telepon) }}"></div>
                <div class="tt-field"><label>Nomor SIM</label><input required name="nomor_sim" value="{{ old('nomor_sim', $item->nomor_sim) }}"></div>
            </div>
            <div class="tt-field"><label>Alamat</label><textarea required rows="3" name="alamat">{{ old('alamat', $item->alamat) }}</textarea></div>
            <div class="tt-field">
                <label>Status</label>
                <select required name="status">
                    <option value="aktif" @selected(old('status', $item->status) === 'aktif')>Aktif</option>
                    <option value="tidak_aktif" @selected(old('status', $item->status) === 'tidak_aktif')>Tidak Aktif</option>
                </select>
            </div>
        @else
            <div class="tt-field"><label>Plat Nomor</label><input required name="plat_nomor" value="{{ old('plat_nomor', $item->plat_nomor) }}" placeholder="N 1234 AB"></div>
            <div class="tt-inline">
                <div class="tt-field"><label>Jenis Kendaraan</label><input required name="jenis" value="{{ old('jenis', $item->jenis) }}" placeholder="Mobil / Bus"></div>
                <div class="tt-field"><label>Merek</label><input required name="merek" value="{{ old('merek', $item->merek) }}" placeholder="Toyota HiAce"></div>
            </div>
            <div class="tt-inline">
                <div class="tt-field"><label>Tahun Produksi</label><input required type="number" min="1980" name="tahun_produksi" value="{{ old('tahun_produksi', $item->tahun_produksi) }}"></div>
                <div class="tt-field"><label>Kapasitas (Kursi)</label><input required type="number" min="1" name="kapasitas" value="{{ old('kapasitas', $item->kapasitas) }}"></div>
            </div>
            <div class="tt-field">
                <label>Status</label>
                <select required name="status">
                    @foreach (['tersedia'=>'Tersedia','digunakan'=>'Digunakan','perawatan'=>'Perawatan','tidak_aktif'=>'Tidak Aktif'] as $val => $label)
                        <option value="{{ $val }}" @selected(old('status', $item->status ?? 'tersedia') === $val)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:16px;">
            <a class="tt-btn-outline" href="{{ route('admin.'.$jenis.'.index') }}">Batal</a>
            <button class="tt-btn" type="submit">Simpan Data</button>
        </div>
    </div>

    <div class="tt-upload-card">
        <strong>{{ $jenis === 'paket' ? 'Foto Paket' : ($jenis === 'driver' ? 'Foto Driver' : 'Foto Kendaraan') }}</strong>
        @if ($item->foto)
            <img id="fotoPreview" src="{{ asset('storage/'.$item->foto) }}" alt="Pratinjau foto">
        @else
            <img id="fotoPreview" alt="Pratinjau foto" hidden>
        @endif
        <input id="fotoInput" type="file" accept="image/png,image/jpeg,image/webp" name="foto" style="color:#fff;">
        <small style="color:rgba(255,255,255,.8);">JPG, PNG, WEBP. Maksimum 2 MB.</small>
    </div>
</form>
@endsection

@push('scripts')
<script>
document.getElementById('fotoInput').addEventListener('change', e => {
    const file = e.target.files[0];
    const img = document.getElementById('fotoPreview');
    if (!file) return;
    img.src = URL.createObjectURL(file);
    img.hidden = false;
});
</script>
@endpush