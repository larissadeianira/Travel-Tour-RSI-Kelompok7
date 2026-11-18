<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Driver extends Model
{
    use SoftDeletes;

    protected $table = 'driver';

    protected $fillable = [
        'nama',
        'nomor_telepon',
        'nomor_sim',
        'alamat',
        'status',
        'foto',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function penugasan()
    {
        return $this->hasMany(Penugasan::class, 'driver_id');
    }
}