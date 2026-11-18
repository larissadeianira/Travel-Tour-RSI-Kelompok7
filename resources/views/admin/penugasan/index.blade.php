@extends('layouts.panel')
@section('title', 'Penugasan Perjalanan - Travel Tour')

@section('panel-content')
<div class="tt-header">
    <div>
        <h1 class="tt-title">Penugasan Kendaraan &amp; Driver</h1>
        <p class="tt-subtitle">Tetapkan kendaraan dan driver untuk pemesanan terverifikasi tanpa bentrok jadwal.</p>
    </div>
</div>

{{-- Tabel Pemesanan Terverifikasi yang Menunggu Penugasan (UI-33) --}}
<div class="tt-panel" style="margin-bottom:20px;">
    <h2>Pemesanan Terverifikasi Menunggu Penugasan</h2>
    <div class="tt-table-wrap">
        <table class="tt-table white-rows">
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Paket &amp; Peserta</th>
                    <th>Berangkat</th>
                    <th>Kendaraan</th>
                    <th>Driver</th>
                    <th>Titik &amp; Jam Kumpul</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($pesanans as $pesanan)
                    @php $formId = 'assign-'.$pesanan->id; @endphp
                    <tr>
                        <td>
                            <strong>{{ $pesanan->kode_pemesanan }}</strong>
                            <form id="{{ $formId }}" method="POST" action="{{ route('admin.penugasan.store') }}">
                                @csrf
                                <input type="hidden" name="pemesanan_id" value="{{ $pesanan->id }}">
                            </form>
                        </td>
                        <td>{{ $pesanan->paket?->nama_paket }} <br><small style="color:var(--muted);">{{ $pesanan->jumlah_peserta }} peserta</small></td>
                        <td>{{ $pesanan->tanggal_berangkat?->format('d M Y') }}</td>
                        <td>
                            <select name="kendaraan_id" form="{{ $formId }}" required style="padding:8px;border-radius:8px;border:1px solid var(--border);max-width:190px;">
                                <option value="">Pilih Kendaraan</option>
                                @foreach ($kendaraans as $k)
                                    <option value="{{ $k->id }}">{{ $k->plat_nomor }} ({{ $k->kapasitas }} kursi)</option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <select name="driver_id" form="{{ $formId }}" required style="padding:8px;border-radius:8px;border:1px solid var(--border);max-width:170px;">
                                <option value="">Pilih Driver</option>
                                @foreach ($drivers as $d)
                                    <option value="{{ $d->id }}">{{ $d->nama }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <div style="display:flex;gap:6px;">
                                <input type="text" name="titik_kumpul" form="{{ $formId }}" value="Lobby / Titik Kumpul" maxlength="160" required style="padding:8px;border-radius:8px;border:1px solid var(--border);width:140px;">
                                <input type="time" name="jam_kumpul" form="{{ $formId }}" value="07:00" required style="padding:8px;border-radius:8px;border:1px solid var(--border);">
                            </div>
                        </td>
                        <td><button type="submit" form="{{ $formId }}" class="tt-btn tt-btn-sm">Simpan</button></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="tt-empty">Semua pemesanan terverifikasi sudah memiliki penugasan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="pj-columns">
    <section class="tt-panel">
        <h2>Jadwal Penugasan Aktif</h2>
        <div class="tt-table-wrap">
            <table class="tt-table">
                <thead>
                    <tr>
                        <th>Kode</th><th>Paket</th><th>Periode</th><th>Driver</th><th>Kendaraan</th><th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($penugasans as $item)
                        <tr>
                            <td><strong>{{ $item->pemesanan?->kode_pemesanan }}</strong></td>
                            <td>{{ $item->pemesanan?->paket?->nama_paket }}</td>
                            <td>{{ $item->tanggal_mulai->format('d/m') }} – {{ $item->tanggal_selesai->format('d/m/Y') }}</td>
                            <td>{{ $item->driver?->nama }}</td>
                            <td>{{ $item->kendaraan?->plat_nomor }}</td>
                            <td>
                                <form method="POST" action="{{ route('admin.penugasan.status', $item) }}">
                                    @csrf @method('PATCH')
                                    <select name="status" onchange="this.form.submit()" class="tt-badge s-{{ $item->status }}" style="border:0;cursor:pointer;">
                                        @foreach (['terjadwal' => 'Terjadwal', 'berjalan' => 'Berjalan', 'selesai' => 'Selesai'] as $val => $lbl)
                                            <option value="{{ $val }}" @selected($item->status === $val)>{{ $lbl }}</option>
                                        @endforeach
                                    </select>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="tt-empty">Belum ada penugasan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $penugasans->links() }}
    </section>

    <section class="tt-panel">
        <h2>Hubungkan Akun Login Driver</h2>
        <p style="font-size:13px;color:var(--muted);margin-top:0;">Pastikan Data Master Driver terhubung ke akun user role <code>driver</code> agar jadwal tampil saat Driver login.</p>
        <form method="POST" action="{{ route('admin.penugasan.link') }}">
            @csrf
            <div class="tt-field">
                <label>Data Master Driver</label>
                <select name="driver_id" required>
                    <option value="">Pilih driver</option>
                    @foreach ($drivers as $driver)
                        <option value="{{ $driver->id }}">{{ $driver->nama }} {{ $driver->user ? '✓ ('.$driver->user->email.')' : '(Belum terhubung)' }}</option>
                    @endforeach
                </select>
            </div>
            <div class="tt-field">
                <label>Akun Login Driver</label>
                <select name="user_id" required>
                    <option value="">Pilih akun</option>
                    @foreach ($driverUsers as $u)
                        <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }})</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="tt-btn" style="width:100%;">Hubungkan Akun Driver</button>
        </form>
    </section>
</div>
@endsection