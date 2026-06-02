<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ScheduleTemplate;
use App\Models\Workload;

class WorkloadDocumentService
{
    public function ensureForWorkload(int $workloadId): ?string
    {
        $workload = (new Workload())->find($workloadId);
        if (!$workload) {
            return null;
        }

        if (!empty($workload['document_path'])) {
            $abs = ROOT_PATH . '/' . ltrim($workload['document_path'], '/');
            if (file_exists($abs)) {
                return $workload['document_path'];
            }
        }

        $template = (new ScheduleTemplate())->active();
        if (!$template || empty($template['file_path'])) {
            return null;
        }

        $src = ROOT_PATH . '/' . ltrim($template['file_path'], '/');
        if (!file_exists($src)) {
            return null;
        }

        $dir = UPLOAD_PATH . '/workloads';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $filename = 'workload_' . $workloadId . '.docx';
        $dest = $dir . '/' . $filename;
        if (!copy($src, $dest)) {
            throw new \RuntimeException('Не удалось создать копию документа для нагрузки.');
        }

        $relative = 'storage/uploads/workloads/' . $filename;
        (new Workload())->update($workloadId, ['document_path' => $relative]);

        return $relative;
    }

    public function absolutePath(?string $relative): ?string
    {
        if (!$relative) {
            return null;
        }
        $abs = ROOT_PATH . '/' . ltrim($relative, '/');
        return file_exists($abs) ? $abs : null;
    }
}
