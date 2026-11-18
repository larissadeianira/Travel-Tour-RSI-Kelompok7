<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\DriverController;
use App\Http\Controllers\Admin\KendaraanController;
use App\Http\Controllers\Admin\PaketTravelController;
use App\Http\Controllers\Admin\PenugasanController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Customer\TravelController;
use App\Http\Controllers\Driver\JadwalTourController;
use App\Http\Controllers\LaporanTransaksiController;
use App\Http\Controllers\Staff\PelangganController;
use App\Http\Controllers\Staff\VerifikasiController;
use App\Http\Middleware\EnsureAdmin;
use App\Http\Middleware\EnsureRole;
use Illuminate\Support\Facades\Route;

// ===============================
// HALAMAN PUBLIK (UMUM)
// ===============================
Route::get('/', fn () => redirect()->route('travel.katalog'));

Route::get('/paket-wisata', [TravelController::class, 'katalog'])
    ->name('travel.katalog');

Route::get('/paket-wisata/{paket}', [TravelController::class, 'detail'])
    ->name('travel.detail');

// ===============================
// LOGIN & REGISTER (UC-01)
// ===============================
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.submit');
});

// ===============================
// AKUN LOGIN
// ===============================
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

// ===============================
// ADMIN (UC-04, UC-05, UC-06, UC-07)
// ===============================
Route::middleware(['auth', EnsureAdmin::class])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        Route::resource('paket', PaketTravelController::class)->except('show');
        Route::resource('driver', DriverController::class)->except('show');
        Route::resource('kendaraan', KendaraanController::class)->except('show');

        Route::get('/penugasan', [PenugasanController::class, 'index'])->name('penugasan.index');
        Route::post('/penugasan', [PenugasanController::class, 'store'])->name('penugasan.store');
        Route::patch('/penugasan/{penugasan}/status', [PenugasanController::class, 'updateStatus'])->name('penugasan.status');
        Route::post('/penugasan/hubungkan-driver', [PenugasanController::class, 'hubungkanDriver'])->name('penugasan.link');
    });

// ===============================
// CUSTOMER (UC-13, UC-14, UC-15)
// ===============================
Route::middleware(['auth', EnsureRole::class.':customer'])
    ->group(function () {
        Route::get('/profil', [TravelController::class, 'profil'])->name('customer.profil.edit');
        Route::put('/profil', [TravelController::class, 'updateProfil'])->name('customer.profil.update');

        Route::get('/pesan/{paket}', [TravelController::class, 'form'])->name('customer.pemesanan.create');
        Route::post('/pesan/{paket}', [TravelController::class, 'checkout'])->name('customer.pemesanan.store');

        Route::get('/pesanan-saya', [TravelController::class, 'pesanan'])->name('customer.pesanan.index');
        Route::get('/pesanan-saya/{pemesanan}', [TravelController::class, 'show'])->name('customer.pesanan.show');
        Route::get('/pesanan-saya/{pemesanan}/edit', [TravelController::class, 'edit'])->name('customer.pesanan.edit');
        Route::put('/pesanan-saya/{pemesanan}', [TravelController::class, 'update'])->name('customer.pesanan.update');
        Route::delete('/pesanan-saya/{pemesanan}', [TravelController::class, 'destroy'])->name('customer.pesanan.destroy');
        Route::get('/pesanan-saya/{pemesanan}/bayar', [TravelController::class, 'bayar'])->name('customer.pesanan.bayar');
        Route::post('/pesanan-saya/{pemesanan}/bukti', [TravelController::class, 'upload'])->name('customer.pesanan.upload');
    });

// ===============================
// STAFF (UC-08, UC-09, UC-10, UC-11)
// ===============================
Route::middleware(['auth', EnsureRole::class.':staff'])
    ->prefix('staff')
    ->name('staff.')
    ->group(function () {
        Route::get('/dashboard', [VerifikasiController::class, 'dashboard'])->name('dashboard');
        Route::get('/verifikasi', [VerifikasiController::class, 'index'])->name('verifikasi');

        Route::get('/pembayaran', [VerifikasiController::class, 'pembayaranIndex'])->name('pembayaran.index');
        Route::get('/pemesanan', [VerifikasiController::class, 'pemesananIndex'])->name('pemesanan.index');
        Route::get('/pemesanan/{pemesanan}', [VerifikasiController::class, 'pemesananShow'])->name('pemesanan.show');

        Route::get('/bukti/{pemesanan}', [VerifikasiController::class, 'bukti'])->name('bukti');
        Route::post('/pembayaran/{pemesanan}', [VerifikasiController::class, 'pembayaran'])->name('pembayaran');
        Route::post('/pemesanan/{pemesanan}', [VerifikasiController::class, 'pesanan'])->name('pesanan');

        Route::get('/laporan', [LaporanTransaksiController::class, 'staff'])->name('laporan.index');
        Route::post('/laporan/kirim', [LaporanTransaksiController::class, 'kirim'])->name('laporan.kirim');
    });

// ===============================
// DRIVER (UC-16)
// ===============================
Route::middleware(['auth', EnsureRole::class.':driver'])
    ->prefix('driver')
    ->name('driver.')
    ->group(function () {
        Route::get('/jadwal-tour', [JadwalTourController::class, 'index'])->name('jadwal.index');
        Route::get('/jadwal-tour/{penugasan}', [JadwalTourController::class, 'show'])->name('jadwal.show');
    });

// ===============================
// MANAGER (UC-17, UC-18)
// ===============================
Route::middleware(['auth', EnsureRole::class.':manager'])
    ->prefix('manager')
    ->name('manager.')
    ->group(function () {
        Route::get('/laporan', [LaporanTransaksiController::class, 'manager'])->name('laporan.index');
        Route::get('/laporan/pdf', [LaporanTransaksiController::class, 'pdf'])->name('laporan.pdf');
        Route::get('/laporan/excel', [LaporanTransaksiController::class, 'excel'])->name('laporan.excel');
    });

// ===============================
// DATA PELANGGAN (UC-12: Staff & Manager)
// ===============================
Route::middleware(['auth', EnsureRole::class.':staff,manager'])
    ->prefix('pelanggan')
    ->name('pelanggan.')
    ->group(function () {
        Route::get('/', [PelangganController::class, 'getAll_pelanggan'])->name('index');
        Route::get('/cari', [PelangganController::class, 'cari_pelanggan'])->name('cari');
        Route::get('/{idPelanggan}/dokumen', [PelangganController::class, 'dokumen'])->name('dokumen');
        Route::get('/{idPelanggan}', [PelangganController::class, 'get_detail_pelanggan'])->name('detail');
    });