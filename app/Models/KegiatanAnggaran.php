<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KegiatanAnggaran extends Model
{
    protected $fillable = ['kegiatan_id', 'pagu_detail_id', 'nominal_digunakan'];

    public function kegiatan()
    {
        return $this->belongsTo(Kegiatan::class);
    }

    public function paguDetail()
    {
        return $this->belongsTo(PaguDetail::class);
    }
}