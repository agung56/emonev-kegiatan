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
        $path = $this->pathFileForLookup();

        foreach ([
            'storage/app/public/',
            'storage/app/',
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
        $rawPath = $this->pathFileForLookup();
        $relativePath = $this->normalizedPathFile();
        $basename = basename($relativePath ?: $rawPath);

        $candidates = [
            $relativePath !== '' ? Storage::disk('public')->path($relativePath) : null,
            $relativePath !== '' ? storage_path('app/public/' . $relativePath) : null,
            $relativePath !== '' ? storage_path('app/' . $relativePath) : null,
            $relativePath !== '' ? public_path('storage/' . $relativePath) : null,
            $relativePath !== '' ? public_path($relativePath) : null,
            $rawPath !== '' ? base_path(ltrim($rawPath, '/')) : null,
            Str::startsWith($rawPath, ['/']) || preg_match('/^[A-Za-z]:[\\\\\\/]/', $this->path_file) ? $this->path_file : null,
            $basename !== '' ? storage_path('app/public/kegiatan_dokumen/' . $basename) : null,
            $basename !== '' ? storage_path('app/kegiatan_dokumen/' . $basename) : null,
            $basename !== '' ? public_path('storage/kegiatan_dokumen/' . $basename) : null,
            $basename !== '' ? public_path('kegiatan_dokumen/' . $basename) : null,
        ];

        if ($basename !== '') {
            foreach ([
                storage_path('app/public/kegiatan_dokumen'),
                storage_path('app/kegiatan_dokumen'),
                public_path('storage/kegiatan_dokumen'),
                public_path('kegiatan_dokumen'),
            ] as $directory) {
                if (is_dir($directory)) {
                    $matches = glob($directory . DIRECTORY_SEPARATOR . '*' . $basename) ?: [];
                    if (!empty($matches)) {
                        $candidates = array_merge($candidates, $matches);
                    }
                }
            }
        }

        return array_values(array_filter(array_unique($candidates)));
    }

    private function pathFileForLookup(): string
    {
        $path = trim(str_replace('\\', '/', (string) $this->path_file));

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            $path = parse_url($path, PHP_URL_PATH) ?: $path;
        }

        return ltrim($path, '/');
    }
}
