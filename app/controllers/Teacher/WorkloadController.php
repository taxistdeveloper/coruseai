<?php

declare(strict_types=1);

namespace App\Controllers\Teacher;

use App\Core\Controller;
use App\Models\ScheduleTemplate;
use App\Models\Workload;
use App\Models\WorkloadVersion;
use App\Services\AuditService;
use App\Services\FileUploadService;
use App\Services\OnlyOfficeService;
use App\Services\WordOnlineService;
use App\Services\WorkloadDocumentService;

class WorkloadController extends Controller
{
    public function show(string $id): void
    {
        $workload = $this->loadOwned((int) $id);
        $template = (new ScheduleTemplate())->active();

        if (!$template || empty($template['file_path'])) {
            flash('error', 'Шаблон grafik.docx не загружен администратором.');
            redirect('/teacher');
        }

        (new WorkloadDocumentService())->ensureForWorkload((int) $id);

        $readonly = $workload['status'] === 'submitted';
        $oo = new OnlyOfficeService();
        $ooEnabled = $oo->isEnabled() && $oo->documentServerUrl() !== '';
        $editorConfig = null;

        if ($ooEnabled) {
            $editorConfig = $oo->buildEditorConfig(
                (int) $id,
                'grafik_' . $workload['module_name'] . '.docx',
                $readonly ? 'view' : 'edit',
                auth_user() ?? []
            );
        }

        $superdocEnabled = (bool) editor_config('superdoc.enabled', true);
        $wordOnline = new WordOnlineService();

        view('teacher.workloads.show', [
            'title'             => $workload['module_name'],
            'workload'          => $workload,
            'readonly'          => $readonly,
            'ooEnabled'         => $ooEnabled,
            'editorConfig'      => $editorConfig,
            'documentServerUrl' => $oo->documentServerUrl(),
            'superdocEnabled'   => $superdocEnabled,
            'docUrl'            => base_url('teacher/workloads/' . $id . '/file'),
            'uploadUrl'         => base_url('teacher/workloads/' . $id . '/upload'),
            'defaultTab'        => editor_config('default_tab', 'superdoc'),
            'wordOnline'        => $wordOnline,
            'wordViewerUrl'     => $wordOnline->viewerEmbedUrl((int) $id),
            'wordOnlineReady'   => $wordOnline->canUseViewer(),
        ]);
    }

    public function file(string $id): void
    {
        $workload = $this->loadOwned((int) $id);
        $relative = (new WorkloadDocumentService())->ensureForWorkload((int) $id);
        $abs = (new WorkloadDocumentService())->absolutePath($relative);

        if (!$abs) {
            http_response_code(404);
            echo 'Not found';
            exit;
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Content-Disposition: inline; filename="grafik.docx"');
        header('Cache-Control: no-cache');
        readfile($abs);
        exit;
    }

    public function download(string $id): void
    {
        $workload = $this->loadOwned((int) $id);
        $relative = (new WorkloadDocumentService())->ensureForWorkload((int) $id);
        $abs = (new WorkloadDocumentService())->absolutePath($relative);

        if (!$abs) {
            flash('error', 'Файл графика не найден.');
            redirect('/teacher/workloads/' . $id);
        }

        $name = 'grafik_' . preg_replace('/[^\w\-\.а-яА-Я ]/u', '_', $workload['module_name']) . '.docx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Content-Disposition: attachment; filename="' . $name . '"');
        readfile($abs);
        exit;
    }

    public function upload(string $id): void
    {
        $this->validateCsrf();
        $workload = $this->loadOwned((int) $id);

        if ($workload['status'] === 'submitted') {
            flash('error', 'График уже сдан.');
            redirect('/teacher/workloads/' . $id);
        }

        $file = $_FILES['grafik'] ?? null;
        if (!$file || empty($file['name'])) {
            flash('error', 'Выберите заполненный файл .docx');
            redirect('/teacher/workloads/' . $id);
        }

        $upload = new FileUploadService();
        $error = $upload->validate($file, 'word');
        if ($error) {
            flash('error', $error);
            redirect('/teacher/workloads/' . $id);
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($ext !== 'docx') {
            flash('error', 'Загрузите файл в формате .docx');
            redirect('/teacher/workloads/' . $id);
        }

        $docService = new WorkloadDocumentService();
        $relative = $docService->ensureForWorkload((int) $id);
        $abs = $docService->absolutePath($relative);

        if (!$abs) {
            flash('error', 'Не удалось сохранить документ.');
            redirect('/teacher/workloads/' . $id);
        }

        if (!move_uploaded_file($file['tmp_name'], $abs)) {
            flash('error', 'Ошибка при сохранении файла.');
            redirect('/teacher/workloads/' . $id);
        }

        $action = $_POST['action'] ?? 'draft';
        $isSubmit = $action === 'submit';

        $update = [
            'status'              => $isSubmit ? 'submitted' : 'in_progress',
            'progress_percent'    => 100,
            'comment'             => trim($_POST['comment'] ?? ''),
            'submitted_filename'  => $file['name'],
        ];
        if ($isSubmit) {
            $update['submitted_at'] = date('Y-m-d H:i:s');
        }
        (new Workload())->update((int) $id, $update);

        $version = (new WorkloadVersion())->nextVersion((int) $id);
        (new WorkloadVersion())->create([
            'workload_id'       => (int) $id,
            'version'           => $version,
            'action'            => $isSubmit ? 'submit' : 'upload',
            'created_by'        => auth_id(),
            'file_path'         => $relative,
            'original_filename' => $file['name'],
        ]);

        (new AuditService())->record($isSubmit ? 'workload_submit' : 'workload_draft', 'workload', (int) $id);

        flash('success', $isSubmit ? 'График отправлен администратору.' : 'Черновик сохранён.');
        redirect($isSubmit ? '/teacher' : '/teacher/workloads/' . $id);
    }

    public function submit(string $id): void
    {
        $this->validateCsrf();
        $workload = $this->loadOwned((int) $id);

        if ($workload['status'] === 'submitted') {
            flash('error', 'График уже сдан.');
            redirect('/teacher');
        }

        $relative = (new WorkloadDocumentService())->ensureForWorkload((int) $id);
        if (!$relative) {
            flash('error', 'Сначала загрузите заполненный grafik.docx');
            redirect('/teacher/workloads/' . $id);
        }

        (new Workload())->update((int) $id, [
            'status'           => 'submitted',
            'submitted_at'     => date('Y-m-d H:i:s'),
            'progress_percent' => 100,
            'comment'          => trim($_POST['comment'] ?? ''),
        ]);

        (new WorkloadVersion())->create([
            'workload_id' => (int) $id,
            'version'     => (new WorkloadVersion())->nextVersion((int) $id),
            'action'      => 'submit',
            'created_by'  => auth_id(),
            'file_path'   => $relative,
        ]);

        (new AuditService())->record('workload_submit', 'workload', (int) $id);
        flash('success', 'График отмечен как сданный.');
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
