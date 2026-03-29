<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Indikator extends Model
{
    public const SATUAN_OPTIONS = [
        'persen',
        'indeks',
        'laporan',
        'juknis',
        'peraturan',
        'rancangan',
        'orang',
        'satker',
        'kategori',
        'aplikasi',
        'kajian',
        'buah',
        'unit',
        'kegiatan',
        'jumlah',
        'dokumen',
        'perkara',
        'wilayah',
        'kali',
    ];

    public const INDEX_TARGET_OPTIONS = [
        30 => ['code' => 'D', 'label' => 'D - Sangat kurang (lebih dari 0 s.d 30)'],
        50 => ['code' => 'C', 'label' => 'C - Kurang (lebih dari 30 s.d 50)'],
        60 => ['code' => 'CC', 'label' => 'CC - Cukup (lebih dari 50 s.d 60)'],
        70 => ['code' => 'B', 'label' => 'B - Baik (lebih dari 60 s.d 70)'],
        80 => ['code' => 'BB', 'label' => 'BB - Sangat Baik (lebih dari 70 s.d 80)'],
        90 => ['code' => 'A', 'label' => 'A - Memuaskan (lebih dari 80 s.d 90)'],
        100 => ['code' => 'AA', 'label' => 'AA - Sangat Memuaskan (lebih dari 90 s.d 100)'],
    ];

    protected $fillable = [
        'sasaran_id',
        'nama_indikator',
        'target',
        'satuan',
    ];

    protected $appends = ['target_display', 'target_label'];

    public function sasaran()
    {
        return $this->belongsTo(Sasaran::class);
    }

    public function kegiatans()  {
        return $this->belongsToMany(Kegiatan::class, 'kegiatan_indikator');
    }

    public static function getIndexTargetOptionsForSelect(): array
    {
        $options = [];

        foreach (self::INDEX_TARGET_OPTIONS as $value => $option) {
            $options[] = [
                'value' => (string) $value,
                'code' => $option['code'],
                'label' => $option['label'],
            ];
        }

        return $options;
    }

    public static function normalizeTargetForStorage(?string $satuan, mixed $target): mixed
    {
        if ($satuan === 'indeks') {
            return self::normalizeIndexTarget($target);
        }

        return is_numeric($target) ? $target : null;
    }

    public static function isValidTarget(?string $satuan, mixed $target): bool
    {
        if ($satuan === 'indeks') {
            return self::normalizeIndexTarget($target) !== null;
        }

        return is_numeric($target) && (float) $target >= 0;
    }

    public static function getIndexTargetMeta(mixed $target): ?array
    {
        if (! is_numeric($target)) {
            return null;
        }

        $numericTarget = (float) $target;
        if ($numericTarget < 0) {
            return null;
        }

        foreach (self::INDEX_TARGET_OPTIONS as $upperBound => $option) {
            if ($numericTarget <= (float) $upperBound) {
                return [
                    'value' => (string) $upperBound,
                    'code' => $option['code'],
                    'label' => $option['label'],
                ];
            }
        }

        return null;
    }

    private static function normalizeIndexTarget(mixed $target): ?int
    {
        $meta = self::getIndexTargetMeta($target);

        return $meta ? (int) $meta['value'] : null;
    }

    public function getTargetDisplayAttribute(): ?string
    {
        if ($this->target === null) {
            return null;
        }

        if ($this->satuan === 'indeks') {
            return self::getIndexTargetMeta($this->target)['code'] ?? null;
        }

        $target = (float) $this->target;
        return fmod($target, 1.0) === 0.0
            ? number_format($target, 0, ',', '.')
            : number_format($target, 2, ',', '.');
    }

    public function getTargetLabelAttribute(): ?string
    {
        if ($this->target_display === null || blank($this->satuan)) {
            return null;
        }

        if ($this->satuan === 'indeks') {
            return self::getIndexTargetMeta($this->target)['label'] ?? null;
        }

        return $this->target_display . ' ' . $this->satuan;
    }
}
