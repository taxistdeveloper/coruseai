<?php

declare(strict_types=1);

namespace App\Controllers\Teacher;

use App\Core\Controller;
use App\Models\ScheduleTemplate;
use App\Models\Workload;

class DashboardController extends Controller
{
    public function index(): void
    {
        $teacherId = (int) (auth_user()['teacher_id'] ?? 0);
        if ($teacherId <= 0) {
            flash('error', 'Профиль преподавателя не найден.');
            redirect('/login');
        }

        $model = new Workload();
        $model->markOverdue();

        view('teacher.dashboard', [
            'title'    => 'Моя нагрузка',
            'workloads' => $model->byTeacher($teacherId),
            'template' => (new ScheduleTemplate())->active(),
        ]);
    }
}
