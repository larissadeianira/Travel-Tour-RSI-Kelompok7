@extends('layouts.panel')
@section('title', 'Paket Wisata - Travel Tour')

@section('panel-content')
<section class="travel-hero">
    <div>
        <span class="eyebrow" style="color:var(--lime);">EXPLORE • DISCOVER • TRAVEL</span>
        <h1>Temukan perjalanan <em>impianmu.</em></h1>
        <p style="margin:0;color:rgba(255,255,255,.9);max-width:560px;">
            Pilih destinasi wisata terbaik dengan jadwal terstruktur, armada nyaman, dan driver berpengalaman.
        </p>
    </div>
    @guest
        <a href="{{ route('login') }}" class="tt-btn-lime">Masuk untuk Memesan ↗</a>
    @endguest
</section>

<form method="GET" action="{{ route('travel.katalog') }}" class="tt-filter">
    <input type="text" name="cari" value="{{ request('cari') }}" class="grow" placeholder="Cari Paket Travel atau destinasi...">
    <select name="tujuan" aria-label="Filter Tujuan">
        <option value="">Semua Tujuan</option>
        @foreach ($tujuanList as $tujuan)
            <option value="{{ $tujuan }}" @selected(request('tujuan') === $tujuan)>{{ $tujuan }}</option>
        @endforeach
    </select>
    <select name="bulan" aria-label="Filter Bulan Keberangkatan">
        <option value="">Semua Tanggal</option>
        @foreach ($bulanList as $bln)
            <option value="{{ $bln }}" @selected(request('bulan') === $bln)>
                {{ \Carbon\Carbon::createFromFormat('Y-m', $bln)->translatedFormat('F Y') }}
            </option>
        @endforeach
    </select>
    <button type="submit" class="tt-btn">Cari</button>
    @if (request()->hasAny(['cari', 'tujuan', 'bulan']))
        <a href="{{ route('travel.katalog') }}" class="tt-btn-outline">Reset</a>
    @endif
</form>

<div class="travel-cards">
    @forelse ($pakets as $paket)
        @php $sisa = $paket->sisaKuota(); @endphp
        <article class="travel-card">
            @if ($paket->foto)
                <img src="{{ asset('storage/'.$paket->foto) }}" alt="Foto {{ $paket->nama_paket }}">
            @else
                <div style="height:190px;display:grid;place-items:center;background:#1E3A8A;color:var(--lime);font-size:40px;">✦</div>
            @endif

            <div class="travel-card-body">
                <div class="travel-card-meta">
                    {{ $paket->tujuan }} • {{ $paket->durasi_hari }} Hari • {{ $paket->tanggal_berangkat->translatedFormat('d M Y') }}
                </div>
                <h3>{{ $paket->nama_paket }}</h3>
                <p class="travel-card-desc">{{ \Illuminate\Support\Str::limit($paket->deskripsi, 85) }}</p>

                <div class="travel-card-price-row">
                    <strong>Rp {{ number_format($paket->harga, 0, ',', '.') }}</strong>
                    <span class="sisa-pill {{ $sisa < 1 ? 'full' : '' }}">
                        {{ $sisa > 0 ? 'Sisa '.$sisa : 'Penuh' }}
                    </span>
                </div>

                @if ($sisa < 1)
                    <button type="button" class="tt-btn-outline" style="width:100%;opacity:.7;" disabled>Kuota habis</button>
                @else
                    <a href="{{ route('travel.detail', $paket) }}" class="tt-btn-lime" style="width:100%;">
                        Lihat &amp; Pesan ↗
                    </a>
                @endif
            </div>
        </article>
    @empty
        <div class="tt-panel" style="grid-column:1 / -1;text-align:center;">
            <p style="margin:0;color:var(--muted);">Belum ada paket perjalanan yang sesuai dengan filter.</p>
        </div>
    @endforelse
</div>

{{ $pakets->links() }}
@endsection
```

##### `resources/views/travel/detail.blade.php`
```blade
@extends('layouts.panel')
@section('title', $paket->nama_paket.' - Travel Tour')

@section('panel-content')
<div class="tt-header">
    <a href="{{ route('travel.katalog') }}" class="tt-btn-outline">← Cari Paket Lain</a>
    @auth
        @if (auth()->user()->role === 'customer')
            <a href="{{ route('customer.pesanan.index') }}" class="tt-btn">Lihat Pesanan Saya →</a>
        @endif
    @endauth
</div>

<div class="travel-detail-grid">
    <div class="tt-panel" style="padding:14px;">
        @if ($paket->foto)
            <img class="travel-detail-img" src="{{ asset('storage/'.$paket->foto) }}" alt="{{ $paket->nama_paket }}">
        @else
            <div style="height:360px;border-radius:14px;display:grid;place-items:center;background:var(--blue-grad);color:var(--lime);font-size:64px;">✦</div>
        @endif
    </div>

    <div class="tt-panel">
        <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;">
            <span class="eyebrow" style="margin:0;">{{ strtoupper($paket->tujuan) }}</span>
            <span class="tt-badge {{ $tersedia > 0 ? 'tone-success' : 'tone-danger' }}">
                {{ $tersedia > 0 ? 'Sisa '.$tersedia.' Kursi' : 'Penuh' }}
            </span>
        </div>

        <h1 class="tt-title" style="margin-top:10px;">{{ $paket->nama_paket }}</h1>
        <p style="font-family:'Unbounded',sans-serif;font-size:22px;color:var(--blue-deep);margin:14px 0;">
            Rp {{ number_format($paket->harga, 0, ',', '.') }} <small style="font-size:12px;color:var(--muted);">/ orang</small>
        </p>

        <div class="kv-list" style="margin-bottom:18px;">
            <div class="kv-row"><span>Durasi Perjalanan</span><strong>{{ $paket->durasi_hari }} Hari</strong></div>
            <div class="kv-row"><span>Tanggal Keberangkatan</span><strong>{{ $paket->tanggal_berangkat->translatedFormat('d F Y') }}</strong></div>
            <div class="kv-row"><span>Kuota Total</span><strong>{{ $paket->kuota }} Peserta</strong></div>
        </div>

        <h3 style="margin:0 0 8px;">Deskripsi Paket</h3>
        <p style="color:#334155;margin-top:0;">{{ $paket->deskripsi ?: 'Informasi detail paket perjalanan wisata.' }}</p>

        @if ($paket->fasilitas)
            <h3 style="margin:18px 0 8px;">Fasilitas Termasuk</h3>
            <ul style="margin:0 0 22px;padding-left:20px;color:#334155;">
                @foreach (preg_split('/\r\n|\r|\n/', trim($paket->fasilitas)) as $fas)
                    @if ($fas !== '')<li>{{ $fas }}</li>@endif
                @endforeach
            </ul>
        @endif

        @if ($paket->tanggal_berangkat->lt(today()))
            <button type="button" class="tt-btn-outline" style="width:100%;" disabled>Jadwal Keberangkatan Sudah Lewat</button>
        @elseif ($tersedia < 1)
            <button type="button" class="tt-btn-outline" style="width:100%;" disabled>Kuota Habis</button>
        @else
            @auth
                @if (auth()->user()->role === 'customer')
                    <a href="{{ route('customer.pemesanan.create', $paket) }}" class="tt-btn-lime" style="width:100%;padding:14px;">Pesan Sekarang ↗</a>
                @else
                    <div class="tt-warning" style="margin:0;">Masuk menggunakan akun <strong>Customer</strong> untuk melakukan pemesanan.</div>
                @endif
            @else
                <a href="{{ route('login') }}" class="tt-btn" style="width:100%;padding:14px;">Masuk untuk Memesan ↗</a>
            @endauth
        @endif
    </div>
</div>
@endsection