@extends('layouts.panel')
@section('title', 'Pembayaran '.$pemesanan->kode_pemesanan.' - Travel Tour')

@section('panel-content')
<div class="tt-header">
    <div>
        <a href="{{ route('customer.pesanan.show', $pemesanan) }}" class="tt-btn-outline tt-btn-sm">← Ringkasan Pemesanan</a>
        <h1 class="tt-title" style="margin-top:12px;">Pembayaran Pesanan</h1>
        <p class="tt-subtitle">Invoice: <strong>{{ $pemesanan->pembayaran?->nomor_invoice }}</strong> · Kode: {{ $pemesanan->kode_pemesanan }}</p>
    </div>
</div>

<div class="tt-grid2">
    <div class="qr-card">
        <span class="eyebrow" style="color:var(--lime);">QRIS PAYMENT</span>
        <h2 style="margin:6px 0 4px;color:#fff;">Scan untuk Membayar</h2>
        <p style="margin:0;font-size:13px;color:rgba(255,255,255,.85);">Total: <b>Rp {{ number_format($pemesanan->total_pembayaran, 0, ',', '.') }}</b></p>

        <div class="qr-frame">
            <svg id="qrSvg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 210 210" width="190" height="190" aria-label="Kode QR Pembayaran"></svg>
        </div>

        <p style="margin:6px 0 14px;font-size:13px;">
            Batas waktu bayar: <strong id="qrTimer" style="color:var(--lime);" data-batas="{{ $pemesanan->batas_waktu_pembayaran->timestamp }}">--:--:--</strong>
        </p>

        <button type="button" id="btnDownloadQr" class="tt-btn-lime">Download QR (.PNG) ↓</button>
    </div>

    <div class="tt-panel">
        <h2>Unggah Bukti Pembayaran</h2>
        <div class="kv-list" style="margin-bottom:18px;">
            <div class="kv-row"><span>{{ $pemesanan->paket?->nama_paket }} &times; {{ $pemesanan->jumlah_peserta }}</span><strong>Rp {{ number_format($pemesanan->harga_satuan * $pemesanan->jumlah_peserta, 0, ',', '.') }}</strong></div>
            <div class="kv-row"><span>Biaya Admin</span><strong>Rp {{ number_format($pemesanan->biaya_admin, 0, ',', '.') }}</strong></div>
            <div class="kv-row"><span>Total Tagihan</span><strong style="color:var(--blue-deep);">Rp {{ number_format($pemesanan->total_pembayaran, 0, ',', '.') }}</strong></div>
        </div>

        <form method="POST" action="{{ route('customer.pesanan.upload', $pemesanan) }}" enctype="multipart/form-data">
            @csrf
            <label class="upload-zone" for="buktiInput" style="display:block;">
                <strong style="display:block;color:var(--navy);">Klik untuk Memilih File Bukti Pembayaran</strong>
                <small style="color:var(--muted);">Format JPG, PNG, atau PDF · Maksimal 4 MB</small>
                <div id="buktiPreview" class="upload-preview"></div>
            </label>
            <input id="buktiInput" type="file" name="bukti" accept=".jpg,.jpeg,.png,.pdf" hidden required>

            <button type="submit" id="btnKirimBukti" class="tt-btn" style="width:100%;margin-top:16px;padding:13px;" disabled>
                Kirim Pembayaran ↗
            </button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(() => {
    // 1. Render pola QR deterministik berdasarkan nomor invoice + kode
    const seedStr = @json(($pemesanan->pembayaran?->nomor_invoice ?? 'INV').'|'.$pemesanan->kode_pemesanan.'|'.$pemesanan->total_pembayaran);
    const svg = document.getElementById('qrSvg');
    const size = 21;
    let hash = 2166136261;
    for (let i = 0; i < seedStr.length; i++) {
        hash ^= seedStr.charCodeAt(i);
        hash = Math.imul(hash, 16777619);
    }

    let rects = '<rect width="210" height="210" fill="#ffffff"/>';
    const isFinder = (r, c) =>
        (r < 7 && c < 7) || (r < 7 && c >= size - 7) || (r >= size - 7 && c < 7);

    const drawFinder = (ox, oy) => {
        rects += `<rect x="${ox}" y="${oy}" width="70" height="70" fill="#252944"/>`;
        rects += `<rect x="${ox+10}" y="${oy+10}" width="50" height="50" fill="#ffffff"/>`;
        rects += `<rect x="${ox+20}" y="${oy+20}" width="30" height="30" fill="#252944"/>`;
    };
    drawFinder(0, 0);
    drawFinder(140, 0);
    drawFinder(0, 140);

    for (let r = 0; r < size; r++) {
        for (let c = 0; c < size; c++) {
            if (isFinder(r, c)) continue;
            hash ^= (r * 31 + c * 17);
            hash = Math.imul(hash, 16777619);
            if ((hash >>> 0) % 2 === 0) {
                rects += `<rect x="${c * 10}" y="${r * 10}" width="10" height="10" fill="#252944"/>`;
            }
        }
    }
    svg.innerHTML = rects;

    // 2. Download QR sebagai PNG
    document.getElementById('btnDownloadQr').addEventListener('click', () => {
        const xml = new XMLSerializer().serializeToString(svg);
        const img = new Image();
        img.onload = () => {
            const canvas = document.createElement('canvas');
            canvas.width = 420; canvas.height = 420;
            const ctx = canvas.getContext('2d');
            ctx.fillStyle = '#ffffff';
            ctx.fillRect(0, 0, 420, 420);
            ctx.drawImage(img, 20, 20, 380, 380);
            const a = document.createElement('a');
            a.href = canvas.toDataURL('image/png');
            a.download = 'QRIS-{{ $pemesanan->kode_pemesanan }}.png';
            a.click();
        };
        img.src = 'data:image/svg+xml;base64,' + btoa(unescape(encodeURIComponent(xml)));
    });

    // 3. Pratinjau file bukti pembayaran
    const fileInput = document.getElementById('buktiInput');
    const previewEl = document.getElementById('buktiPreview');
    const submitBtn = document.getElementById('btnKirimBukti');

    fileInput.addEventListener('change', () => {
        const file = fileInput.files[0];
        previewEl.innerHTML = '';
        submitBtn.disabled = !file;
        if (!file) return;
        if (file.type.startsWith('image/')) {
            const img = document.createElement('img');
            img.src = URL.createObjectURL(file);
            img.alt = 'Pratinjau bukti pembayaran';
            previewEl.appendChild(img);
        } else {
            previewEl.innerHTML = `<p style="margin:12px 0 0;font-weight:700;color:var(--blue-deep);">📄 ${file.name}</p>`;
        }
    });

    // 4. Countdown timer
    const timerEl = document.getElementById('qrTimer');
    const deadline = Number(timerEl.dataset.batas) * 1000;
    function tick() {
        const diff = Math.max(0, Math.floor((deadline - Date.now()) / 1000));
        const pad = n => String(n).padStart(2, '0');
        timerEl.textContent = `${pad(Math.floor(diff / 3600))}:${pad(Math.floor((diff % 3600) / 60))}:${pad(diff % 60)}`;
    }
    tick();
    setInterval(tick, 1000);
})();
</script>
@endpush