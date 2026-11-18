@extends('layouts.panel')
@section('title', 'Pemesanan '.$paket->nama_paket.' - Travel Tour')

@section('panel-content')
<div class="tt-header">
    <div>
        <a href="{{ route('travel.detail', $paket) }}" class="tt-btn-outline tt-btn-sm">← Kembali ke Detail Paket</a>
        <h1 class="tt-title" style="margin-top:12px;">Pesan Paket: {{ $paket->nama_paket }}</h1>
        <p class="tt-subtitle">Keberangkatan {{ $paket->tanggal_berangkat->translatedFormat('d F Y') }} · Sisa kuota real-time: <strong>{{ $tersedia }}</strong> peserta</p>
    </div>
</div>

@if ($tersedia < 1)
    <div class="tt-error">Maaf, kuota paket wisata ini sudah habis.</div>
@else
    <form method="POST" action="{{ route('customer.pemesanan.store', $paket) }}" id="bookingForm">
        @csrf
        <div class="booking-grid">
            <div>
                <div class="tt-panel" style="padding:14px;">
                    @if ($paket->foto)
                        <img src="{{ asset('storage/'.$paket->foto) }}" alt="{{ $paket->nama_paket }}" style="width:100%;height:190px;object-fit:cover;border-radius:12px;">
                    @endif
                </div>
                <div class="cost-box">
                    <div class="cost-row">
                        <span>{{ $paket->nama_paket }} &times; <b id="lblPeserta">1</b></span>
                        <span id="lblSubtotal">Rp 0</span>
                    </div>
                    <div class="cost-row">
                        <span>Biaya Admin</span>
                        <span>Rp {{ number_format($biayaAdmin, 0, ',', '.') }}</span>
                    </div>
                    <div class="cost-total">
                        <span>Total</span>
                        <span id="lblTotal">Rp 0</span>
                    </div>
                </div>
            </div>

            <div>
                {{-- Tahap 1: Data Pemesanan (UI-34) --}}
                <div class="tt-panel" id="stepBooking" @if ($errors->any()) hidden @endif>
                    <h2>Langkah 1 — Data Pemesanan</h2>
                    <div class="tt-field">
                        <label>Paket Wisata</label>
                        <input type="text" value="{{ $paket->nama_paket }} ({{ $paket->tujuan }})" readonly>
                    </div>
                    <div class="tt-field">
                        <label>Jadwal Keberangkatan</label>
                        <select name="jadwal_keberangkatan" disabled>
                            <option>{{ $paket->tanggal_berangkat->translatedFormat('d F Y') }}</option>
                        </select>
                    </div>
                    <div class="tt-field">
                        <label for="jumlahPeserta">Jumlah Peserta (Maks. {{ min(30, $tersedia) }})</label>
                        <select id="jumlahPeserta" name="jumlah_peserta" required>
                            @for ($i = 1; $i <= min(30, $tersedia); $i++)
                                <option value="{{ $i }}" @selected(old('jumlah_peserta', 1) == $i)>{{ $i }} Orang</option>
                            @endfor
                        </select>
                    </div>
                    <div style="display:flex;justify-content:flex-end;margin-top:20px;">
                        <button type="button" id="btnToStep2" class="tt-btn">Pengisian Data Penumpang →</button>
                    </div>
                </div>

                {{-- Tahap 2: Data Penumpang (UI-35) --}}
                <div class="tt-panel" id="stepPassengers" @unless ($errors->any()) hidden @endunless>
                    <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;margin-bottom:16px;flex-wrap:wrap;">
                        <h2 style="margin:0;">Langkah 2 — Data Penumpang</h2>
                        <button type="button" id="btnBackStep1" class="tt-btn-outline tt-btn-sm">← Edit Jumlah Peserta</button>
                    </div>

                    <div class="tt-inline">
                        <div class="tt-field">
                            <label for="email_pemesan">Email Pemesan</label>
                            <input type="email" id="email_pemesan" name="email_pemesan" value="{{ old('email_pemesan', auth()->user()->email) }}" required>
                        </div>
                        <div class="tt-field">
                            <label for="telepon_pemesan">No. HP Pemesan</label>
                            <input type="tel" id="telepon_pemesan" name="telepon_pemesan" value="{{ old('telepon_pemesan', auth()->user()->nomor_telepon) }}" required>
                        </div>
                    </div>

                    <div class="passenger-tabs" id="passengerTabs"></div>
                    <div id="passengerContainer"></div>

                    <div style="display:flex;justify-content:flex-end;margin-top:20px;">
                        <button type="submit" class="tt-btn-lime" style="padding:13px 26px;">Check Out Pemesanan ↗</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endif
@endsection

@push('scripts')
<script>
(() => {
    const countEl = document.getElementById('jumlahPeserta');
    if (!countEl) return;

    const harga = @json((float) $paket->harga);
    const biayaAdmin = @json((float) $biayaAdmin);
    const oldPenumpang = @json(old('penumpang', []));
    const step1 = document.getElementById('stepBooking');
    const step2 = document.getElementById('stepPassengers');
    const tabsEl = document.getElementById('passengerTabs');
    const containerEl = document.getElementById('passengerContainer');
    let activeIdx = 0;

    const rp = v => 'Rp ' + new Intl.NumberFormat('id-ID').format(v);
    const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));

    function updateCost() {
        const n = Number(countEl.value) || 1;
        document.getElementById('lblPeserta').textContent = n;
        document.getElementById('lblSubtotal').textContent = rp(harga * n);
        document.getElementById('lblTotal').textContent = rp((harga * n) + biayaAdmin);
    }

    function showTab(idx) {
        activeIdx = idx;
        containerEl.querySelectorAll('.passenger-card').forEach((card, i) => card.hidden = i !== idx);
        tabsEl.querySelectorAll('.passenger-tab').forEach((btn, i) => btn.classList.toggle('active', i === idx));
    }

    function renderPassengers() {
        const prev = [...containerEl.querySelectorAll('.passenger-card')].map(el => ({
            nama_lengkap: el.querySelector('[data-nama]').value,
            nik: el.querySelector('[data-nik]').value,
            tanggal_lahir: el.querySelector('[data-lahir]').value,
        }));

        const n = Number(countEl.value) || 1;
        let tabsHtml = '';
        let cardsHtml = '';

        for (let i = 0; i < n; i++) {
            const v = prev[i] || oldPenumpang[i] || {};
            tabsHtml += `<button type="button" class="passenger-tab ${i === 0 ? 'active' : ''}" data-idx="${i}">Penumpang ${i + 1}</button>`;
            cardsHtml += `
                <div class="passenger-card" ${i !== 0 ? 'hidden' : ''} style="padding:18px;border:1.5px solid var(--border);border-radius:14px;background:#F8FAFC;">
                    <h3 style="margin:0 0 14px;font-size:15px;">Data Penumpang ${i + 1}</h3>
                    <div class="tt-field">
                        <label>Nama Lengkap (Sesuai KTP)</label>
                        <input data-nama name="penumpang[${i}][nama_lengkap]" value="${esc(v.nama_lengkap)}" maxlength="120" required>
                    </div>
                    <div class="tt-inline">
                        <div class="tt-field">
                            <label>NIK / No. KTP (16 Digit)</label>
                            <input data-nik name="penumpang[${i}][nik]" value="${esc(v.nik)}" inputmode="numeric" pattern="[0-9]{16}" maxlength="16" placeholder="3573xxxxxxxxxxxx" required>
                        </div>
                        <div class="tt-field">
                            <label>Tanggal Lahir</label>
                            <input data-lahir type="date" name="penumpang[${i}][tanggal_lahir]" value="${esc(v.tanggal_lahir)}" max="{{ now()->subDay()->toDateString() }}" required>
                        </div>
                    </div>
                </div>`;
        }

        tabsEl.innerHTML = tabsHtml;
        containerEl.innerHTML = cardsHtml;
        showTab(Math.min(activeIdx, n - 1));
        updateCost();
    }

    tabsEl.addEventListener('click', e => {
        const btn = e.target.closest('.passenger-tab');
        if (btn) showTab(Number(btn.dataset.idx));
    });

    containerEl.addEventListener('invalid', e => {
        const card = e.target.closest('.passenger-card');
        if (card) {
            const idx = [...containerEl.children].indexOf(card);
            showTab(idx);
        }
    }, true);

    document.getElementById('btnToStep2').addEventListener('click', () => {
        renderPassengers();
        step1.hidden = true;
        step2.hidden = false;
    });

    document.getElementById('btnBackStep1').addEventListener('click', () => {
        step2.hidden = true;
        step1.hidden = false;
    });

    countEl.addEventListener('change', renderPassengers);
    renderPassengers();
})();
</script>
@endpush