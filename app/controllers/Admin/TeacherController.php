<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\Teacher;
use App\Models\User;
use App\Models\Workload;
use App\Services\AuditService;

class TeacherController extends Controller
{
    private User $users;
    private Teacher $teachers;
    private AuditService $audit;

    public function __construct()
    {
        $this->users = new User();
        $this->teachers = new Teacher();
        $this->audit = new AuditService();
    }

    public function index(): void
    {
        $search = trim($_GET['q'] ?? '');
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = (int) config('pagination', 15);
        $total = $this->users->countTeachers($search);
        $pagination = paginate($total, $page, $perPage);
        $teachers = $this->users->allTeachers($search, $perPage, $pagination['offset']);

        view('admin.teachers.index', [
            'title'      => 'Преподаватели',
            'teachers'   => $teachers,
            'search'     => $search,
            'pagination' => $pagination,
        ]);
    }

    public function create(): void
    {
        view('admin.teachers.form', ['title' => 'Новый преподаватель', 'teacher' => null]);
    }

    public function store(): void
    {
        $this->validateCsrf();
        $data = $this->validated();

        if ($data['fullname'] === '' || $data['login'] === '' || $data['password'] === '') {
            flash('error', 'Заполните ФИО, логин и пароль.');
            redirect('/admin/teachers/create');
        }

        if ($this->users->loginExists($data['login'])) {
            flash('error', 'Логин уже занят.');
            redirect('/admin/teachers/create');
        }

        $userId = $this->users->create([
            'fullname' => $data['fullname'],
            'login'    => $data['login'],
            'password' => password_hash($data['password'], PASSWORD_BCRYPT),
            'role'     => 'teacher',
        ]);
        $teacherId = $this->teachers->create($userId, $data['department'] ?: null);
        $this->audit->record('teacher_create', 'user', $userId);

        if ($data['module_name'] !== '' && $data['deadline'] !== '') {
            $wlId = (new Workload())->create([
                'teacher_id'     => $teacherId,
                'module_name'    => $data['module_name'],
                'study_group'    => $data['study_group'] ?: null,
                'practice_hours' => (int) $data['practice_hours'],
                'deadline'       => $data['deadline'],
                'assigned_by'    => auth_id(),
            ]);
        }

        flash('success', 'Преподаватель и нагрузка добавлены.');
        redirect('/admin/teachers');
    }

    public function edit(string $id): void
    {
        $teacher = $this->teachers->find((int) $id);
        if (!$teacher) {
            flash('error', 'Преподаватель не найден.');
            redirect('/admin/teachers');
        }
        view('admin.teachers.form', [
            'title'     => 'Редактирование',
            'teacher'   => $teacher,
            'workloads' => (new Workload())->byTeacher((int) $id),
        ]);
    }

    public function update(string $id): void
    {
        $this->validateCsrf();
        $teacher = $this->teachers->find((int) $id);
        if (!$teacher) {
            flash('error', 'Преподаватель не найден.');
            redirect('/admin/teachers');
        }

        $data = $this->validated();
        if ($this->users->loginExists($data['login'], (int) $teacher['user_id'])) {
            flash('error', 'Логин уже занят.');
            redirect('/admin/teachers/' . $id . '/edit');
        }

        $userData = [
            'fullname'  => $data['fullname'],
            'login'     => $data['login'],
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
        ];
        if ($data['password'] !== '') {
            $userData['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        }
        $this->users->update((int) $teacher['user_id'], $userData);
        $this->teachers->update((int) $id, [
            'department' => $data['department'],
        ]);
        $this->audit->record('teacher_update', 'teacher', (int) $id);

        flash('success', 'Данные обновлены.');
        redirect('/admin/teachers');
    }

    public function destroy(string $id): void
    {
        $this->validateCsrf();
        $teacher = $this->teachers->find((int) $id);
        if ($teacher) {
            $this->users->delete((int) $teacher['user_id']);
            $this->audit->record('teacher_delete', 'teacher', (int) $id);
        }
        flash('success', 'Преподаватель удалён.');
        redirect('/admin/teachers');
    }

    private function validated(): array
    {
        return [
            'fullname'       => trim($_POST['fullname'] ?? ''),
            'login'          => trim($_POST['login'] ?? ''),
            'password'       => $_POST['password'] ?? '',
            'department'     => trim($_POST['department'] ?? ''),
            'study_group'    => trim($_POST['study_group'] ?? ''),
            'module_name'    => trim($_POST['module_name'] ?? ''),
            'practice_hours' => (int) ($_POST['practice_hours'] ?? 0),
            'deadline'       => trim($_POST['deadline'] ?? ''),
        ];
    }
}
