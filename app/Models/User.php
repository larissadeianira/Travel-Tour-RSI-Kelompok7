<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'nomor_telepon',
        'password',
        'role',
        'nomor_identitas',
        'dokumen_identitas',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function pemesanan()
    {
        return $this->hasMany(Pemesanan::class, 'user_id');
    }

    public function driverProfile()
    {
        return $this->hasOne(Driver::class, 'user_id');
    }
}