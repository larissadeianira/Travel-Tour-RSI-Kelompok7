<?php
// tests/Feature/TravelTourTest.php

namespace Tests\Feature;

use App\Models\Driver;
use App\Models\Kendaraan;
use App\Models\PaketTravel;
use App\Models\Pemesanan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TravelTourTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_katalog_and_detail(): void
    {
        $paket = PaketTravel::create([
            'nama_paket' => 'Bali Test',
            'tujuan' => 'Bali',
            'durasi_hari' => 3,
            'harga' => 1500000,
            'kuota' => 10,
            'tanggal_berangkat' => now()->addDays(5)->toDateString(),
        ]);

        $this->get(route('travel.katalog'))->assertOk()->assertSee('Bali Test');
        $this->get(route('travel.detail', $paket))->assertOk()->assertSee('Sisa 10 Kursi');
    }

    public function test_full_end_to_end_booking_verification_assignment_driver_and_report(): void
    {
        Storage::fake('local');

        $admin = User::create(['name' => 'Admin', 'email' => 'a@t.id', 'password' => 'password', 'role' => 'admin']);
        $staff = User::create(['name' => 'Staff', 'email' => 's@t.id', 'password' => 'password', 'role' => 'staff']);
        $customer = User::create(['name' => 'Cust', 'email' => 'c@t.id', 'nomor_telepon' => '08123', 'password' => 'password', 'role' => 'customer']);
        $driverUser = User::create(['name' => 'Drv', 'email' => 'd@t.id', 'password' => 'password', 'role' => 'driver']);
        $manager = User::create(['name' => 'Mgr', 'email' => 'm@t.id', 'password' => 'password', 'role' => 'manager']);

        $paket = PaketTravel::create([
            'nama_paket' => 'Bromo Test',
            'tujuan' => 'Bromo',
            'durasi_hari' => 2,
            'harga' => 1000000,
            'kuota' => 5,
            'tanggal_berangkat' => now()->addDays(7)->toDateString(),
        ]);
        $driver = Driver::create([
            'nama' => 'Budi Driver',
            'nomor_telepon' => '08111',
            'nomor_sim' => 'SIM001',
            'alamat' => 'Malang',
            'status' => 'aktif',
            'user_id' => $driverUser->id,
        ]);
        $kendaraan = Kendaraan::create([
            'plat_nomor' => 'N 1111 AA',
            'jenis' => 'Mobil',
            'merek' => 'HiAce',
            'kapasitas' => 10,
            'tahun_produksi' => 2024,
            'status' => 'tersedia',
        ]);

        // 1. Customer Checkout (UC-13)
        $this->actingAs($customer)->post(route('customer.pemesanan.store', $paket), [
            'jumlah_peserta' => 2,
            'email_pemesan' => 'c@t.id',
            'telepon_pemesan' => '08123456789',
            'penumpang' => [
                ['nama_lengkap' => 'Peserta Satu', 'nik' => '3573011205990001', 'tanggal_lahir' => '1999-05-12'],
                ['nama_lengkap' => 'Peserta Dua', 'nik' => '3573011205990002', 'tanggal_lahir' => '2000-01-10'],
            ],
        ])->assertRedirect();

        $order = Pemesanan::firstOrFail();
        $this->assertEquals(2010000, (int) $order->total_pembayaran);

        // 2. Customer Edit Data Penumpang (UI-38)
        $firstPassenger = $order->penumpang()->firstOrFail();
        $secondPassenger = $order->penumpang()->skip(1)->firstOrFail();
        $this->actingAs($customer)->put(route('customer.pesanan.update', $order), [
            'email_pemesan' => 'baru@t.id',
            'telepon_pemesan' => '0899999999',
            'penumpang' => [
                ['id' => $firstPassenger->id, 'nama_lengkap' => 'Peserta Revisi', 'nik' => '3573011205990001', 'tanggal_lahir' => '1999-05-12'],
                ['id' => $secondPassenger->id, 'nama_lengkap' => 'Peserta Dua', 'nik' => '3573011205990002', 'tanggal_lahir' => '2000-01-10'],
            ],
        ])->assertRedirect(route('customer.pesanan.show', $order));

        // 3. Customer Upload Bukti Pembayaran (UC-14)
        $this->actingAs($customer)->post(route('customer.pesanan.upload', $order), [
            'bukti' => UploadedFile::fake()->image('bukti.png'),
        ])->assertRedirect(route('customer.pesanan.show', $order));
        $this->assertEquals('menunggu_verifikasi_pembayaran', $order->fresh()->status);

        // 4. Staff Verifikasi Pembayaran & Pemesanan (UC-09, UC-08) & Cek Pelanggan (UC-12)
        $this->actingAs($staff)->post(route('staff.pembayaran', $order), ['keputusan' => 'terverifikasi'])->assertRedirect();
        $this->actingAs($staff)->post(route('staff.pesanan', $order), [
            'keputusan' => 'diverifikasi',
            'link_grup_wa' => 'https://chat.whatsapp.com/TestGrup',
        ])->assertRedirect();
        $this->assertEquals('diverifikasi', $order->fresh()->status);

        $this->actingAs($staff)->get(route('pelanggan.detail', $customer->id))->assertOk()->assertSee($order->kode_pemesanan);

        // 5. Admin Assign Penugasan (UC-07)
        $this->actingAs($admin)->post(route('admin.penugasan.store'), [
            'pemesanan_id' => $order->id,
            'driver_id' => $driver->id,
            'kendaraan_id' => $kendaraan->id,
            'titik_kumpul' => 'Stasiun Malang',
            'jam_kumpul' => '07:00',
        ])->assertRedirect(route('admin.penugasan.index'));

        // 6. Driver Melihat Jadwal Tour (UC-16)
        $this->actingAs($driverUser)->get(route('driver.jadwal.index'))->assertOk()->assertSee('Bromo Test');

        // 7. Staff Kirim Laporan & Manager Tinjau + Ekspor (UC-10, UC-11, UC-17, UC-18)
        $today = now()->toDateString();
        $this->actingAs($staff)->post(route('staff.laporan.kirim'), ['jenis' => 'harian', 'dari' => $today, 'sampai' => $today])->assertRedirect();
        $this->actingAs($manager)->get(route('manager.laporan.index', ['dari' => $today, 'sampai' => $today]))->assertOk()->assertSee($order->kode_pemesanan);
        $this->actingAs($manager)->get(route('manager.laporan.pdf', ['dari' => $today, 'sampai' => $today]))->assertOk();
        $this->actingAs($manager)->get(route('manager.laporan.excel', ['dari' => $today, 'sampai' => $today]))->assertOk();
    }
}