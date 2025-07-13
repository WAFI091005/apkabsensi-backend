<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AbsenSession extends Model
{
    use HasFactory;

    protected $fillable = ['token', 'kelas', 'expired_at', 'created_by'];

    public function guru()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
