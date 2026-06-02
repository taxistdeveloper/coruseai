<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Workload;
use App\Services\OnlyOfficeService;
use App\Services\WorkloadDocumentService;

class DocumentController extends Controller
{
    public function downloadWorkload(string $id): void
    {
        $workloadId = (int) $id;
        $token = $_GET['token'] ?? '';

        $oo = new OnlyOfficeService();
        if (!$oo->validateDownloadToken($token, $workloadId)) {
            http_response_code(403);
            echo 'Invalid token';
            exit;
        }

        $relative = (new WorkloadDocumentService())->ensureForWorkload($workloadId);
        $abs = (new WorkloadDocumentService())->absolutePath($relative);
        if (!$abs) {
            http_response_code(404);
            echo 'Document not found';
            exit;
        }

        $workload = (new Workload())->find($workloadId);
        $name = 'grafik_' . ($workload['module_name'] ?? 'workload') . '.docx';
        $name = preg_replace('/[^\w\-\.а-яА-Я ]/u', '_', $name);

        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Content-Disposition: attachment; filename="' . $name . '"');
        header('Access-Control-Allow-Origin: *');
        readfile($abs);
        exit;
    }

    public function callback(): void
    {
        $raw = file_get_contents('php://input');
        $body = json_decode($raw ?: '{}', true);
        if (!is_array($body)) {
            $body = [];
        }

        $result = (new OnlyOfficeService())->processCallback($body);
        header('Content-Type: application/json');
        echo json_encode($result);
        exit;
    }
}
