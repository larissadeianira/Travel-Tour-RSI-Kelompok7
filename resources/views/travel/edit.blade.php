@extends('layouts.panel')
@section('title', 'Edit Data Penumpang - Travel Tour')

@section('panel-content')
<div class="tt-header">
    <div>
        <a href="{{ route('customer.pesanan.show', $pemesanan) }}" class="tt-btn-outline tt-btn-sm">← Kembali ke Detail Pesanan</a>
        <h1 class="tt-title" style="margin-top:12px;">Edit Data Penumpang</h1>
        <p class="tt-subtitle">{{ $pemesanan->paket?->nama_paket }} ({{ $pemesanan->jumlah_peserta }} Peserta) — {{ $pemesanan->kode_pemesanan }}</p>
    </div>
</div>

<form method="POST" action="{{ route('customer.pesanan.update', $pemesanan) }}" class="tt-panel" style="max-width:860px;">
    @csrf @method('PUT')
    <div class="tt-inline">
        <div class="tt-field">
            <label for="email_pemesan">Email Pemesan</label>
            <input id="email_pemesan" name="email_pemesan" type="email" value="{{ old('email_pemesan', $pemesanan->email_pemesan) }}" required>
        </div>
        <div class="tt-field">
            <label for="telepon_pemesan">No. HP Pemesan</label>
            <input id="telepon_pemesan" name="telepon_pemesan" type="tel" value="{{ old('telepon_pemesan', $pemesanan->telepon_pemesan) }}" required>
        </div>
    </div>

    <div class="passenger-tabs" id="editTabs">
        @foreach ($pemesanan->penumpang as $i => $p)
            <button type="button" class="passenger-tab {{ $loop->first ? 'active' : '' }}" data-idx="{{ $i }}">Penumpang {{ $i + 1 }}</button>
        @endforeach
    </div>

    <div id="editCards">
        @foreach ($pemesanan->penumpang as $i => $p)
            <div class="passenger-card" @unless ($loop->first) hidden @endunless style="padding:18px;border:1.5px solid var(--border);border-radius:14px;background:#F8FAFC;">
                <h3 style="margin:0 0 12px;">Penumpang {{ $i + 1 }}</h3>
                <input type="hidden" name="penumpang[{{ $i }}][id]" value="{{ $p->id }}">
                <div class="tt-field">
                    <label>Nama Lengkap</label>
                    <input name="penumpang[{{ $i }}][nama_lengkap]" value="{{ old("penumpang.$i.nama_lengkap", $p->nama_lengkap) }}" maxlength="120" required>
                </div>
                <div class="tt-inline">
                    <div class="tt-field">
                        <label>NIK / No. KTP (16 Digit)</label>
                        <input name="penumpang[{{ $i }}][nik]" value="{{ old("penumpang.$i.nik", $p->nik) }}" inputmode="numeric" pattern="[0-9]{16}" maxlength="16" required>
                    </div>
                    <div class="tt-field">
                        <label>Tanggal Lahir</label>
                        <input type="date" name="penumpang[{{ $i }}][tanggal_lahir]" value="{{ old("penumpang.$i.tanggal_lahir", $p->tanggal_lahir->toDateString()) }}" max="{{ now()->subDay()->toDateString() }}" required>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div style="display:flex;justify-content:space-between;align-items:center;margin-top:22px;flex-wrap:wrap;gap:12px;">
        <strong>Total Pembayaran: Rp {{ number_format($pemesanan->total_pembayaran, 0, ',', '.') }}</strong>
        <button type="submit" class="tt-btn">Simpan Perubahan</button>
    </div>
</form>
@endsection

@push('scripts')
<script>
document.querySelectorAll('#editTabs .passenger-tab').forEach((btn, idx) => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('#editCards .passenger-card').forEach((c, i) => c.hidden = i !== idx);
        document.querySelectorAll('#editTabs .passenger-tab').forEach((b, i) => b.classList.toggle('active', i === idx));
    });
});
</script>
@endpush