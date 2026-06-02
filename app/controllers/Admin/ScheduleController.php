<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\Schedule;
use App\Models\ScheduleVersion;
use App\Models\Teacher;
use App\Services\AuditService;
use App\Services\ExcelImportService;

class ScheduleController extends Controller
{
    private Schedule $schedules;
    private AuditService $audit;
    private ExcelImportService $excel;

    public function __construct()
    {
        $this->schedules = new Schedule();
        $this->audit = new AuditService();
        $this->excel = new ExcelImportService();
    }

    public function index(): void
    {
        $search = trim($_GET['q'] ?? '');
        $rows = $this->schedules->adminDashboardRows($search);
        view('admin.schedules.index', [
            'title' => 'Графики',
            'rows'  => $rows,
            'search' => $search,
        ]);
    }

    public function uploadForm(): void
    {
        view('admin.schedules.upload', [
            'title'    => 'Загрузка grafik.xlsm',
            'teachers' => (new Teacher())->allForSelect(),
            'excelOk'  => $this->excel->isAvailable(),
        ]);
    }

    public function upload(): void
    {
        $this->validateCsrf();
        $file = $_FILES['grafik'] ?? null;
        if (!$file || empty($file['name'])) {
            flash('error', 'Выберите файл grafik.xlsm');
            redirect('/admin/schedules/upload');
        }

        $error = $this->excel->validateUpload($file);
        if ($error) {
            flash('error', $error);
            redirect('/admin/schedules/upload');
        }

        try {
            $path = $this->excel->storeUpload($file);
            $abs = ROOT_PATH . '/' . $path;
            $imported = $this->excel->import($abs);

            $teacherId = !empty($_POST['teacher_id']) ? (int) $_POST['teacher_id'] : null;
            $status = $teacherId ? 'assigned' : 'not_assigned';

            $id = $this->schedules->create([
                'title'             => trim($_POST['title'] ?? 'График ' . date('Y-m-d')),
                'file_path'         => $path,
                'original_filename' => $file['name'],
                'deadline'          => $_POST['deadline'] ?: null,
                'status'            => $status,
                'teacher_id'        => $teacherId,
                'assigned_by'       => auth_id(),
                'cell_data'         => $imported['cells'],
                'progress_percent'  => $imported['meta']['percent'] ?? 0,
            ]);

            (new ScheduleVersion())->create([
                'schedule_id' => $id,
                'teacher_id'  => $teacherId,
                'version'     => 1,
                'cell_data'   => $imported['cells'],
                'file_path'   => $path,
                'action'      => 'admin_upload',
                'created_by'  => auth_id(),
            ]);

            $this->audit->record('schedule_upload', 'schedule', $id, $file['name']);
            flash('success', 'График загружен и импортирован.');
            redirect('/admin/schedules/' . $id);
        } catch (\Throwable $e) {
            flash('error', 'Ошибка: ' . $e->getMessage());
            redirect('/admin/schedules/upload');
        }
    }

    public function assignForm(string $id): void
    {
        $schedule = $this->schedules->find((int) $id);
        if (!$schedule) {
            flash('error', 'График не найден.');
            redirect('/admin/schedules');
        }
        view('admin.schedules.assign', [
            'title'    => 'Назначить график',
            'schedule' => $schedule,
            'teachers' => (new Teacher())->allForSelect(),
        ]);
    }

    public function assign(string $id): void
    {
        $this->validateCsrf();
        $schedule = $this->schedules->find((int) $id);
        if (!$schedule) {
            flash('error', 'График не найден.');
            redirect('/admin/schedules');
        }

        $teacherId = (int) ($_POST['teacher_id'] ?? 0);
        if ($teacherId <= 0) {
            flash('error', 'Выберите преподавателя.');
            redirect('/admin/schedules/' . $id . '/assign');
        }

        $this->schedules->update((int) $id, [
            'teacher_id' => $teacherId,
            'status'     => 'assigned',
        ]);

        $version = (new ScheduleVersion())->nextVersion((int) $id);
        (new ScheduleVersion())->create([
            'schedule_id' => (int) $id,
            'teacher_id'  => $teacherId,
            'version'     => $version,
            'action'      => 'assign',
            'created_by'  => auth_id(),
        ]);

        $this->audit->record('schedule_assign', 'schedule', (int) $id);
        flash('success', 'График назначен преподавателю.');
        redirect('/admin');
    }

    public function show(string $id): void
    {
        $schedule = $this->schedules->find((int) $id);
        if (!$schedule) {
            flash('error', 'График не найден.');
            redirect('/admin/schedules');
        }
        $versions = (new ScheduleVersion())->bySchedule((int) $id);
        $cells = json_decode($schedule['cell_data'] ?? '{}', true) ?: [];

        view('admin.schedules.show', [
            'title'    => $schedule['title'],
            'schedule' => $schedule,
            'versions' => $versions,
            'cells'    => $cells,
        ]);
    }

    public function destroy(string $id): void
    {
        $this->validateCsrf();
        $this->schedules->delete((int) $id);
        $this->audit->record('schedule_delete', 'schedule', (int) $id);
        flash('success', 'График удалён.');
        redirect('/admin/schedules');
    }
}
