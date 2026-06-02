<?php

declare(strict_types=1);

namespace App\Controllers\Teacher;

use App\Core\Controller;
use App\Models\Schedule;
use App\Models\ScheduleSubmission;
use App\Models\ScheduleVersion;
use App\Services\AuditService;
use App\Services\ExcelImportService;

class ScheduleController extends Controller
{
    private Schedule $schedules;
    private ScheduleSubmission $submissions;
    private AuditService $audit;

    public function __construct()
    {
        $this->schedules = new Schedule();
        $this->submissions = new ScheduleSubmission();
        $this->audit = new AuditService();
    }

    public function edit(string $id): void
    {
        $schedule = $this->loadOwned((int) $id);
        if ($schedule['status'] === 'submitted') {
            flash('error', 'График уже отправлен. Редактирование недоступно.');
            redirect('/teacher');
        }

        $draft = $this->submissions->findDraft((int) $id, (int) auth_user()['teacher_id']);
        $cells = $draft
            ? (json_decode($draft['cell_data'] ?? '{}', true) ?: [])
            : (json_decode($schedule['cell_data'] ?? '{}', true) ?: []);

        view('teacher.schedules.edit', [
            'title'    => $schedule['title'],
            'schedule' => $schedule,
            'cells'    => $cells,
            'readonly' => false,
        ]);
    }

    public function saveDraft(string $id): void
    {
        $this->validateCsrf();
        $schedule = $this->loadOwned((int) $id);
        if ($schedule['status'] === 'submitted') {
            flash('error', 'График уже отправлен.');
            redirect('/teacher');
        }

        $cells = $this->parseCellsFromPost();
        $teacherId = (int) auth_user()['teacher_id'];
        $progress = ExcelImportService::calcProgress($cells);

        $draft = $this->submissions->findDraft((int) $id, $teacherId);
        if ($draft) {
            $this->submissions->update((int) $draft['id'], [
                'cell_data' => $cells,
                'comment'   => trim($_POST['comment'] ?? ''),
            ]);
        } else {
            $version = $this->submissions->nextVersion((int) $id);
            $this->submissions->create([
                'schedule_id' => (int) $id,
                'teacher_id'  => $teacherId,
                'status'      => 'draft',
                'cell_data'   => $cells,
                'version'     => $version,
                'comment'     => trim($_POST['comment'] ?? ''),
            ]);
        }

        $this->schedules->update((int) $id, [
            'cell_data'        => $cells,
            'status'           => 'in_progress',
            'progress_percent' => $progress,
        ]);

        $ver = (new ScheduleVersion())->nextVersion((int) $id);
        (new ScheduleVersion())->create([
            'schedule_id' => (int) $id,
            'teacher_id'  => $teacherId,
            'version'     => $ver,
            'cell_data'   => $cells,
            'action'      => 'save_draft',
            'created_by'  => auth_id(),
        ]);

        $this->audit->record('schedule_draft', 'schedule', (int) $id);
        flash('success', 'Черновик сохранён (' . $progress . '% заполнено).');
        redirect('/teacher/schedules/' . $id);
    }

    public function submit(string $id): void
    {
        $this->validateCsrf();
        $schedule = $this->loadOwned((int) $id);
        if ($schedule['status'] === 'submitted') {
            flash('error', 'График уже отправлен.');
            redirect('/teacher');
        }

        $cells = $this->parseCellsFromPost();
        $teacherId = (int) auth_user()['teacher_id'];
        $version = $this->submissions->nextVersion((int) $id);

        $this->submissions->create([
            'schedule_id' => (int) $id,
            'teacher_id'  => $teacherId,
            'submit_date' => date('Y-m-d H:i:s'),
            'status'      => 'submitted',
            'cell_data'   => $cells,
            'version'     => $version,
            'comment'     => trim($_POST['comment'] ?? ''),
        ]);

        $this->schedules->update((int) $id, [
            'cell_data'        => $cells,
            'status'           => 'submitted',
            'progress_percent' => 100,
        ]);

        (new ScheduleVersion())->create([
            'schedule_id' => (int) $id,
            'teacher_id'  => $teacherId,
            'version'     => (new ScheduleVersion())->nextVersion((int) $id),
            'cell_data'   => $cells,
            'action'      => 'submit',
            'created_by'  => auth_id(),
        ]);

        $this->audit->record('schedule_submit', 'schedule', (int) $id);
        flash('success', 'График отправлен администратору.');
        redirect('/teacher');
    }

    public function download(string $id): void
    {
        $schedule = $this->loadOwned((int) $id);
        $path = ROOT_PATH . '/' . $schedule['file_path'];
        if (!file_exists($path)) {
            flash('error', 'Файл не найден.');
            redirect('/teacher');
        }
        $name = $schedule['original_filename'] ?? basename($path);
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $name . '"');
        readfile($path);
        exit;
    }

    private function loadOwned(int $id): array
    {
        $schedule = $this->schedules->find($id);
        $teacherId = (int) (auth_user()['teacher_id'] ?? 0);
        if (!$schedule || (int) $schedule['teacher_id'] !== $teacherId) {
            flash('error', 'График не найден или не назначен вам.');
            redirect('/teacher');
        }
        return $schedule;
    }

    private function parseCellsFromPost(): array
    {
        $cells = $_POST['cells'] ?? [];
        if (!is_array($cells)) {
            return [];
        }
        $out = [];
        foreach ($cells as $coord => $value) {
            $out[preg_replace('/[^A-Z0-9]/', '', strtoupper((string) $coord))] = trim((string) $value);
        }
        return $out;
    }
}
