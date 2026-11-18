<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\PaketTravel;
use App\Models\Pembayaran;
use App\Models\Pemesanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class TravelController extends Controller
{
    public const BIAYA_ADMIN = 10000;

    private function customer(): void
    {
        abort_unless(auth()->check() && auth()->user()->role === 'customer', 403);
    }

    private function owned(int $id): Pemesanan
    {
        return Pemesanan::with(['paket', 'penumpang', 'pembayaran', 'penugasan.driver', 'penugasan.kendaraan'])
            ->where('user_id', auth()->id())
            ->findOrFail($id);
    }

    private function expire(Pemesanan $order): void
    {
        if ($order->status === 'belum_terbayar' && $order->batas_waktu_pembayaran->isPast()) {
            $order->update(['status' => 'kedaluwarsa']);
        }
    }

    public function katalog(Request $request)
    {
        $pakets = PaketTravel::query()
            ->whereDate('tanggal_berangkat', '>=', today())
            ->withSum(['pemesanan as terpakai_kuota' => function ($q) {
                $q->whereNotIn('status', ['ditolak', 'kedaluwarsa'])
                    ->where(fn ($sub) => $sub->where('status', '!=', 'belum_terbayar')
                        ->orWhere('batas_waktu_pembayaran', '>', now()));
            }], 'jumlah_peserta')
            ->when($request->filled('cari'), function ($q) use ($request) {
                $cari = $request->query('cari');
                $q->where(fn ($sub) => $sub->where('nama_paket', 'like', '%'.$cari.'%')
                    ->orWhere('tujuan', 'like', '%'.$cari.'%'));
            })
            ->when($request->filled('tujuan'), fn ($q) => $q->where('tujuan', $request->query('tujuan')))
            ->when($request->filled('bulan'), function ($q) use ($request) {
                $bulan = $request->query('bulan'); // format YYYY-MM
                if (preg_match('/^\d{4}-\d{2}$/', (string) $bulan)) {
                    [$y, $m] = explode('-', $bulan);
                    $q->whereYear('tanggal_berangkat', $y)->whereMonth('tanggal_berangkat', $m);
                }
            })
            ->orderBy('tanggal_berangkat')
            ->paginate(9)
            ->withQueryString();

        $tujuanList = PaketTravel::whereDate('tanggal_berangkat', '>=', today())
            ->select('tujuan')->distinct()->orderBy('tujuan')->pluck('tujuan');

        $bulanList = PaketTravel::whereDate('tanggal_berangkat', '>=', today())
            ->orderBy('tanggal_berangkat')
            ->get(['tanggal_berangkat'])
            ->map(fn ($p) => $p->tanggal_berangkat->format('Y-m'))
            ->unique()
            ->values();

        return view('travel.katalog', compact('pakets', 'tujuanList', 'bulanList'));
    }

    public function detail(PaketTravel $paket)
    {
        $tersedia = $paket->sisaKuota();

        return view('travel.detail', compact('paket', 'tersedia'));
    }

    public function form(PaketTravel $paket)
    {
        $this->customer();
        abort_if($paket->tanggal_berangkat->lt(today()), 404);
        $tersedia = $paket->sisaKuota();
        $biayaAdmin = self::BIAYA_ADMIN;

        return view('travel.form', compact('paket', 'tersedia', 'biayaAdmin'));
    }

    public function checkout(Request $request, PaketTravel $paket)
    {
        $this->customer();

        $data = $request->validate([
            'jumlah_peserta' => ['required', 'integer', 'min:1', 'max:30'],
            'email_pemesan' => ['required', 'email', 'max:255'],
            'telepon_pemesan' => ['required', 'string', 'max:20'],
            'penumpang' => ['required', 'array'],
            'penumpang.*.nama_lengkap' => ['required', 'string', 'max:120'],
            'penumpang.*.nik' => ['required', 'digits:16'],
            'penumpang.*.tanggal_lahir' => ['required', 'date', 'before:today'],
        ]);

        if (count($data['penumpang']) !== (int) $data['jumlah_peserta']) {
            throw ValidationException::withMessages(['jumlah_peserta' => 'Jumlah data penumpang harus sama dengan jumlah peserta.']);
        }

        $order = DB::transaction(function () use ($paket, $data) {
            $locked = PaketTravel::whereKey($paket->id)->lockForUpdate()->firstOrFail();

            if ($locked->tanggal_berangkat->lt(today())) {
                throw ValidationException::withMessages(['jumlah_peserta' => 'Tanggal keberangkatan telah lewat.']);
            }

            Pemesanan::where('paket_travel_id', $locked->id)
                ->where('status', 'belum_terbayar')
                ->where('batas_waktu_pembayaran', '<=', now())
                ->update(['status' => 'kedaluwarsa']);

            $terpakai = Pemesanan::where('paket_travel_id', $locked->id)
                ->whereNotIn('status', ['ditolak', 'kedaluwarsa'])
                ->sum('jumlah_peserta');

            if ($terpakai + $data['jumlah_peserta'] > $locked->kuota) {
                throw ValidationException::withMessages(['jumlah_peserta' => 'Sisa kuota tidak mencukupi.']);
            }

            $harga = $locked->harga;
            $admin = self::BIAYA_ADMIN;

            $order = Pemesanan::create([
                'kode_pemesanan' => 'TRV-'.strtoupper(Str::random(8)),
                'user_id' => auth()->id(),
                'paket_travel_id' => $locked->id,
                'tanggal_berangkat' => $locked->tanggal_berangkat,
                'jumlah_peserta' => $data['jumlah_peserta'],
                'harga_satuan' => $harga,
                'biaya_admin' => $admin,
                'total_pembayaran' => ($harga * $data['jumlah_peserta']) + $admin,
                'email_pemesan' => $data['email_pemesan'],
                'telepon_pemesan' => $data['telepon_pemesan'],
                'status' => 'belum_terbayar',
                'batas_waktu_pembayaran' => now()->addHours(24),
            ]);

            foreach ($data['penumpang'] as $penumpang) {
                $order->penumpang()->create($penumpang);
            }

            Pembayaran::create([
                'pemesanan_id' => $order->id,
                'nomor_invoice' => 'INV-'.strtoupper(Str::random(10)),
                'nominal' => $order->total_pembayaran,
                'metode_pembayaran' => 'QRIS',
                'status' => 'belum_dibayar',
            ]);

            return $order;
        }, 3);

        return redirect()->route('customer.pesanan.show', $order)
            ->with('success', 'Pemesanan berhasil dibuat! Silakan lakukan pembayaran sebelum batas waktu.');
    }

    public function pesanan()
    {
        $this->customer();
        $pesanans = Pemesanan::with('paket', 'pembayaran')
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(10);

        foreach ($pesanans as $item) {
            $this->expire($item);
        }

        return view('travel.pesanan', compact('pesanans'));
    }

    public function show(Pemesanan $pemesanan)
    {
        $this->customer();
        $pemesanan = $this->owned($pemesanan->id);
        $this->expire($pemesanan);

        return view('travel.show', compact('pemesanan'));
    }

    public function edit(Pemesanan $pemesanan)
    {
        $this->customer();
        $pemesanan = $this->owned($pemesanan->id);
        $this->expire($pemesanan);

        if ($pemesanan->status !== 'belum_terbayar') {
            return redirect()->route('customer.pesanan.show', $pemesanan)
                ->with('error', 'Data penumpang hanya dapat diubah saat status belum terbayar.');
        }

        return view('travel.edit', compact('pemesanan'));
    }

    public function update(Request $request, Pemesanan $pemesanan)
    {
        $this->customer();

        $data = $request->validate([
            'email_pemesan' => ['required', 'email', 'max:255'],
            'telepon_pemesan' => ['required', 'string', 'max:20'],
            'penumpang' => ['required', 'array'],
            'penumpang.*.id' => ['required', 'integer'],
            'penumpang.*.nama_lengkap' => ['required', 'string', 'max:120'],
            'penumpang.*.nik' => ['required', 'digits:16'],
            'penumpang.*.tanggal_lahir' => ['required', 'date', 'before:today'],
        ]);

        DB::transaction(function () use ($pemesanan, $data) {
            $order = Pemesanan::with('penumpang')->where('user_id', auth()->id())->lockForUpdate()->findOrFail($pemesanan->id);
            $this->expire($order);

            if ($order->status !== 'belum_terbayar') {
                throw ValidationException::withMessages(['email_pemesan' => 'Pesanan ini tidak dapat diubah lagi.']);
            }

            if (count($data['penumpang']) !== (int) $order->jumlah_peserta) {
                throw ValidationException::withMessages(['penumpang' => 'Jumlah data penumpang tidak sesuai pesanan.']);
            }

            $order->update([
                'email_pemesan' => $data['email_pemesan'],
                'telepon_pemesan' => $data['telepon_pemesan'],
            ]);

            foreach ($data['penumpang'] as $pData) {
                $penumpang = $order->penumpang()->whereKey($pData['id'])->firstOrFail();
                $penumpang->update([
                    'nama_lengkap' => $pData['nama_lengkap'],
                    'nik' => $pData['nik'],
                    'tanggal_lahir' => $pData['tanggal_lahir'],
                ]);
            }
        });

        return redirect()->route('customer.pesanan.show', $pemesanan)
            ->with('success', 'Data penumpang berhasil diperbarui.');
    }

    public function destroy(Pemesanan $pemesanan)
    {
        $this->customer();

        DB::transaction(function () use ($pemesanan) {
            $order = Pemesanan::with('pembayaran')->where('user_id', auth()->id())->lockForUpdate()->findOrFail($pemesanan->id);

            if (! in_array($order->status, ['belum_terbayar', 'ditolak', 'kedaluwarsa'], true)) {
                throw ValidationException::withMessages(['status' => 'Pesanan yang sedang diproses atau sudah diverifikasi tidak dapat dihapus.']);
            }

            if ($order->pembayaran?->bukti_pembayaran) {
                Storage::disk('local')->delete($order->pembayaran->bukti_pembayaran);
            }

            $order->pembayaran()?->delete();
            $order->penumpang()->delete();
            $order->delete();
        });

        return redirect()->route('customer.pesanan.index')
            ->with('success', 'Pemesanan berhasil dihapus dan kuota paket dilepas.');
    }

    public function bayar(Pemesanan $pemesanan)
    {
        $this->customer();
        $pemesanan = $this->owned($pemesanan->id);
        $this->expire($pemesanan);

        if ($pemesanan->status !== 'belum_terbayar') {
            return redirect()->route('customer.pesanan.show', $pemesanan)
                ->with('warning', 'Pesanan ini sudah tidak berada pada tahap pembayaran.');
        }

        return view('travel.bayar', compact('pemesanan'));
    }

    public function upload(Request $request, Pemesanan $pemesanan)
    {
        $this->customer();
        $validated = $request->validate([
            'bukti' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
        ]);

        $path = $validated['bukti']->store('bukti-pembayaran', 'local');

        try {
            $order = DB::transaction(function () use ($pemesanan, $path) {
                $order = Pemesanan::where('user_id', auth()->id())->lockForUpdate()->findOrFail($pemesanan->id);

                if ($order->status !== 'belum_terbayar' || $order->batas_waktu_pembayaran->isPast()) {
                    if ($order->status === 'belum_terbayar' && $order->batas_waktu_pembayaran->isPast()) {
                        $order->update(['status' => 'kedaluwarsa']);
                    }
                    throw ValidationException::withMessages(['bukti' => 'Batas waktu pembayaran berakhir atau pesanan tidak dapat dibayar lagi.']);
                }

                $order->pembayaran()->update([
                    'bukti_pembayaran' => $path,
                    'tanggal_bayar' => now(),
                    'status' => 'menunggu_verifikasi',
                    'alasan_penolakan' => null,
                ]);

                $order->update(['status' => 'menunggu_verifikasi_pembayaran']);

                return $order;
            });
        } catch (\Throwable $e) {
            Storage::disk('local')->delete($path);
            throw $e;
        }

        return redirect()->route('customer.pesanan.show', $order)
            ->with('success', 'Bukti pembayaran berhasil dikirim. Menunggu verifikasi Staff.');
    }

    public function profil()
    {
        $this->customer();
        $user = auth()->user();

        return view('travel.profil', compact('user'));
    }

    public function updateProfil(Request $request)
    {
        $this->customer();
        $user = auth()->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nomor_telepon' => ['required', 'string', 'max:20'],
            'nomor_identitas' => ['nullable', 'string', 'max:50'],
            'dokumen_identitas' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
        ]);

        if ($request->hasFile('dokumen_identitas')) {
            $newDoc = $request->file('dokumen_identitas')->store('identitas', 'local');
            if ($user->dokumen_identitas) {
                Storage::disk('local')->delete($user->dokumen_identitas);
            }
            $data['dokumen_identitas'] = $newDoc;
        }

        $user->update($data);

        return redirect()->route('customer.profil.edit')->with('success', 'Profil & identitas berhasil diperbarui.');
    }
}