<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kegiatan extends Model
{
    protected $fillable = [
        'tahun_anggaran', 'kepemilikan', 'pagu_id', 'sasaran_id',
        'nama_kegiatan', 'lokus', 'tanggal_mulai', 'tanggal_selesai',
        'output_kegiatan', 'kendala_kegiatan', 'created_by',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];

    public function pagu(): BelongsTo
    {
        return $this->belongsTo(Pagu::class);
    }

    public function sasaran(): BelongsTo
    {
        return $this->belongsTo(Sasaran::class);
    }

    public function indikators(): BelongsToMany
    {
        return $this->belongsToMany(Indikator::class, 'kegiatan_indikator');
    }

    public function anggarans(): HasMany
    {
        return $this->hasMany(KegiatanAnggaran::class);
    }

    public function dokumens(): HasMany
    {
        return $this->hasMany(KegiatanDokumen::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}