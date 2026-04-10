<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KegiatanDokumen extends Model
{
    protected $fillable = ['kegiatan_id', 'nama_file', 'path_file', 'tipe_file', 'ukuran_file'];

    public function kegiatan(): BelongsTo
    {
        return $this->belongsTo(Kegiatan::class);
    }

    public function getViewUrlAttribute(): string
    {
        return route('kegiatans.dokumens.show', [
            'kegiatan' => $this->kegiatan_id,
            'dokumen' => $this->id,
        ]);
    }
}
