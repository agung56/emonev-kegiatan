<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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

    public function normalizedPathFile(): string
    {
        $path = ltrim(str_replace('\\', '/', $this->path_file), '/');

        foreach ([
            'storage/app/public/',
            'public/storage/',
            'storage/',
            'public/',
        ] as $prefix) {
            if (Str::startsWith($path, $prefix)) {
                $path = substr($path, strlen($prefix));
            }
        }

        return ltrim($path, '/');
    }

    public function resolveExistingFilePath(): ?string
    {
        foreach ($this->filePathCandidates() as $candidate) {
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    public function filePathCandidates(): array
    {
        $rawPath = str_replace('\\', '/', $this->path_file);
        $relativePath = $this->normalizedPathFile();

        return array_values(array_filter(array_unique([
            $relativePath !== '' ? Storage::disk('public')->path($relativePath) : null,
            $relativePath !== '' ? public_path('storage/' . $relativePath) : null,
            $relativePath !== '' ? public_path($relativePath) : null,
            $rawPath !== '' ? base_path(ltrim($rawPath, '/')) : null,
            Str::startsWith($rawPath, ['/']) || preg_match('/^[A-Za-z]:[\\\\\\/]/', $this->path_file) ? $this->path_file : null,
        ])));
    }
}
