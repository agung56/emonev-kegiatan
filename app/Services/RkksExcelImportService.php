<?php

namespace App\Services;

use DOMDocument;
use DOMElement;
use DOMXPath;
use RuntimeException;
use ZipArchive;

class RkksExcelImportService
{
    private const SPREADSHEET_NS = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';

    private const REQUIRED_HEADERS = [
        'program',
        'nama_kegiatan',
        'ro',
        'komponen_label',
        'sub_komponen',
        'detail',
        'nominal',
    ];

    private const HIERARCHY_HEADERS = [
        'tahun_anggaran',
        'program',
        'keterangan',
        'nama_kegiatan',
        'ro',
        'komponen_label',
        'sub_komponen',
    ];

    private const HEADER_ALIASES = [
        'tahun_anggaran' => ['tahun_anggaran', 'tahun', 'ta'],
        'program' => ['program', 'nama_program', 'program_name'],
        'nama_kegiatan' => ['nama_kegiatan', 'kegiatan', 'nama kegiatan'],
        'ro' => ['ro', 'kro', 'kro_ro', 'ro_kro'],
        'komponen_label' => ['komponen_label', 'komponen', 'nama_komponen', 'label_komponen'],
        'sub_komponen' => ['sub_komponen', 'subkomponen', 'sub_komp', 'sub komponen'],
        'detail' => ['detail', 'detil', 'nama_akun', 'akun', 'detail_anggaran', 'detail_belanja'],
        'nominal' => ['nominal', 'jumlah', 'nilai', 'total', 'pagu', 'alokasi'],
        'keterangan' => ['keterangan', 'catatan', 'notes'],
    ];

    public function parse(string $filePath, ?string $originalFilename = null): array
    {
        if (! is_file($filePath)) {
            throw new RuntimeException('File RKKS Excel tidak ditemukan.');
        }

        $sourceName = $originalFilename ?: basename($filePath);
        $extension = strtolower(pathinfo($sourceName, PATHINFO_EXTENSION));

        $rows = match ($extension) {
            'xlsx' => $this->readXlsxRows($filePath),
            'csv', 'txt' => $this->readCsvRows($filePath),
            default => throw new RuntimeException('Format file tidak didukung. Gunakan file Excel .xlsx atau .csv.'),
        };

        if ($rows === []) {
            throw new RuntimeException('Tidak ada data yang dapat dibaca dari file Excel.');
        }

        $payload = $this->containsTemplateHeaders($rows)
            ? $this->buildPayloadFromTemplateRows($rows, $sourceName)
            : $this->buildPayloadFromHierarchyRows($rows, $sourceName);

        $payload['filename'] = $sourceName;

        return $payload;
    }

    private function containsTemplateHeaders(array $rows): bool
    {
        foreach ($rows as $row) {
            $headers = collect($row)
                ->map(fn ($value) => $this->resolveHeaderName($value))
                ->filter()
                ->values()
                ->all();

            if (count(array_intersect(self::REQUIRED_HEADERS, $headers)) >= 4) {
                return true;
            }
        }

        return false;
    }

    private function buildPayloadFromTemplateRows(array $rows, string $sourceName): array
    {
        [$headers, $dataRows, $headerRowNumber] = $this->extractHeaderAndRows($rows);
        $missingHeaders = array_values(array_diff(self::REQUIRED_HEADERS, array_filter($headers)));

        if ($missingHeaders !== []) {
            throw new RuntimeException('Kolom Excel belum lengkap. Wajib ada: program, nama_kegiatan, ro, komponen, sub_komponen, detail, nominal.');
        }

        $headerIndexes = [];

        foreach ($headers as $index => $header) {
            if ($header === null || $header === '') {
                continue;
            }

            $headerIndexes[$header] = $index;
        }

        $carry = [];
        $programs = [];
        $defaultYear = (int) now()->year;

        foreach ($dataRows as $offset => $row) {
            $rowNumber = $headerRowNumber + $offset + 1;
            $mapped = [];

            foreach ($headerIndexes as $header => $index) {
                $mapped[$header] = $this->stringValue($row[$index] ?? '');
            }

            if ($this->rowIsEmpty($mapped)) {
                continue;
            }

            foreach (self::HIERARCHY_HEADERS as $header) {
                if (($mapped[$header] ?? '') === '' && isset($carry[$header])) {
                    $mapped[$header] = $carry[$header];
                }

                if (($mapped[$header] ?? '') !== '') {
                    $carry[$header] = $mapped[$header];
                }
            }

            $mapped['nominal'] = $this->normalizeAmount($mapped['nominal'] ?? '');
            $mapped['tahun_anggaran'] = $this->extractYear($mapped['tahun_anggaran'] ?? '', $defaultYear);

            if (($mapped['detail'] ?? '') === '' && ($mapped['nominal'] ?? 0.0) === 0.0) {
                continue;
            }

            $missingValues = collect(self::REQUIRED_HEADERS)
                ->filter(fn (string $header) => ($mapped[$header] ?? '') === '' || ($header === 'nominal' && ($mapped['nominal'] ?? 0.0) <= 0))
                ->values()
                ->all();

            if ($missingValues !== []) {
                throw new RuntimeException('Baris Excel '. $rowNumber .' belum lengkap pada kolom: '. implode(', ', $missingValues) .'.');
            }

            $programKey = mb_strtolower($mapped['program']) .'|'. $mapped['tahun_anggaran'];
            $kegiatanKey = mb_strtolower($mapped['nama_kegiatan']) .'|'. mb_strtolower($mapped['ro']) .'|'. mb_strtolower($mapped['komponen_label']);
            $subKomponenKey = mb_strtolower($mapped['sub_komponen']);

            $programs[$programKey] ??= [
                'program' => $mapped['program'],
                'tahun_anggaran' => $mapped['tahun_anggaran'],
                'total_nominal' => 0,
                'keterangan' => $mapped['keterangan'] !== '' ? $mapped['keterangan'] : 'Import RKKS Excel: '. $sourceName,
                'komponen_anggaran' => [],
            ];

            $programs[$programKey]['komponen_anggaran'][$kegiatanKey] ??= [
                'nama_kegiatan' => $mapped['nama_kegiatan'],
                'ro' => $mapped['ro'],
                'komponen_label' => $mapped['komponen_label'],
                'sub_komponens' => [],
            ];

            $programs[$programKey]['komponen_anggaran'][$kegiatanKey]['sub_komponens'][$subKomponenKey] ??= [
                'sub_komponen' => $mapped['sub_komponen'],
                'details' => [],
            ];

            $programs[$programKey]['komponen_anggaran'][$kegiatanKey]['sub_komponens'][$subKomponenKey]['details'][] = [
                'detail' => $mapped['detail'],
                'nominal' => $mapped['nominal'],
            ];

            $programs[$programKey]['total_nominal'] += $mapped['nominal'];
        }

        if ($programs === []) {
            throw new RuntimeException('Tidak ada data pagu yang berhasil dibaca dari file Excel.');
        }

        return $this->finalizePrograms($programs, $defaultYear);
    }

    private function buildPayloadFromHierarchyRows(array $rows, string $sourceName): array
    {
        $defaultYear = $this->extractYear($sourceName, (int) now()->year);
        $programs = [];
        $currentProgramKey = null;
        $currentKegiatan = '';
        $currentRo = '';
        $currentKomponen = '';
        $currentSubKomponen = '';
        $currentAccountCode = '';
        $currentAccountName = '';

        foreach ($rows as $row) {
            $code = $this->stringValue($row[0] ?? '');
            $description = $this->stringValue($row[3] ?? '');
            $detailText = $this->stringValue($row[4] ?? '');
            $total = $this->normalizeAmount($row[9] ?? '');

            if ($this->rowIsEmpty([$code, $description, $detailText, $total])) {
                continue;
            }

            if ($this->isProgramCode($code) && $description !== '') {
                $currentProgramKey = mb_strtolower($code .'|'. $description);
                $programs[$currentProgramKey] ??= [
                    'program' => $description,
                    'tahun_anggaran' => $defaultYear,
                    'total_nominal' => 0,
                    'keterangan' => 'Import RKKS Excel: '. $sourceName,
                    'komponen_anggaran' => [],
                ];

                $currentKegiatan = '';
                $currentRo = '';
                $currentKomponen = '';
                $currentSubKomponen = '';
                $currentAccountCode = '';
                $currentAccountName = '';
                continue;
            }

            if ($currentProgramKey === null || $this->shouldSkipHierarchyRow($code, $description, $detailText)) {
                continue;
            }

            if ($this->isKegiatanCode($code) && $description !== '') {
                $currentKegiatan = $description;
                $currentRo = '';
                $currentKomponen = '';
                $currentSubKomponen = '';
                $currentAccountCode = '';
                $currentAccountName = '';
                continue;
            }

            if ($this->isRoCode($code) && $description !== '') {
                $currentRo = $description;
                $currentKomponen = '';
                $currentSubKomponen = '';
                $currentAccountCode = '';
                $currentAccountName = '';
                continue;
            }

            if ($this->isKomponenCode($code) && $description !== '') {
                $currentKomponen = $description;
                $currentSubKomponen = '';
                $currentAccountCode = '';
                $currentAccountName = '';
                continue;
            }

            if ($this->isSubKomponenCode($code, $description)) {
                $currentSubKomponen = $description;
                $currentAccountCode = '';
                $currentAccountName = '';
                continue;
            }

            if ($this->isAccountCode($code) && $description !== '') {
                $currentAccountCode = $code;
                $currentAccountName = $description;
                continue;
            }

            if (! $this->isDetailRow($code, $description, $detailText, $total)) {
                continue;
            }

            $namaKegiatan = $currentKegiatan !== '' ? $currentKegiatan : 'Kegiatan Utama';
            $ro = $currentRo !== '' ? $currentRo : $namaKegiatan;
            $komponen = $currentKomponen !== '' ? $currentKomponen : $ro;
            $subKomponen = $currentSubKomponen !== '' ? $currentSubKomponen : 'Sub Komponen Utama';
            $detailLabel = $this->composeDetailLabel($currentAccountCode, $currentAccountName, $detailText, $description);

            if ($detailLabel === '' || $total <= 0) {
                continue;
            }

            $kegiatanKey = mb_strtolower($namaKegiatan .'|'. $ro .'|'. $komponen);
            $subKomponenKey = mb_strtolower($subKomponen);

            $programs[$currentProgramKey]['komponen_anggaran'][$kegiatanKey] ??= [
                'nama_kegiatan' => $namaKegiatan,
                'ro' => $ro,
                'komponen_label' => $komponen,
                'sub_komponens' => [],
            ];

            $programs[$currentProgramKey]['komponen_anggaran'][$kegiatanKey]['sub_komponens'][$subKomponenKey] ??= [
                'sub_komponen' => $subKomponen,
                'details' => [],
            ];

            $programs[$currentProgramKey]['komponen_anggaran'][$kegiatanKey]['sub_komponens'][$subKomponenKey]['details'][] = [
                'detail' => $detailLabel,
                'nominal' => $total,
            ];

            $programs[$currentProgramKey]['total_nominal'] += $total;
        }

        if ($programs === []) {
            throw new RuntimeException('Format RKKS Excel belum dikenali. Pastikan file berupa RKKS Excel atau template import yang didukung.');
        }

        return $this->finalizePrograms($programs, $defaultYear);
    }

    private function finalizePrograms(array $programs, int $defaultYear): array
    {
        $programList = collect($programs)
            ->map(function (array $program) {
                $program['komponen_anggaran'] = collect($program['komponen_anggaran'])
                    ->map(function (array $kegiatan) {
                        $kegiatan['sub_komponens'] = array_values($kegiatan['sub_komponens']);

                        return $kegiatan;
                    })
                    ->values()
                    ->all();

                return $program;
            })
            ->values()
            ->all();

        return [
            'tahun_anggaran' => $programList[0]['tahun_anggaran'] ?? $defaultYear,
            'programs' => $programList,
        ];
    }

    private function extractHeaderAndRows(array $rows): array
    {
        foreach ($rows as $index => $row) {
            $headers = collect($row)
                ->map(fn ($value) => $this->resolveHeaderName($value))
                ->filter()
                ->values()
                ->all();

            if (count(array_intersect(self::REQUIRED_HEADERS, $headers)) >= 4) {
                $normalizedHeaders = collect($row)
                    ->map(fn ($value) => $this->resolveHeaderName($value))
                    ->all();

                return [$normalizedHeaders, array_slice($rows, $index + 1), $index + 1];
            }
        }

        throw new RuntimeException('Header Excel tidak dikenali.');
    }

    private function readCsvRows(string $filePath): array
    {
        $handle = fopen($filePath, 'rb');

        if ($handle === false) {
            throw new RuntimeException('File CSV tidak dapat dibuka.');
        }

        $firstLine = fgets($handle);
        rewind($handle);
        $delimiter = $this->detectDelimiter($firstLine ?: '');
        $rows = [];

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            $rows[] = array_map(fn ($value) => $this->stringValue($value), $row);
        }

        fclose($handle);

        return $rows;
    }

    private function readXlsxRows(string $filePath): array
    {
        if (! class_exists(ZipArchive::class)) {
            throw new RuntimeException('Ekstensi ZIP pada server tidak tersedia untuk membaca file XLSX.');
        }

        $zip = new ZipArchive();

        if ($zip->open($filePath) !== true) {
            throw new RuntimeException('File XLSX tidak dapat dibuka.');
        }

        $sheetPath = $this->resolveFirstWorksheetPath($zip);
        $sheetXml = $zip->getFromName($sheetPath);
        $sharedStrings = $this->readSharedStrings($zip);
        $zip->close();

        if (! $sheetXml) {
            throw new RuntimeException('Worksheet pertama pada file XLSX tidak ditemukan.');
        }

        $dom = new DOMDocument();
        $dom->preserveWhiteSpace = false;

        if (! @$dom->loadXML($sheetXml)) {
            throw new RuntimeException('Worksheet XLSX tidak dapat diparse.');
        }

        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('x', self::SPREADSHEET_NS);

        $rows = [];

        foreach ($xpath->query('//x:sheetData/x:row') as $rowNode) {
            $rowCells = [];

            foreach ($xpath->query('./x:c', $rowNode) as $cellNode) {
                if (! $cellNode instanceof DOMElement) {
                    continue;
                }

                $reference = $cellNode->getAttribute('r');
                $index = $this->columnIndexFromReference($reference);
                $rowCells[$index] = $this->extractDomCellValue($xpath, $cellNode, $sharedStrings);
            }

            if ($rowCells === []) {
                continue;
            }

            ksort($rowCells);
            $maxIndex = max(array_keys($rowCells));
            $normalized = [];

            for ($index = 1; $index <= $maxIndex; $index++) {
                $normalized[] = $this->stringValue($rowCells[$index] ?? '');
            }

            $rows[] = $normalized;
        }

        return $rows;
    }

    private function resolveFirstWorksheetPath(ZipArchive $zip): string
    {
        $worksheets = [];

        for ($index = 0; $index < $zip->numFiles; $index++) {
            $name = $zip->getNameIndex($index);

            if (preg_match('#^xl/worksheets/sheet\d+\.xml$#', $name)) {
                $worksheets[] = $name;
            }
        }

        sort($worksheets, SORT_NATURAL);

        if ($worksheets === []) {
            throw new RuntimeException('Worksheet pada file XLSX tidak ditemukan.');
        }

        return $worksheets[0];
    }

    private function readSharedStrings(ZipArchive $zip): array
    {
        $sharedStringsXml = $zip->getFromName('xl/sharedStrings.xml');

        if (! $sharedStringsXml) {
            return [];
        }

        $dom = new DOMDocument();
        $dom->preserveWhiteSpace = false;

        if (! @$dom->loadXML($sharedStringsXml)) {
            return [];
        }

        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('x', self::SPREADSHEET_NS);
        $strings = [];

        foreach ($xpath->query('//x:si') as $itemNode) {
            $parts = [];

            foreach ($xpath->query('./x:t | ./x:r/x:t', $itemNode) as $textNode) {
                $parts[] = (string) $textNode->nodeValue;
            }

            $strings[] = implode('', $parts);
        }

        return $strings;
    }

    private function extractDomCellValue(DOMXPath $xpath, DOMElement $cellNode, array $sharedStrings): string
    {
        $type = $cellNode->getAttribute('t');

        if ($type === 'inlineStr') {
            $parts = [];

            foreach ($xpath->query('./x:is/x:t | ./x:is/x:r/x:t', $cellNode) as $textNode) {
                $parts[] = (string) $textNode->nodeValue;
            }

            return $this->stringValue(implode('', $parts));
        }

        $valueNode = $xpath->query('./x:v', $cellNode)->item(0);
        $value = $valueNode?->nodeValue ?? '';

        return match ($type) {
            's' => $this->stringValue($sharedStrings[(int) $value] ?? ''),
            'b' => $value === '1' ? 'TRUE' : 'FALSE',
            default => $this->stringValue($value),
        };
    }

    private function detectDelimiter(string $line): string
    {
        $delimiters = [',', ';', "\t"];
        $bestDelimiter = ',';
        $bestCount = 0;

        foreach ($delimiters as $delimiter) {
            $count = count(str_getcsv($line, $delimiter));

            if ($count > $bestCount) {
                $bestCount = $count;
                $bestDelimiter = $delimiter;
            }
        }

        return $bestDelimiter;
    }

    private function shouldSkipHierarchyRow(string $code, string $description, string $detailText): bool
    {
        if ($description === '' && $detailText === '' && $code === '') {
            return true;
        }

        if (str_starts_with($description, 'Lokasi :') || str_starts_with($description, '(KPPN.')) {
            return true;
        }

        return false;
    }

    private function isProgramCode(string $value): bool
    {
        return (bool) preg_match('/^\d{3}\.\d{2}\.[A-Z]{2}$/', $value);
    }

    private function isKegiatanCode(string $value): bool
    {
        return (bool) preg_match('/^\d{4}$/', $value);
    }

    private function isRoCode(string $value): bool
    {
        return (bool) preg_match('/^\d{4}\.[A-Z]{3}(?:\.\d{3})?$/', $value);
    }

    private function isKomponenCode(string $value): bool
    {
        return (bool) preg_match('/^\d{3}$/', $value);
    }

    private function isSubKomponenCode(string $value, string $description): bool
    {
        return $description !== '' && (bool) preg_match('/^[A-Z]{1,3}$/', $value);
    }

    private function isAccountCode(string $value): bool
    {
        return (bool) preg_match('/^\d{6}$/', $value);
    }

    private function isDetailRow(string $code, string $description, string $detailText, float $total): bool
    {
        if ($total <= 0) {
            return false;
        }

        if ($code !== '') {
            return false;
        }

        return $detailText !== '' || trim($description) === '-';
    }

    private function composeDetailLabel(string $accountCode, string $accountName, string $detailText, string $description): string
    {
        $detail = $detailText !== '' ? $detailText : trim($description, ' -');
        $account = trim($accountCode .' '. $accountName);

        if ($account !== '' && $detail !== '') {
            return $account .' - '. $detail;
        }

        return $account !== '' ? $account : $detail;
    }

    private function resolveHeaderName(mixed $value): ?string
    {
        $normalized = $this->normalizeHeaderKey($value);

        if ($normalized === '') {
            return null;
        }

        foreach (self::HEADER_ALIASES as $canonical => $aliases) {
            $normalizedAliases = array_map(fn ($alias) => $this->normalizeHeaderKey($alias), $aliases);

            if (in_array($normalized, $normalizedAliases, true)) {
                return $canonical;
            }
        }

        return null;
    }

    private function normalizeHeaderKey(mixed $value): string
    {
        $value = $this->stringValue($value);
        $value = str_replace(['(', ')', '.', '/'], ' ', strtolower($value));
        $value = preg_replace('/[^a-z0-9]+/', '_', $value) ?? '';

        return trim($value, '_');
    }

    private function normalizeAmount(mixed $value): float
    {
        $value = $this->stringValue($value);
        $value = str_replace(['Rp', 'rp', ' '], '', $value);

        if ($value === '') {
            return 0;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        $value = preg_replace('/[^0-9,.-]/', '', $value) ?? '';

        if ($value === '') {
            return 0;
        }

        if (str_contains($value, ',') && str_contains($value, '.')) {
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        } elseif (str_contains($value, ',')) {
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        }

        return (float) $value;
    }

    private function extractYear(string $value, int $defaultYear): int
    {
        if (preg_match('/\b(20\d{2})\b/', $value, $matches)) {
            return (int) $matches[1];
        }

        return $defaultYear;
    }

    private function columnIndexFromReference(string $reference): int
    {
        $letters = preg_replace('/[^A-Z]/', '', strtoupper($reference)) ?: 'A';
        $index = 0;

        foreach (str_split($letters) as $letter) {
            $index = ($index * 26) + (ord($letter) - 64);
        }

        return $index;
    }

    private function rowIsEmpty(array $row): bool
    {
        foreach ($row as $value) {
            if ($this->stringValue($value) !== '') {
                return false;
            }
        }

        return true;
    }

    private function stringValue(mixed $value): string
    {
        return trim((string) $value);
    }
}
