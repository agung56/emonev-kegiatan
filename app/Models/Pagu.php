<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pagu extends Model
{
    protected $fillable = ['program', 'kegiatan', 'tahun_anggaran', 'total_nominal', 'keterangan'];

    public function details() {
        return $this->hasMany(PaguDetail::class);
    }

    public function komponens() {
        return $this->hasMany(PaguKomponen::class)->orderBy('id');
    }

    public function kegiatans() {
        return $this->hasMany(Kegiatan::class);
    }

    public function getProgramLabelAttribute(): string
    {
        return $this->program ?: $this->kegiatan;
    }
}
