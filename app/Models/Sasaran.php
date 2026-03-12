<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sasaran extends Model
{
    protected $table = 'sasarans';
    
    protected $fillable = [
        'nama_sasaran',
        'kepemilikan',
        'tahun_anggaran',
        'is_aktif',
    ];

    protected $casts = [
        'is_aktif' => 'boolean',
    ];

    public function indikators()
    {
        return $this->hasMany(Indikator::class);
    }

    public function kegiatans() {
        return $this->hasMany(Kegiatan::class);
    }
}