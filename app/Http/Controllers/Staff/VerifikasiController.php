<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use App\Models\Pemesanan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class VerifikasiController extends Controller
{
    private function staff(): void
    {
        abort_unless(auth()->check() && auth()->user()->role === 'staff', 403);
    }

    public function dashboard()
    {
        $this->staff();

        return view('staff.dashboard', [
            'menungguPembayaran' => Pemesanan::where('status', 'menunggu_verifikasi_pembayaran')->count(),
            'menungguPemesanan' => Pemesanan::where('status', 'pembayaran_terverifikasi')->count(),
            'terverifikasiCount' => Pemesanan::where('status', 'diverifikasi')->count(),
            'pelangganCount' => User::where('role', 'customer')->count(),
            'totalPenerimaan' => Pembayaran::where('status', 'terverifikasi')->sum('nominal'),
            'pesananTerbaru' => Pemesanan::with('paket', 'pemesan', 'pembayaran')->latest()->take(6)->get(),
        ]);
    }

    public function index()
    {
        $this->staff();

        return redirect()->route('staff.pembayaran.index');
    }

    public function pembayaranIndex(Request $request)
    {
        $this->staff();

        $query = Pemesanan::with(['paket', 'pemesan', 'pembayaran'])
            ->whereHas('pembayaran');

        if ($request->filled('status')) {
            $status = $request->query('status');
            $query->whereHas('pembayaran', fn ($q) => $q->where('status', $status));
        }

        if ($request->filled('cari')) {
            $cari = $request->query('cari');
            $query->where(fn ($q) => $q->where('kode_pemesanan', 'like', '%'.$cari.'%')
                ->orWhereHas('pemesan', fn ($u) => $u->where('name', 'like', '%'.$cari.'%')));
        }

        $pesanans = $query->latest()->paginate(12)->withQueryString();

        return view('staff.pembayaran', compact('pesanans'));
    }

    public function pemesananIndex(Request $request)
    {
        $this->staff();

        $query = Pemesanan::with(['paket', 'pemesan', 'pembayaran']);

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->filled('cari')) {
            $cari = $request->query('cari');
            $query->where(fn ($q) => $q->where('kode_pemesanan', 'like', '%'.$cari.'%')
                ->orWhereHas('pemesan', fn ($u) => $u->where('name', 'like', '%'.$cari.'%')));
        }

        $pesanans = $query->latest()->paginate(12)->withQueryString();

        return view('staff.pemesanan', compact('pesanans'));
    }

    public function pemesananShow(Pemesanan $pemesanan)
    {
        $this->staff();
        $pemesanan->load(['paket', 'pemesan', 'penumpang', 'pembayaran']);

        return view('staff.pemesanan_detail', compact('pemesanan'));
    }

    public function bukti(Pemesanan $pemesanan)
    {
        $this->staff();
        $payment = $pemesanan->pembayaran;
        abort_unless($payment && $payment->bukti_pembayaran && Storage::disk('local')->exists($payment->bukti_pembayaran), 404);

        return response()->file(Storage::disk('local')->path($payment->bukti_pembayaran));
    }

    public function pembayaran(Request $request, Pemesanan $pemesanan)
    {
        $this->staff();

        $data = $request->validate([
            'keputusan' => ['required', 'in:terverifikasi,ditolak'],
            'alasan' => ['required_if:keputusan,ditolak', 'nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($pemesanan, $data) {
            $order = Pemesanan::whereKey($pemesanan->id)->lockForUpdate()->firstOrFail();

            if ($order->status !== 'menunggu_verifikasi_pembayaran') {
                throw ValidationException::withMessages(['keputusan' => 'Status pesanan sudah berubah.']);
            }

            $pay = $order->pembayaran;
            if (! $pay || $pay->status !== 'menunggu_verifikasi') {
                throw ValidationException::withMessages(['keputusan' => 'Pembayaran belum siap diverifikasi.']);
            }

            if ($data['keputusan'] === 'terverifikasi') {
                $pay->update(['status' => 'terverifikasi', 'alasan_penolakan' => null]);
                $order->update(['status' => 'pembayaran_terverifikasi']);
            } else {
                $pay->update(['status' => 'ditolak', 'alasan_penolakan' => $data['alasan']]);
                $order->update(['status' => 'ditolak', 'alasan_penolakan' => $data['alasan']]);
            }
        });

        return back()->with('success', 'Status pembayaran berhasil diperbarui.');
    }

    public function pesanan(Request $request, Pemesanan $pemesanan)
    {
        $this->staff();

        $data = $request->validate([
            'keputusan' => ['required', 'in:diverifikasi,ditolak'],
            'link_grup_wa' => ['required_if:keputusan,diverifikasi', 'nullable', 'url:http,https', 'max:2048'],
            'alasan' => ['required_if:keputusan,ditolak', 'nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($pemesanan, $data) {
            $order = Pemesanan::whereKey($pemesanan->id)->lockForUpdate()->firstOrFail();

            if ($data['keputusan'] === 'diverifikasi' && $order->status !== 'pembayaran_terverifikasi') {
                throw ValidationException::withMessages(['keputusan' => 'Verifikasi pemesanan baru dapat dilakukan setelah pembayaran terverifikasi.']);
            }

            if ($data['keputusan'] === 'ditolak' && ! in_array($order->status, ['belum_terbayar', 'menunggu_verifikasi_pembayaran', 'pembayaran_terverifikasi'], true)) {
                throw ValidationException::withMessages(['keputusan' => 'Pemesanan dengan status ini tidak dapat ditolak lagi.']);
            }

            $order->update([
                'status' => $data['keputusan'],
                'link_grup_wa' => $data['keputusan'] === 'diverifikasi' ? $data['link_grup_wa'] : null,
                'alasan_penolakan' => $data['keputusan'] === 'ditolak' ? $data['alasan'] : null,
            ]);
        });

        return redirect()->route('staff.pemesanan.index')->with('success', 'Status pemesanan berhasil diperbarui.');
    }
}