<?php

declare(strict_types=1);

namespace App\Services;

class FileUploadService
{
    public function validate(array $file, string $type = 'word'): ?string
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return 'Ошибка загрузки файла.';
        }
        if ($file['size'] > (int) config('upload.max_size')) {
            return 'Файл слишком большой (макс. 15 МБ).';
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = $type === 'word'
            ? config('upload.word_ext', ['doc', 'docx'])
            : config('upload.allowed_ext', []);

        if (!in_array($ext, $allowed, true)) {
            return 'Допустимые форматы: ' . implode(', ', $allowed);
        }
        return null;
    }

    public function store(array $file, string $subdir): array
    {
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $stored = uniqid('file_', true) . '.' . $ext;
        $destDir = UPLOAD_PATH . '/' . $subdir;
        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }
        $dest = $destDir . '/' . $stored;
        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            throw new \RuntimeException('Не удалось сохранить файл.');
        }
        return [
            'path'     => 'storage/uploads/' . $subdir . '/' . $stored,
            'filename' => $file['name'],
        ];
    }

    public static function absolutePath(string $relativePath): string
    {
        return ROOT_PATH . '/' . ltrim($relativePath, '/');
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
}
