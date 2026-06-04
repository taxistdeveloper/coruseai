<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Services\PracticeDayReportService;

class PracticeReportController extends Controller
{
    public function index(): void
    {
        $date = trim($_GET['date'] ?? '');
        if ($date === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $date = date('Y-m-d');
        }

        $service = new PracticeDayReportService();
        $rows = $service->forDate($date);

        view('admin.practice_report.index', [
            'title' => 'Практики на дату',
            'date'  => $date,
            'rows'  => $rows,
        ]);
    }
}
