<?php

namespace Database\Seeders;

use App\Models\Driver;
use App\Models\Kendaraan;
use App\Models\LaporanTerkirim;
use App\Models\PaketTravel;
use App\Models\Pembayaran;
use App\Models\Pemesanan;
use App\Models\Penugasan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedSampleAssets();

        $pass = Hash::make('password');

        // 1. Akun Demo Semua Role
        $admin = User::updateOrCreate(
            ['email' => 'admin@traveltour.test'],
            [
                'name' => 'Filbert Admin',
                'nomor_telepon' => '081110002001',
                'password' => $pass,
                'role' => 'admin',
            ]
        );

        $staff = User::updateOrCreate(
            ['email' => 'staff@traveltour.test'],
            [
                'name' => 'Rizky Staff',
                'nomor_telepon' => '081110002002',
                'password' => $pass,
                'role' => 'staff',
            ]
        );

        $manager = User::updateOrCreate(
            ['email' => 'manager@traveltour.test'],
            [
                'name' => 'Sari Manager',
                'nomor_telepon' => '081110002003',
                'password' => $pass,
                'role' => 'manager',
            ]
        );

        $driverUser1 = User::updateOrCreate(
            ['email' => 'driver@traveltour.test'],
            [
                'name' => 'Budi Santoso',
                'nomor_telepon' => '081222333444',
                'password' => $pass,
                'role' => 'driver',
            ]
        );

        $driverUser2 = User::updateOrCreate(
            ['email' => 'dika.driver@traveltour.test'],
            [
                'name' => 'Dika Susanto',
                'nomor_telepon' => '081344556677',
                'password' => $pass,
                'role' => 'driver',
            ]
        );

        $andi = User::updateOrCreate(
            ['email' => 'customer@traveltour.test'],
            [
                'name' => 'Andi Pratama',
                'nomor_telepon' => '081234567890',
                'nomor_identitas' => '3573011205990001',
                'dokumen_identitas' => 'identitas/ktp-andi.svg',
                'password' => $pass,
                'role' => 'customer',
            ]
        );

        $siti = User::updateOrCreate(
            ['email' => 'siti@traveltour.test'],
            [
                'name' => 'Siti Rahmawati',
                'nomor_telepon' => '081298765432',
                'nomor_identitas' => '3573024508000002',
                'dokumen_identitas' => 'identitas/ktp-siti.svg',
                'password' => $pass,
                'role' => 'customer',
            ]
        );

        $dewi = User::updateOrCreate(
            ['email' => 'dewi@traveltour.test'],
            [
                'name' => 'Dewi Lestari',
                'nomor_telepon' => '081355667788',
                'nomor_identitas' => null,
                'dokumen_identitas' => null,
                'password' => $pass,
                'role' => 'customer',
            ]
        );

        // Hapus data operasional lama jika re-seed dijalankan tanpa fresh
        if (Pemesanan::count() > 0) {
            return;
        }

        // 2. Paket Travel
        $bali = PaketTravel::create([
            'nama_paket' => 'Bali Hemat 3D2N',
            'tujuan' => 'Bali',
            'durasi_hari' => 3,
            'harga' => 2500000,
            'kuota' => 12,
            'tanggal_berangkat' => now()->addDays(14)->toDateString(),
            'deskripsi' => 'Eksplorasi keindahan Tanah Lot, Uluwatu, Kintamani, dan pantai selatan Bali dengan perjalanan nyaman dan terorganisir.',
            'fasilitas' => "Transportasi AC HiAce PP\nHotel Bintang 3 (2 Malam)\nMakan 6x & Air Mineral\nTiket Masuk Wisata & Tour Leader",
            'foto' => 'paket/bali-hemat.svg',
        ]);

        $bromo = PaketTravel::create([
            'nama_paket' => 'Bromo Sunrise Adventure',
            'tujuan' => 'Bromo',
            'durasi_hari' => 2,
            'harga' => 1200000,
            'kuota' => 14,
            'tanggal_berangkat' => now()->addDays(20)->toDateString(),
            'deskripsi' => 'Saksikan matahari terbit spektakuler dari Penanjakan, kawah Bromo, Pasir Berbisik, dan Bukit Teletubbies.',
            'fasilitas' => "Transportasi PP & Jeep 4x4 Bromo\nHomestay Hangat 1 Malam\nTiket TNBTS & Sarapan\nDokumentasi Perjalanan",
            'foto' => 'paket/bromo-sunrise.svg',
        ]);

        $bromoFull = PaketTravel::create([
            'nama_paket' => 'Bromo Midnight Trip',
            'tujuan' => 'Bromo',
            'durasi_hari' => 1,
            'harga' => 850000,
            'kuota' => 4,
            'tanggal_berangkat' => now()->addDays(10)->toDateString(),
            'deskripsi' => 'Perjalanan singkat malam hingga pagi menuju Golden Sunrise Bromo bersama driver berpengalaman.',
            'fasilitas' => "Penjemputan Kota Malang\nJeep Bromo 4x4\nTiket Masuk & Snack Pagi",
            'foto' => 'paket/bromo-trip.svg',
        ]);

        $bajo = PaketTravel::create([
            'nama_paket' => 'Labuan Bajo Sailing',
            'tujuan' => 'NTT',
            'durasi_hari' => 4,
            'harga' => 4800000,
            'kuota' => 10,
            'tanggal_berangkat' => now()->addDays(30)->toDateString(),
            'deskripsi' => 'Berlayar menyusuri Pulau Padar, Pulau Komodo, Pink Beach, dan Manta Point dengan kapal phinisi modern.',
            'fasilitas' => "Kapal Phinisi Kabin AC\nMakan Fullboard & Alat Snorkeling\nAntar Jemput Bandara Komodo\nGuide Lokal Tersertifikasi",
            'foto' => 'paket/labuan-bajo.svg',
        ]);

        $jogja = PaketTravel::create([
            'nama_paket' => 'Yogyakarta Heritage',
            'tujuan' => 'Yogyakarta',
            'durasi_hari' => 3,
            'harga' => 1850000,
            'kuota' => 15,
            'tanggal_berangkat' => now()->addDays(25)->toDateString(),
            'deskripsi' => 'Wisata budaya Candi Prambanan, Keraton Yogyakarta, Tebing Breksi, hingga kuliner malam Malioboro.',
            'fasilitas' => "Armada Pariwisata Nyaman\nHotel Pusat Kota 2 Malam\nTiket Wisata & Driver Ramah",
            'foto' => 'paket/jogja-heritage.svg',
        ]);

        // 3. Kendaraan
        $hiace = Kendaraan::create([
            'plat_nomor' => 'N 1234 AB',
            'jenis' => 'Mobil',
            'merek' => 'Toyota HiAce Premio',
            'kapasitas' => 14,
            'tahun_produksi' => 2024,
            'status' => 'tersedia',
            'foto' => 'kendaraan/hiace.svg',
        ]);

        $elf = Kendaraan::create([
            'plat_nomor' => 'N 5678 CD',
            'jenis' => 'Bus',
            'merek' => 'Isuzu Elf Long',
            'kapasitas' => 18,
            'tahun_produksi' => 2023,
            'status' => 'tersedia',
            'foto' => 'kendaraan/elf.svg',
        ]);

        $zenix = Kendaraan::create([
            'plat_nomor' => 'L 9012 EF',
            'jenis' => 'Mobil',
            'merek' => 'Toyota Innova Zenix',
            'kapasitas' => 7,
            'tahun_produksi' => 2024,
            'status' => 'digunakan',
            'foto' => 'kendaraan/innova.svg',
        ]);

        Kendaraan::create([
            'plat_nomor' => 'N 7788 GH',
            'jenis' => 'Mobil',
            'merek' => 'Toyota Avanza Veloz',
            'kapasitas' => 6,
            'tahun_produksi' => 2022,
            'status' => 'perawatan',
            'foto' => 'kendaraan/avanza.svg',
        ]);

        // 4. Driver
        $driver1 = Driver::create([
            'nama' => 'Budi Santoso',
            'nomor_telepon' => '081222333444',
            'nomor_sim' => 'SIM9203123456',
            'alamat' => 'Jl. Soekarno Hatta No. 18, Malang',
            'status' => 'aktif',
            'foto' => 'driver/driver-budi.svg',
            'user_id' => $driverUser1->id,
        ]);

        $driver2 = Driver::create([
            'nama' => 'Dika Susanto',
            'nomor_telepon' => '081344556677',
            'nomor_sim' => 'SIM9203654321',
            'alamat' => 'Jl. Merdeka Barat No. 7, Blitar',
            'status' => 'aktif',
            'foto' => 'driver/driver-dika.svg',
            'user_id' => $driverUser2->id,
        ]);

        Driver::create([
            'nama' => 'Celvin Wahyudi',
            'nomor_telepon' => '081234568890',
            'nomor_sim' => 'SIM1244567890',
            'alamat' => 'Jl. Slamet Riyadi No. 41, Solo',
            'status' => 'aktif',
            'foto' => 'driver/driver-celvin.svg',
            'user_id' => null,
        ]);

        // 5. Pemesanan + Penumpang + Pembayaran (Mencakup seluruh alur status)
        $adminFee = 10000;

        // BK-001: Sudah diverifikasi & sudah ada Penugasan (tampil di Driver & Laporan)
        $o1 = Pemesanan::create([
            'kode_pemesanan' => 'TRV-BK001',
            'user_id' => $andi->id,
            'paket_travel_id' => $bali->id,
            'tanggal_berangkat' => $bali->tanggal_berangkat,
            'jumlah_peserta' => 2,
            'harga_satuan' => $bali->harga,
            'biaya_admin' => $adminFee,
            'total_pembayaran' => ($bali->harga * 2) + $adminFee,
            'email_pemesan' => $andi->email,
            'telepon_pemesan' => $andi->nomor_telepon,
            'status' => 'diverifikasi',
            'batas_waktu_pembayaran' => now()->subDays(1),
            'link_grup_wa' => 'https://chat.whatsapp.com/DemoBaliHemat3D2N',
        ]);
        $o1->penumpang()->createMany([
            ['nama_lengkap' => 'Andi Pratama', 'nik' => '3573011205990001', 'tanggal_lahir' => '1999-05-12'],
            ['nama_lengkap' => 'Raka Pratama', 'nik' => '3573011809010004', 'tanggal_lahir' => '2001-09-18'],
        ]);
        Pembayaran::create([
            'pemesanan_id' => $o1->id,
            'nomor_invoice' => 'INV-2026-0001',
            'nominal' => $o1->total_pembayaran,
            'metode_pembayaran' => 'QRIS',
            'bukti_pembayaran' => 'bukti-pembayaran/bukti-lunas.svg',
            'tanggal_bayar' => now()->subDays(2),
            'status' => 'terverifikasi',
        ]);
        Penugasan::create([
            'pemesanan_id' => $o1->id,
            'driver_id' => $driver1->id,
            'kendaraan_id' => $hiace->id,
            'tanggal_mulai' => $bali->tanggal_berangkat->toDateString(),
            'tanggal_selesai' => $bali->tanggal_berangkat->copy()->addDays($bali->durasi_hari - 1)->toDateString(),
            'titik_kumpul' => 'Stasiun Kota Baru Malang (Pintu Timur)',
            'jam_kumpul' => '07:00',
            'catatan' => 'Bawa dokumen manifest peserta dan air mineral cadangan.',
            'status' => 'terjadwal',
        ]);

        // BK-002: Diverifikasi Staff, menunggu Penugasan oleh Admin (muncul di Admin Penugasan!)
        $o2 = Pemesanan::create([
            'kode_pemesanan' => 'TRV-BK002',
            'user_id' => $siti->id,
            'paket_travel_id' => $bromo->id,
            'tanggal_berangkat' => $bromo->tanggal_berangkat,
            'jumlah_peserta' => 3,
            'harga_satuan' => $bromo->harga,
            'biaya_admin' => $adminFee,
            'total_pembayaran' => ($bromo->harga * 3) + $adminFee,
            'email_pemesan' => $siti->email,
            'telepon_pemesan' => $siti->nomor_telepon,
            'status' => 'diverifikasi',
            'batas_waktu_pembayaran' => now()->subDays(1),
            'link_grup_wa' => 'https://chat.whatsapp.com/DemoBromoSunrise',
        ]);
        $o2->penumpang()->createMany([
            ['nama_lengkap' => 'Siti Rahmawati', 'nik' => '3573024508000002', 'tanggal_lahir' => '2000-08-05'],
            ['nama_lengkap' => 'Nadia Putri', 'nik' => '3573025504010003', 'tanggal_lahir' => '2001-04-15'],
            ['nama_lengkap' => 'Larasati Dewi', 'nik' => '3573026111000009', 'tanggal_lahir' => '2000-11-21'],
        ]);
        Pembayaran::create([
            'pemesanan_id' => $o2->id,
            'nomor_invoice' => 'INV-2026-0002',
            'nominal' => $o2->total_pembayaran,
            'metode_pembayaran' => 'QRIS',
            'bukti_pembayaran' => 'bukti-pembayaran/bukti-lunas.svg',
            'tanggal_bayar' => now()->subDay(),
            'status' => 'terverifikasi',
        ]);

        // BK-003: Pembayaran Terverifikasi, menunggu Staff verifikasi pemesanan + isi Link WA
        $o3 = Pemesanan::create([
            'kode_pemesanan' => 'TRV-BK003',
            'user_id' => $andi->id,
            'paket_travel_id' => $bajo->id,
            'tanggal_berangkat' => $bajo->tanggal_berangkat,
            'jumlah_peserta' => 2,
            'harga_satuan' => $bajo->harga,
            'biaya_admin' => $adminFee,
            'total_pembayaran' => ($bajo->harga * 2) + $adminFee,
            'email_pemesan' => $andi->email,
            'telepon_pemesan' => $andi->nomor_telepon,
            'status' => 'pembayaran_terverifikasi',
            'batas_waktu_pembayaran' => now()->addHours(12),
        ]);
        $o3->penumpang()->createMany([
            ['nama_lengkap' => 'Andi Pratama', 'nik' => '3573011205990001', 'tanggal_lahir' => '1999-05-12'],
            ['nama_lengkap' => 'Bagas Saputra', 'nik' => '3573010903990005', 'tanggal_lahir' => '1999-03-09'],
        ]);
        Pembayaran::create([
            'pemesanan_id' => $o3->id,
            'nomor_invoice' => 'INV-2026-0003',
            'nominal' => $o3->total_pembayaran,
            'metode_pembayaran' => 'QRIS',
            'bukti_pembayaran' => 'bukti-pembayaran/bukti-lunas.svg',
            'tanggal_bayar' => now()->subHours(4),
            'status' => 'terverifikasi',
        ]);

        // BK-004: Bukti diunggah, menunggu Staff verifikasi pembayaran
        $o4 = Pemesanan::create([
            'kode_pemesanan' => 'TRV-BK004',
            'user_id' => $siti->id,
            'paket_travel_id' => $jogja->id,
            'tanggal_berangkat' => $jogja->tanggal_berangkat,
            'jumlah_peserta' => 2,
            'harga_satuan' => $jogja->harga,
            'biaya_admin' => $adminFee,
            'total_pembayaran' => ($jogja->harga * 2) + $adminFee,
            'email_pemesan' => $siti->email,
            'telepon_pemesan' => $siti->nomor_telepon,
            'status' => 'menunggu_verifikasi_pembayaran',
            'batas_waktu_pembayaran' => now()->addHours(16),
        ]);
        $o4->penumpang()->createMany([
            ['nama_lengkap' => 'Siti Rahmawati', 'nik' => '3573024508000002', 'tanggal_lahir' => '2000-08-05'],
            ['nama_lengkap' => 'Dimas Nugroho', 'nik' => '3573021002980007', 'tanggal_lahir' => '1998-02-10'],
        ]);
        Pembayaran::create([
            'pemesanan_id' => $o4->id,
            'nomor_invoice' => 'INV-2026-0004',
            'nominal' => $o4->total_pembayaran,
            'metode_pembayaran' => 'QRIS',
            'bukti_pembayaran' => 'bukti-pembayaran/bukti-lunas.svg',
            'tanggal_bayar' => now()->subHours(2),
            'status' => 'menunggu_verifikasi',
        ]);

        // BK-005: Belum terbayar (Customer dapat edit penumpang, bayar QR, atau hapus)
        $o5 = Pemesanan::create([
            'kode_pemesanan' => 'TRV-BK005',
            'user_id' => $andi->id,
            'paket_travel_id' => $bromo->id,
            'tanggal_berangkat' => $bromo->tanggal_berangkat,
            'jumlah_peserta' => 1,
            'harga_satuan' => $bromo->harga,
            'biaya_admin' => $adminFee,
            'total_pembayaran' => $bromo->harga + $adminFee,
            'email_pemesan' => $andi->email,
            'telepon_pemesan' => $andi->nomor_telepon,
            'status' => 'belum_terbayar',
            'batas_waktu_pembayaran' => now()->addHours(20),
        ]);
        $o5->penumpang()->create([
            'nama_lengkap' => 'Andi Pratama',
            'nik' => '3573011205990001',
            'tanggal_lahir' => '1999-05-12',
        ]);
        Pembayaran::create([
            'pemesanan_id' => $o5->id,
            'nomor_invoice' => 'INV-2026-0005',
            'nominal' => $o5->total_pembayaran,
            'metode_pembayaran' => 'QRIS',
            'status' => 'belum_dibayar',
        ]);

        // BK-006: Mengisi penuh kuota Bromo Midnight Trip (kuota 4) + status penugasan berjalan
        $o6 = Pemesanan::create([
            'kode_pemesanan' => 'TRV-BK006',
            'user_id' => $dewi->id,
            'paket_travel_id' => $bromoFull->id,
            'tanggal_berangkat' => $bromoFull->tanggal_berangkat,
            'jumlah_peserta' => 4,
            'harga_satuan' => $bromoFull->harga,
            'biaya_admin' => $adminFee,
            'total_pembayaran' => ($bromoFull->harga * 4) + $adminFee,
            'email_pemesan' => $dewi->email,
            'telepon_pemesan' => $dewi->nomor_telepon,
            'status' => 'diverifikasi',
            'batas_waktu_pembayaran' => now()->subDays(3),
            'link_grup_wa' => 'https://chat.whatsapp.com/DemoBromoMidnight',
        ]);
        $o6->penumpang()->createMany([
            ['nama_lengkap' => 'Dewi Lestari', 'nik' => '3573035001000001', 'tanggal_lahir' => '2000-01-10'],
            ['nama_lengkap' => 'Rian Santoso', 'nik' => '3573031506990002', 'tanggal_lahir' => '1999-06-15'],
            ['nama_lengkap' => 'Zara Permata', 'nik' => '3573036207010003', 'tanggal_lahir' => '2001-07-22'],
            ['nama_lengkap' => 'Grisel Vania', 'nik' => '3573034409000004', 'tanggal_lahir' => '2000-09-04'],
        ]);
        Pembayaran::create([
            'pemesanan_id' => $o6->id,
            'nomor_invoice' => 'INV-2026-0006',
            'nominal' => $o6->total_pembayaran,
            'metode_pembayaran' => 'QRIS',
            'bukti_pembayaran' => 'bukti-pembayaran/bukti-lunas.svg',
            'tanggal_bayar' => now()->subDays(3),
            'status' => 'terverifikasi',
        ]);
        Penugasan::create([
            'pemesanan_id' => $o6->id,
            'driver_id' => $driver1->id,
            'kendaraan_id' => $zenix->id,
            'tanggal_mulai' => $bromoFull->tanggal_berangkat->toDateString(),
            'tanggal_selesai' => $bromoFull->tanggal_berangkat->toDateString(),
            'titik_kumpul' => 'Lobby Hotel Santika Malang',
            'jam_kumpul' => '23:30',
            'catatan' => 'Pastikan peserta membawa jaket tebal.',
            'status' => 'berjalan',
        ]);

        // 6. Riwayat Laporan Terkirim dari Staff ke Manager
        LaporanTerkirim::create([
            'staff_user_id' => $staff->id,
            'periode_awal' => now()->startOfMonth()->toDateString(),
            'periode_akhir' => now()->toDateString(),
            'jumlah_transaksi' => 3,
            'total_nominal' => $o1->total_pembayaran + $o2->total_pembayaran + $o6->total_pembayaran,
        ]);
    }

    private function seedSampleAssets(): void
    {
        $pakets = [
            'paket/bali-hemat.svg' => ['#1D4ED8', '#0EA5E9', 'BALI HEMAT', 'Tanah Lot & Uluwatu'],
            'paket/bromo-sunrise.svg' => ['#EA580C', '#F59E0B', 'BROMO SUNRISE', 'Penanjakan & Kawah'],
            'paket/bromo-trip.svg' => ['#312E81', '#F97316', 'BROMO MIDNIGHT', 'Golden Sunrise'],
            'paket/labuan-bajo.svg' => ['#0F766E', '#38BDF8', 'LABUAN BAJO', 'Padar & Pink Beach'],
            'paket/jogja-heritage.svg' => ['#7C2D12', '#D97706', 'JOGJA HERITAGE', 'Prambanan & Keraton'],
        ];

        foreach ($pakets as $path => [$c1, $c2, $title, $sub]) {
            if (! Storage::disk('public')->exists($path)) {
                Storage::disk('public')->put($path, $this->scenicSvg($c1, $c2, $title, $sub));
            }
        }

        $drivers = [
            'driver/driver-budi.svg' => ['BS', '#2460A9'],
            'driver/driver-dika.svg' => ['DS', '#1E3A8A'],
            'driver/driver-celvin.svg' => ['CW', '#0F766E'],
        ];

        foreach ($drivers as $path => [$initial, $bg]) {
            if (! Storage::disk('public')->exists($path)) {
                Storage::disk('public')->put($path, $this->avatarSvg($initial, $bg));
            }
        }

        $vehicles = [
            'kendaraan/hiace.svg' => ['HiAce Premio', 'N 1234 AB', '#2460A9'],
            'kendaraan/elf.svg' => ['Isuzu Elf Long', 'N 5678 CD', '#1D4ED8'],
            'kendaraan/innova.svg' => ['Innova Zenix', 'L 9012 EF', '#0F766E'],
            'kendaraan/avanza.svg' => ['Avanza Veloz', 'N 7788 GH', '#475569'],
        ];

        foreach ($vehicles as $path => [$name, $plate, $bg]) {
            if (! Storage::disk('public')->exists($path)) {
                Storage::disk('public')->put($path, $this->vehicleSvg($name, $plate, $bg));
            }
        }

        if (! Storage::disk('local')->exists('bukti-pembayaran/bukti-lunas.svg')) {
            Storage::disk('local')->put('bukti-pembayaran/bukti-lunas.svg', $this->receiptSvg());
        }

        foreach (['identitas/ktp-andi.svg' => 'Andi Pratama — 3573011205990001', 'identitas/ktp-siti.svg' => 'Siti Rahmawati — 3573024508000002'] as $path => $label) {
            if (! Storage::disk('local')->exists($path)) {
                Storage::disk('local')->put($path, $this->idCardSvg($label));
            }
        }
    }

    private function scenicSvg(string $c1, string $c2, string $title, string $sub): string
    {
        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 500">
  <defs>
    <linearGradient id="g" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0%" stop-color="{$c1}"/>
      <stop offset="100%" stop-color="{$c2}"/>
    </linearGradient>
  </defs>
  <rect width="800" height="500" fill="url(#g)"/>
  <circle cx="640" cy="115" r="64" fill="#E3FF3B" opacity="0.9"/>
  <path d="M0 390 L190 220 L360 360 L540 190 L800 390 L800 500 L0 500 Z" fill="#252944" opacity="0.35"/>
  <path d="M0 430 L240 280 L430 400 L650 260 L800 410 L800 500 L0 500 Z" fill="#252944" opacity="0.6"/>
  <text x="48" y="420" font-family="sans-serif" font-weight="800" font-size="36" fill="#FFFFFF">{$title}</text>
  <text x="48" y="458" font-family="sans-serif" font-weight="600" font-size="20" fill="#E3FF3B">{$sub}</text>
</svg>
SVG;
    }

    private function avatarSvg(string $initial, string $bg): string
    {
        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 240 240">
  <rect width="240" height="240" rx="28" fill="{$bg}"/>
  <circle cx="120" cy="86" r="38" fill="#E3FF3B"/>
  <path d="M52 206 C52 158 188 158 188 206" fill="#FFFFFF" opacity="0.9"/>
  <text x="120" y="98" text-anchor="middle" font-family="sans-serif" font-weight="800" font-size="34" fill="#252944">{$initial}</text>
</svg>
SVG;
    }

    private function vehicleSvg(string $name, string $plate, string $bg): string
    {
        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 260">
  <rect width="400" height="260" rx="20" fill="{$bg}"/>
  <rect x="65" y="90" width="270" height="82" rx="22" fill="#FFFFFF" opacity="0.95"/>
  <rect x="92" y="106" width="84" height="32" rx="6" fill="#3285EB" opacity="0.35"/>
  <rect x="190" y="106" width="118" height="32" rx="6" fill="#3285EB" opacity="0.35"/>
  <circle cx="122" cy="174" r="22" fill="#252944"/>
  <circle cx="122" cy="174" r="10" fill="#E3FF3B"/>
  <circle cx="278" cy="174" r="22" fill="#252944"/>
  <circle cx="278" cy="174" r="10" fill="#E3FF3B"/>
  <text x="200" y="45" text-anchor="middle" font-family="sans-serif" font-weight="800" font-size="22" fill="#E3FF3B">{$plate}</text>
  <text x="200" y="232" text-anchor="middle" font-family="sans-serif" font-weight="700" font-size="18" fill="#FFFFFF">{$name}</text>
</svg>
SVG;
    }

    private function receiptSvg(): string
    {
        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 480 620">
  <rect width="480" height="620" rx="24" fill="#F8FAFC"/>
  <rect x="28" y="28" width="424" height="564" rx="18" fill="#FFFFFF" stroke="#CBD5E1" stroke-width="2"/>
  <rect x="28" y="28" width="424" height="88" rx="18" fill="#2460A9"/>
  <text x="240" y="80" text-anchor="middle" font-family="sans-serif" font-weight="800" font-size="24" fill="#E3FF3B">QRIS • BUKTI PEMBAYARAN</text>
  <circle cx="240" cy="184" r="36" fill="#5FD76D"/>
  <path d="M224 184 L236 196 L258 172" fill="none" stroke="#FFFFFF" stroke-width="6" stroke-linecap="round"/>
  <text x="240" y="252" text-anchor="middle" font-family="sans-serif" font-weight="800" font-size="22" fill="#252944">Transaksi Berhasil</text>
  <text x="240" y="284" text-anchor="middle" font-family="sans-serif" font-size="16" fill="#64748B">Merchant: TRAVEL TOUR NUSANTARA</text>
  <line x1="60" y1="320" x2="420" y2="320" stroke="#E2E8F0" stroke-dasharray="6 6"/>
  <text x="64" y="366" font-family="sans-serif" font-size="16" fill="#64748B">Metode Pembayaran</text>
  <text x="416" y="366" text-anchor="end" font-family="sans-serif" font-weight="700" font-size="16" fill="#252944">QRIS Nasional</text>
  <text x="64" y="408" font-family="sans-serif" font-size="16" fill="#64748B">Status Sistem</text>
  <text x="416" y="408" text-anchor="end" font-family="sans-serif" font-weight="700" font-size="16" fill="#16A34A">TERKIRIM / VALID</text>
  <text x="64" y="450" font-family="sans-serif" font-size="16" fill="#64748B">Referensi</text>
  <text x="416" y="450" text-anchor="end" font-family="sans-serif" font-weight="700" font-size="16" fill="#252944">QRIS-2026-TRV</text>
  <rect x="60" y="498" width="360" height="56" rx="12" fill="#E3FF3B"/>
  <text x="240" y="532" text-anchor="middle" font-family="sans-serif" font-weight="800" font-size="16" fill="#252944">Simulasi Bukti Pembayaran Demo</text>
</svg>
SVG;
    }

    private function idCardSvg(string $label): string
    {
        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 600 360">
  <rect width="600" height="360" rx="20" fill="#1E3A8A"/>
  <rect x="20" y="20" width="560" height="320" rx="14" fill="#EFF6FF"/>
  <rect x="20" y="20" width="560" height="64" rx="14" fill="#2460A9"/>
  <text x="44" y="60" font-family="sans-serif" font-weight="800" font-size="22" fill="#E3FF3B">DOKUMEN IDENTITAS PELANGGAN</text>
  <rect x="44" y="112" width="120" height="144" rx="12" fill="#CBD5E1"/>
  <circle cx="104" cy="162" r="24" fill="#64748B"/>
  <path d="M68 238 C68 202 140 202 140 238" fill="#64748B"/>
  <text x="192" y="152" font-family="sans-serif" font-weight="800" font-size="22" fill="#252944">{$label}</text>
  <text x="192" y="192" font-family="sans-serif" font-size="16" fill="#475569">Status: Terverifikasi untuk keperluan manifes wisata</text>
  <text x="192" y="232" font-family="sans-serif" font-size="15" fill="#64748B">Dokumen tersimpan privat &amp; hanya dapat diakses Staff/Manager.</text>
</svg>
SVG;
    }
}