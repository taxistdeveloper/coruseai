<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Импорт grafik.xlsm / xlsx в структуру ячеек для веб-редактора.
 * Требует: composer install (phpoffice/phpspreadsheet)
 */
class ExcelImportService
{
    private const MAX_ROWS = 200;
    private const MAX_COLS = 30;

    public function isAvailable(): bool
    {
        return class_exists(\PhpOffice\PhpSpreadsheet\IOFactory::class);
    }

    /**
     * @return array{cells: array<string, mixed>, meta: array<string, mixed>}
     */
    public function import(string $filePath): array
    {
        if (!$this->isAvailable()) {
            return $this->importFallback($filePath);
        }

        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $cells = [];
        $filled = 0;
        $total = 0;

        $highestRow = min((int) $sheet->getHighestRow(), self::MAX_ROWS);
        $highestCol = min(
            \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($sheet->getHighestColumn()),
            self::MAX_COLS
        );

        for ($row = 1; $row <= $highestRow; $row++) {
            for ($col = 1; $col <= $highestCol; $col++) {
                $coord = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . $row;
                $value = $sheet->getCell($coord)->getCalculatedValue();
                $str = $this->cellToString($value);
                $cells[$coord] = $str;
                $total++;
                if ($str !== '') {
                    $filled++;
                }
            }
        }

        return [
            'cells' => $cells,
            'meta'  => [
                'rows'    => $highestRow,
                'cols'    => $highestCol,
                'filled'  => $filled,
                'total'   => $total,
                'percent' => $total > 0 ? (int) round(($filled / $total) * 100) : 0,
            ],
        ];
    }

    public function exportCsv(array $rows, string $filename): never
    {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        $out = fopen('php://output', 'w');
        fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
        foreach ($rows as $row) {
            fputcsv($out, $row, ';');
        }
        fclose($out);
        exit;
    }

    public function validateUpload(array $file): ?string
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return 'Ошибка загрузки файла.';
        }
        if ($file['size'] > (int) config('upload.max_size')) {
            return 'Файл слишком большой (макс. 10 МБ).';
        }
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = config('upload.allowed_ext', []);
        if (!in_array($ext, $allowed, true)) {
            return 'Допустимы только файлы: ' . implode(', ', $allowed);
        }
        return null;
    }

    public function storeUpload(array $file, string $subdir = 'schedules'): string
    {
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $name = uniqid('grafik_', true) . '.' . $ext;
        $destDir = UPLOAD_PATH . '/' . $subdir;
        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }
        $dest = $destDir . '/' . $name;
        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            throw new \RuntimeException('Не удалось сохранить файл.');
        }
        return 'storage/uploads/' . $subdir . '/' . $name;
    }

    /**
     * Базовый парсер xlsx/xlsm через ZipArchive (без PhpSpreadsheet).
     */
    private function importFallback(string $filePath): array
    {
        $abs = ROOT_PATH . '/' . ltrim(str_replace('storage/', 'storage/', $filePath), '/');
        if (!str_starts_with($abs, STORAGE_PATH) && file_exists(ROOT_PATH . '/' . $filePath)) {
            $abs = ROOT_PATH . '/' . $filePath;
        }
        if (!file_exists($abs)) {
            $abs = $filePath;
        }

        $cells = [];
        $zip = new \ZipArchive();
        if ($zip->open($abs) !== true) {
            return ['cells' => [], 'meta' => ['rows' => 0, 'cols' => 0, 'filled' => 0, 'total' => 0, 'percent' => 0]];
        }

        $sharedStrings = [];
        $sharedXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($sharedXml) {
            $xml = simplexml_load_string($sharedXml);
            if ($xml) {
                foreach ($xml->si as $si) {
                    $sharedStrings[] = (string) ($si->t ?? $si->r->t ?? '');
                }
            }
        }

        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        if (!$sheetXml) {
            return ['cells' => [], 'meta' => ['rows' => 0, 'cols' => 0, 'filled' => 0, 'total' => 0, 'percent' => 0]];
        }

        $sheet = simplexml_load_string($sheetXml);
        $filled = 0;
        $total = 0;
        if ($sheet && isset($sheet->sheetData->row)) {
            foreach ($sheet->sheetData->row as $row) {
                foreach ($row->c as $c) {
                    $ref = (string) $c['r'];
                    $type = (string) $c['t'];
                    $val = (string) $c->v;
                    if ($type === 's' && isset($sharedStrings[(int) $val])) {
                        $val = $sharedStrings[(int) $val];
                    }
                    $cells[$ref] = $val;
                    $total++;
                    if ($val !== '') {
                        $filled++;
                    }
                }
            }
        }

        return [
            'cells' => $cells,
            'meta'  => [
                'rows'    => self::MAX_ROWS,
                'cols'    => self::MAX_COLS,
                'filled'  => $filled,
                'total'   => max($total, 1),
                'percent' => $total > 0 ? (int) round(($filled / $total) * 100) : 0,
            ],
        ];
    }

    private function cellToString(mixed $value): string
    {
        if ($value === null) {
            return '';
        }
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d H:i');
        }
        return trim((string) $value);
    }

    public static function calcProgress(array $cells): int
    {
        if ($cells === []) {
            return 0;
        }
        $filled = 0;
        foreach ($cells as $v) {
            if (trim((string) $v) !== '') {
                $filled++;
            }
        }
        return (int) round(($filled / count($cells)) * 100);
    }
}
