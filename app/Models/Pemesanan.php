<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pemesanan extends Model
{
    protected $table = 'pemesanan';

    protected $guarded = ['id'];

    protected $casts = [
        'tanggal_berangkat' => 'date',
        'batas_waktu_pembayaran' => 'datetime',
        'harga_satuan' => 'decimal:2',
        'biaya_admin' => 'decimal:2',
        'total_pembayaran' => 'decimal:2',
    ];

    public function paket()
    {
        return $this->belongsTo(PaketTravel::class, 'paket_travel_id')->withTrashed();
    }

    public function pemesan()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function penumpang()
    {
        return $this->hasMany(Penumpang::class);
    }

    public function pembayaran()
    {
        return $this->hasOne(Pembayaran::class);
    }

    public function penugasan()
    {
        return $this->hasOne(Penugasan::class);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'belum_terbayar' => 'Belum Terbayar',
            'menunggu_verifikasi_pembayaran' => 'Menunggu Verifikasi Pembayaran',
            'pembayaran_terverifikasi' => 'Pembayaran Terverifikasi',
            'diverifikasi' => 'Diverifikasi',
            'ditolak' => 'Ditolak',
            'kedaluwarsa' => 'Kedaluwarsa',
            default => str_replace('_', ' ', ucwords((string) $this->status, '_')),
        };
    }

    public function statusTone(): string
    {
        return match ($this->status) {
            'diverifikasi' => 'success',
            'pembayaran_terverifikasi' => 'info',
            'menunggu_verifikasi_pembayaran' => 'warning',
            'ditolak', 'kedaluwarsa' => 'danger',
            default => 'muted',
        };
    }
}