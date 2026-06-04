<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\Teacher;
use App\Models\Workload;
use App\Models\WorkloadVersion;
use App\Services\AuditService;
use App\Services\FileUploadService;
use App\Services\WorkloadDocumentService;
use App\Services\WorkloadScheduleService;

class WorkloadController extends Controller
{
    public function index(): void
    {
        $search = trim($_GET['q'] ?? '');
        $workloadModel = new Workload();
        $workloadModel->markOverdue();

        view('admin.workloads.index', [
            'title'    => 'Нагрузки практики',
            'rows'     => $workloadModel->dashboardRows($search),
            'search'   => $search,
            'teachers' => (new Teacher())->allForSelect(),
        ]);
    }

    public function create(): void
    {
        view('admin.workloads.form', [
            'title'    => 'Назначить нагрузку',
            'workload' => null,
            'teachers' => (new Teacher())->allForSelect(),
        ]);
    }

    public function store(): void
    {
        $this->validateCsrf();
        $data = $this->validated();

        if ($data['teacher_id'] <= 0 || $data['module_name'] === '' || $data['deadline'] === '') {
            flash('error', 'Заполните преподавателя, модуль и срок сдачи.');
            redirect('/admin/workloads/create');
        }

        $id = (new Workload())->create([
            'teacher_id'     => $data['teacher_id'],
            'module_name'    => $data['module_name'],
            'study_group'    => $data['study_group'] ?: null,
            'practice_hours' => $data['practice_hours'],
            'deadline'       => $data['deadline'],
            'assigned_by'    => auth_id(),
        ]);

        (new AuditService())->record('workload_create', 'workload', $id);
        flash('success', 'Нагрузка назначена.');
        redirect('/admin/workloads');
    }

    public function show(string $id): void
    {
        $workload = (new Workload())->find((int) $id);
        if (!$workload) {
            flash('error', 'Нагрузка не найдена.');
            redirect('/admin/workloads');
        }

        $schedule = new WorkloadScheduleService();
        $entries = $schedule->entriesForWorkload($workload);
        $entries = array_values(array_filter($entries, fn ($e) => $schedule->rowHasAnyValue($e)));
        $limit = (int) $workload['practice_hours'];

        view('admin.workloads.show', [
            'title'        => $workload['module_name'],
            'workload'     => $workload,
            'entries'      => $entries,
            'hoursSummary' => $schedule->hoursSummary($entries, $limit),
        ]);
    }

    public function edit(string $id): void
    {
        $workload = (new Workload())->find((int) $id);
        if (!$workload) {
            flash('error', 'Нагрузка не найдена.');
            redirect('/admin/workloads');
        }

        view('admin.workloads.form', [
            'title'    => 'Редактировать нагрузку',
            'workload' => $workload,
            'teachers' => (new Teacher())->allForSelect(),
        ]);
    }

    public function update(string $id): void
    {
        $this->validateCsrf();
        $data = $this->validated();

        (new Workload())->update((int) $id, [
            'module_name'    => $data['module_name'],
            'study_group'    => $data['study_group'] ?: null,
            'practice_hours' => $data['practice_hours'],
            'deadline'       => $data['deadline'],
        ]);

        (new AuditService())->record('workload_update', 'workload', (int) $id);
        flash('success', 'Нагрузка обновлена.');
        redirect('/admin/workloads');
    }

    public function destroy(string $id): void
    {
        $this->validateCsrf();
        (new Workload())->delete((int) $id);
        (new AuditService())->record('workload_delete', 'workload', (int) $id);
        flash('success', 'Нагрузка удалена.');
        redirect('/admin/workloads');
    }

    public function file(string $id): void
    {
        $workload = (new Workload())->find((int) $id);
        if (!$workload) {
            http_response_code(404);
            exit;
        }
        $abs = (new WorkloadDocumentService())->absolutePath(
            (new WorkloadDocumentService())->ensureForWorkload((int) $id)
        );
        if (!$abs) {
            http_response_code(404);
            exit;
        }
        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Content-Disposition: inline; filename="grafik.docx"');
        readfile($abs);
        exit;
    }

    public function downloadDoc(string $id): void
    {
        $workload = (new Workload())->find((int) $id);
        if (!$workload) {
            flash('error', 'Не найдено.');
            redirect('/admin/workloads');
        }
        $abs = (new WorkloadDocumentService())->absolutePath(
            (new WorkloadDocumentService())->ensureForWorkload((int) $id)
        );
        if (!$abs) {
            flash('error', 'Документ не найден.');
            redirect('/admin/workloads/' . $id);
        }
        $name = $workload['submitted_filename'] ?? 'grafik.docx';
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $name . '"');
        readfile($abs);
        exit;
    }

    private function validated(): array
    {
        return [
            'teacher_id'     => (int) ($_POST['teacher_id'] ?? 0),
            'module_name'    => trim($_POST['module_name'] ?? ''),
            'study_group'    => trim($_POST['study_group'] ?? ''),
            'practice_hours' => max(0, (int) ($_POST['practice_hours'] ?? 0)),
            'deadline'       => trim($_POST['deadline'] ?? ''),
        ];
    }
}
