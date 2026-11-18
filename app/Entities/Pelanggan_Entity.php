<?php

namespace App\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

/** CD-22 (Pelanggan Entity) — UC-12 Memeriksa Data Pelanggan. */
class Pelanggan_Entity
{
    /** PC-35 */
    public function getAll_data_pelanggan(): Collection
    {
        return User::query()
            ->where('role', 'customer')
            ->withCount('pemesanan')
            ->orderBy('name')
            ->get();
    }

    /** PC-36 */
    public function get_cari_pelanggan(string $keyword): Collection
    {
        return User::query()
            ->where('role', 'customer')
            ->withCount('pemesanan')
            ->where(function ($q) use ($keyword) {
                $q->where('name', 'like', '%'.$keyword.'%')
                    ->orWhere('nomor_telepon', 'like', '%'.$keyword.'%')
                    ->orWhere('email', 'like', '%'.$keyword.'%');
            })
            ->orderBy('name')
            ->get();
    }

    /** PC-37 */
    public function get_data_detail_pelanggan(string $idPelanggan): ?User
    {
        return User::query()
            ->where('role', 'customer')
            ->with(['pemesanan' => fn ($q) => $q->with(['paket', 'pembayaran'])->latest()])
            ->find($idPelanggan);
    }
}