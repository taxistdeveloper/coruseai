<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\Teacher;
use App\Models\Workload;
use App\Services\FileUploadService;

class SubmissionController extends Controller
{
    public function index(): void
    {
        $search = trim($_GET['q'] ?? '');
        $teacherId = !empty($_GET['teacher_id']) ? (int) $_GET['teacher_id'] : null;
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = (int) config('pagination', 15);

        $model = new Workload();
        $total = $model->countSubmitted($search, $teacherId);
        $pagination = paginate($total, $page, $perPage);

        view('admin.submissions.index', [
            'title'       => 'Сданные графики',
            'submissions' => $model->submittedList($search, $teacherId, $perPage, $pagination['offset']),
            'teachers'    => (new Teacher())->allForSelect(),
            'search'      => $search,
            'teacherId'   => $teacherId,
            'pagination'  => $pagination,
        ]);
    }

    public function export(): void
    {
        $search = trim($_GET['q'] ?? '');
        $teacherId = !empty($_GET['teacher_id']) ? (int) $_GET['teacher_id'] : null;
        $rows = (new Workload())->submittedList($search, $teacherId, 10000, 0);

        $csv = [['ФИО', 'Модуль', 'Часов', 'Срок', 'Дата сдачи', 'Статус']];
        foreach ($rows as $r) {
            $csv[] = [
                $r['teacher_name'],
                $r['module_name'],
                $r['practice_hours'],
                $r['deadline'],
                $r['submitted_at'] ?? '',
                $r['status'],
            ];
        }
        (new FileUploadService())->exportCsv($csv, 'report_' . date('Y-m-d') . '.csv');
    }
}
