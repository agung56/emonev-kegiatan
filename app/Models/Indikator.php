<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Indikator extends Model
{
    protected $fillable = [
        'sasaran_id',
        'nama_indikator',
    ];

    public function sasaran()
    {
        return $this->belongsTo(Sasaran::class);
    }

    public function kegiatans()  {
        return $this->belongsToMany(Kegiatan::class, 'kegiatan_indikator');
    }
}