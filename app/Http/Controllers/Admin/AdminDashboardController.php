<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\Kendaraan;
use App\Models\PaketTravel;
use App\Models\Pembayaran;
use App\Models\Pemesanan;
use App\Models\Penugasan;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $paketCount = PaketTravel::count();
        $driverCount = Driver::count();
        $driverAktifCount = Driver::where('status', 'aktif')->count();
        $driverTidakAktifCount = max(0, $driverCount - $driverAktifCount);

        $kendaraanCount = Kendaraan::count();
        $tersediaCount = Kendaraan::where('status', 'tersedia')->count();
        $digunakanCount = Kendaraan::where('status', 'digunakan')->count();
        $perawatanCount = Kendaraan::where('status', 'perawatan')->count();

        $penugasanTerjadwal = Penugasan::where('status', 'terjadwal')->count();
        $penugasanBerjalan = Penugasan::where('status', 'berjalan')->count();
        $penugasanSelesai = Penugasan::where('status', 'selesai')->count();
        $penugasanTotal = $penugasanTerjadwal + $penugasanBerjalan + $penugasanSelesai;

        $paketTerpopuler = PaketTravel::query()
            ->withSum(['pemesanan as terjual' => fn ($q) => $q->whereNotIn('status', ['ditolak', 'kedaluwarsa'])], 'jumlah_peserta')
            ->orderByDesc('terjual')
            ->orderBy('nama_paket')
            ->take(4)
            ->get();

        // Gabungkan aktivitas terbaru dari pemesanan, pembayaran, dan penugasan
        $aktivitas = collect()
            ->merge(Pemesanan::with('paket', 'pemesan')->latest()->take(3)->get()->map(fn ($o) => [
                'judul' => 'Pemesanan '.$o->kode_pemesanan.' ('.($o->paket?->nama_paket ?? 'Paket').')',
                'sub' => ($o->pemesan?->name ?? 'Customer').' · '.$o->jumlah_peserta.' peserta',
                'waktu' => $o->created_at,
                'tone' => 'info',
            ]))
            ->merge(Penugasan::with('driver', 'pemesanan')->latest()->take(3)->get()->map(fn ($p) => [
                'judul' => 'Penugasan driver '.($p->driver?->nama ?? '-'),
                'sub' => 'Pesanan '.($p->pemesanan?->kode_pemesanan ?? '-').' · '.ucfirst($p->status),
                'waktu' => $p->created_at,
                'tone' => 'success',
            ]))
            ->merge(Pembayaran::with('pemesanan')->where('status', 'menunggu_verifikasi')->latest()->take(2)->get()->map(fn ($pay) => [
                'judul' => 'Bukti bayar '.$pay->nomor_invoice,
                'sub' => 'Menunggu verifikasi Staff',
                'waktu' => $pay->updated_at,
                'tone' => 'warning',
            ]))
            ->sortByDesc('waktu')
            ->take(5)
            ->values();

        return view('admin.dashboard', compact(
            'paketCount',
            'driverCount',
            'driverAktifCount',
            'driverTidakAktifCount',
            'kendaraanCount',
            'tersediaCount',
            'digunakanCount',
            'perawatanCount',
            'penugasanTerjadwal',
            'penugasanBerjalan',
            'penugasanSelesai',
            'penugasanTotal',
            'paketTerpopuler',
            'aktivitas'
        ) + ['judul' => 'Dashboard']);
    }
}