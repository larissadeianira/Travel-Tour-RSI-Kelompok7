@extends('layouts.panel')
@section('title', 'Profil & Identitas - Travel Tour')

@section('panel-content')
<div class="tt-header">
    <div>
        <h1 class="tt-title">Profil &amp; Identitas Pelanggan</h1>
        <p class="tt-subtitle">Lengkapi nomor &amp; dokumen identitas agar verifikasi data pelanggan oleh Staff berstatus lengkap.</p>
    </div>
</div>

<form method="POST" action="{{ route('customer.profil.update') }}" enctype="multipart/form-data" class="tt-panel" style="max-width:720px;">
    @csrf @method('PUT')
    <div class="tt-field">
        <label>Nama Lengkap</label>
        <input name="name" value="{{ old('name', $user->name) }}" required>
    </div>
    <div class="tt-inline">
        <div class="tt-field">
            <label>Email</label>
            <input type="email" value="{{ $user->email }}" disabled>
        </div>
        <div class="tt-field">
            <label>Nomor Telepon</label>
            <input name="nomor_telepon" value="{{ old('nomor_telepon', $user->nomor_telepon) }}" required>
        </div>
    </div>
    <div class="tt-field">
        <label>Nomor Identitas (NIK / SIM / Paspor)</label>
        <input name="nomor_identitas" value="{{ old('nomor_identitas', $user->nomor_identitas) }}" placeholder="Contoh: 3573011205990001">
    </div>
    <div class="tt-field">
        <label>Dokumen Identitas (JPG, PNG, PDF maks. 2 MB)</label>
        <input type="file" name="dokumen_identitas" accept=".jpg,.jpeg,.png,.pdf">
        <small style="color:var(--muted);">
            Status saat ini:
            <strong>{{ $user->dokumen_identitas ? 'Sudah diunggah (Tersimpan aman)' : 'Belum diunggah' }}</strong>
        </small>
    </div>
    <button type="submit" class="tt-btn">Simpan Profil</button>
</form>
@endsection