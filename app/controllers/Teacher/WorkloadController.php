<?php

declare(strict_types=1);

namespace App\Controllers\Teacher;

use App\Core\Controller;
use App\Models\Workload;
use App\Models\WorkloadVersion;
use App\Services\AuditService;
use App\Services\KazakhstanCalendarService;
use App\Services\WorkloadScheduleService;

class WorkloadController extends Controller
{
    public function show(string $id): void
    {
        $workload = $this->loadOwned((int) $id);
        $schedule = new WorkloadScheduleService();
        $entries = $schedule->entriesForWorkload($workload);
        $limit = (int) $workload['practice_hours'];
        $hoursSummary = $schedule->hoursSummary($entries, $limit);

        view('teacher.workloads.show', [
            'title'        => $workload['module_name'],
            'workload'     => $workload,
            'readonly'     => $workload['status'] === 'submitted',
            'entries'      => $entries,
            'hoursSummary' => $hoursSummary,
            'holidayDates' => (new KazakhstanCalendarService())->holidayDates(),
        ]);
    }

    public function save(string $id): void
    {
        $this->validateCsrf();
        $workload = $this->loadOwned((int) $id);
        if ($workload['status'] === 'submitted') {
            flash('error', 'График уже сдан.');
            redirect('/teacher/workloads/' . $id);
        }

        $schedule = new WorkloadScheduleService();
        $entries = $schedule->parsePostedEntries($_POST['entries'] ?? []);
        $limit = (int) $workload['practice_hours'];
        $formData = ['entries' => $entries];
        $progress = $schedule->calcProgress($entries, $limit);

        (new Workload())->update((int) $id, [
            'status'           => $progress > 0 ? 'in_progress' : 'assigned',
            'form_data'        => $formData,
            'progress_percent' => $progress,
            'comment'          => trim($_POST['comment'] ?? ''),
        ]);

        (new WorkloadVersion())->create([
            'workload_id' => (int) $id,
            'version'     => (new WorkloadVersion())->nextVersion((int) $id),
            'action'      => 'upload',
            'form_data'   => $formData,
            'created_by'  => auth_id(),
        ]);

        (new AuditService())->record('workload_draft', 'workload', (int) $id);
        flash('success', 'Черновик сохранён.');
        redirect('/teacher/workloads/' . $id);
    }

    public function submit(string $id): void
    {
        $this->validateCsrf();
        $workload = $this->loadOwned((int) $id);
        if ($workload['status'] === 'submitted') {
            flash('error', 'График уже сдан.');
            redirect('/teacher');
        }

        $schedule = new WorkloadScheduleService();
        $entries = $schedule->parsePostedEntries($_POST['entries'] ?? []);
        $limit = (int) $workload['practice_hours'];
        $error = $schedule->validateForSubmit($entries, $limit);
        if ($error) {
            flash('error', $error);
            redirect('/teacher/workloads/' . $id);
        }

        $formData = ['entries' => $entries];
        $progress = $schedule->calcProgress($entries, $limit);

        (new Workload())->update((int) $id, [
            'status'           => 'submitted',
            'form_data'        => $formData,
            'progress_percent' => min(100, $progress),
            'comment'          => trim($_POST['comment'] ?? ''),
            'submitted_at'     => date('Y-m-d H:i:s'),
        ]);

        (new WorkloadVersion())->create([
            'workload_id' => (int) $id,
            'version'     => (new WorkloadVersion())->nextVersion((int) $id),
            'action'      => 'submit',
            'form_data'   => $formData,
            'created_by'  => auth_id(),
        ]);

        (new AuditService())->record('workload_submit', 'workload', (int) $id);
        flash('success', 'График отправлен администратору.');
        redirect('/teacher');
    }

    private function loadOwned(int $id): array
    {
        $workload = (new Workload())->find($id);
        $teacherId = (int) (auth_user()['teacher_id'] ?? 0);
        if (!$workload || (int) $workload['teacher_id'] !== $teacherId) {
            flash('error', 'Нагрузка не найдена.');
            redirect('/teacher');
        }
        return $workload;
    }
}
