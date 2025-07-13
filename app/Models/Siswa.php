<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Siswa extends Model
{
    protected $table = 'siswas';
    protected $fillable = ['user_id','nama', 'kelas','nis',  'no_hp_ortu'];

    public function kehadiran()
{
    return $this->hasMany(Kehadiran::class);
}

public function user()
{
    return $this->belongsTo(User::class);
}
public function kehadiranHariIni()
{
    return $this->hasOne(Kehadiran::class)->whereDate('tanggal', now());
}



}
