<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KegiatanDokumen extends Model
{
    protected $fillable = ['kegiatan_id', 'nama_file', 'path_file', 'tipe_file', 'ukuran_file'];

    public function kegiatan()
    {
        return $this->belongsTo(Kegiatan::class);
    }
}