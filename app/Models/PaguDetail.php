<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaguDetail extends Model
{
    protected $fillable = ['pagu_id', 'nama_akun', 'nominal'];

    public function pagu()
    {
        return $this->belongsTo(Pagu::class);
    }

    public function kegiatanAnggarans()
    {
        return $this->hasMany(KegiatanAnggaran::class);
    }
}