<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\Teacher;
use App\Models\Workload;

class DashboardController extends Controller
{
    public function index(): void
    {
        $workloadModel = new Workload();
        $workloadModel->markOverdue();

        $search = trim($_GET['q'] ?? '');
        $teacherId = !empty($_GET['teacher_id']) ? (int) $_GET['teacher_id'] : null;
        $status = trim($_GET['status'] ?? '');

        view('admin.dashboard', [
            'title'        => 'Панель администратора',
            'stats'        => $workloadModel->stats(),
            'rows'         => $workloadModel->dashboardRows($search, $teacherId, $status),
            'search'       => $search,
            'teacherId'    => $teacherId,
            'statusFilter' => $status,
            'teachers'     => (new Teacher())->allForSelect(),
        ]);
    }
}
