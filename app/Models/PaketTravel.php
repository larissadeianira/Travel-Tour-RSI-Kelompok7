<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaketTravel extends Model
{
    use SoftDeletes;

    protected $table = 'paket_travel';

    protected $fillable = [
        'nama_paket',
        'tujuan',
        'durasi_hari',
        'harga',
        'kuota',
        'tanggal_berangkat',
        'deskripsi',
        'fasilitas',
        'foto',
    ];

    protected $casts = [
        'tanggal_berangkat' => 'date',
        'harga' => 'decimal:2',
    ];

    public function pemesanan()
    {
        return $this->hasMany(Pemesanan::class, 'paket_travel_id');
    }

    public function kuotaTerpakai(): int
    {
        return (int) $this->pemesanan()
            ->whereNotIn('status', ['ditolak', 'kedaluwarsa'])
            ->where(function ($q) {
                $q->where('status', '!=', 'belum_terbayar')
                    ->orWhere('batas_waktu_pembayaran', '>', now());
            })
            ->sum('jumlah_peserta');
    }

    public function sisaKuota(): int
    {
        if (isset($this->attributes['terpakai_kuota'])) {
            return max(0, (int) $this->kuota - (int) $this->attributes['terpakai_kuota']);
        }

        return max(0, (int) $this->kuota - $this->kuotaTerpakai());
    }
}