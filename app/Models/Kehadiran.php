<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Siswa;

class Kehadiran extends Model
{
    protected $fillable = ['siswa_id', 'tanggal', 'status'];

        protected $casts = [
        'tanggal' => 'date',   // atau 'datetime:Y-m-d' kalau kolomnya datetime
    ];

public function siswa()
{
    return $this->belongsTo(\App\Models\Siswa::class);
}
}

