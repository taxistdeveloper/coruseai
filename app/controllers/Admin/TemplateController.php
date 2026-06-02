<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\ScheduleTemplate;
use App\Services\AuditService;
use App\Services\FileUploadService;

class TemplateController extends Controller
{
    public function index(): void
    {
        view('admin.template.index', [
            'title'    => 'Шаблон графика (Word)',
            'template' => (new ScheduleTemplate())->active(),
        ]);
    }

    public function upload(): void
    {
        $this->validateCsrf();
        $file = $_FILES['template'] ?? null;
        if (!$file || empty($file['name'])) {
            flash('error', 'Выберите файл grafik.docx');
            redirect('/admin/template');
        }

        $upload = new FileUploadService();
        $error = $upload->validate($file, 'word');
        if ($error) {
            flash('error', $error);
            redirect('/admin/template');
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($ext !== 'docx') {
            flash('error', 'Для онлайн-редактора нужен формат .docx (сохраните документ в Word как DOCX).');
            redirect('/admin/template');
        }

        try {
            $stored = $upload->store($file, 'templates');
            $id = (new ScheduleTemplate())->create([
                'title'             => trim($_POST['title'] ?? 'Шаблон графика'),
                'file_path'         => $stored['path'],
                'original_filename' => $stored['filename'],
                'uploaded_by'       => auth_id(),
            ]);
            (new AuditService())->record('template_upload', 'template', $id, $stored['filename']);
            flash('success', 'Шаблон .docx загружен. Преподаватели будут редактировать этот документ в браузере.');
        } catch (\Throwable $e) {
            flash('error', $e->getMessage());
        }
        redirect('/admin/template');
    }
}
