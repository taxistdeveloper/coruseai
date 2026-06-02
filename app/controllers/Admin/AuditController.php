<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\AuditLog;

class AuditController extends Controller
{
    public function index(): void
    {
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = (int) config('pagination', 15);
        $model = new AuditLog();
        $total = $model->count();
        $pagination = paginate($total, $page, $perPage);
        $logs = $model->recent($perPage, $pagination['offset']);

        view('admin.audit.index', [
            'title'      => 'Журнал аудита',
            'logs'       => $logs,
            'pagination' => $pagination,
        ]);
    }
}
