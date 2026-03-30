<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaguDetail extends Model
{
    protected $fillable = [
        'pagu_id',
        'pagu_komponen_id',
        'ro',
        'komponen_label',
        'sub_komponen',
        'detail',
        'nama_akun',
        'nominal',
    ];

    public function pagu()
    {
        return $this->belongsTo(Pagu::class);
    }

    public function komponen()
    {
        return $this->belongsTo(PaguKomponen::class, 'pagu_komponen_id');
    }

    public function kegiatanAnggarans()
    {
        return $this->hasMany(KegiatanAnggaran::class);
    }

    public function getDetailLabelAttribute(): string
    {
        return $this->detail ?: $this->nama_akun;
    }
}
