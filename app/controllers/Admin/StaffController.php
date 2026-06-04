<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\User;
use App\Services\AuditService;

class StaffController extends Controller
{
    private User $users;
    private AuditService $audit;

    public function __construct()
    {
        $this->users = new User();
        $this->audit = new AuditService();
    }

    public function index(): void
    {
        $search = trim($_GET['q'] ?? '');
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = (int) config('pagination', 15);
        $total = $this->users->countByRoleFiltered('academic', $search);
        $pagination = paginate($total, $page, $perPage);
        $staff = $this->users->allByRole('academic', $search, $perPage, $pagination['offset']);

        view('admin.staff.index', [
            'title'      => 'Учебный процесс',
            'staff'      => $staff,
            'search'     => $search,
            'pagination' => $pagination,
        ]);
    }

    public function create(): void
    {
        view('admin.staff.form', ['title' => 'Новый сотрудник', 'member' => null]);
    }

    public function store(): void
    {
        $this->validateCsrf();
        $data = $this->validated();

        if ($data['fullname'] === '' || $data['login'] === '' || $data['password'] === '') {
            flash('error', 'Заполните ФИО, логин и пароль.');
            redirect('/admin/staff/create');
        }

        if ($this->users->loginExists($data['login'])) {
            flash('error', 'Логин уже занят.');
            redirect('/admin/staff/create');
        }

        $userId = $this->users->create([
            'fullname' => $data['fullname'],
            'login'    => $data['login'],
            'password' => password_hash($data['password'], PASSWORD_BCRYPT),
            'role'     => 'academic',
        ]);
        $this->audit->record('staff_create', 'user', $userId);

        flash('success', 'Сотрудник учебного процесса добавлен.');
        redirect('/admin/staff');
    }

    public function edit(string $id): void
    {
        $member = $this->users->findByRole((int) $id, 'academic');
        if (!$member) {
            flash('error', 'Сотрудник не найден.');
            redirect('/admin/staff');
        }
        view('admin.staff.form', [
            'title'  => 'Редактирование',
            'member' => $member,
        ]);
    }

    public function update(string $id): void
    {
        $this->validateCsrf();
        $member = $this->users->findByRole((int) $id, 'academic');
        if (!$member) {
            flash('error', 'Сотрудник не найден.');
            redirect('/admin/staff');
        }

        $data = $this->validated();
        if ($this->users->loginExists($data['login'], (int) $member['id'])) {
            flash('error', 'Логин уже занят.');
            redirect('/admin/staff/' . $id . '/edit');
        }

        $userData = [
            'fullname'  => $data['fullname'],
            'login'     => $data['login'],
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
        ];
        if ($data['password'] !== '') {
            $userData['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        }
        $this->users->update((int) $member['id'], $userData);
        $this->audit->record('staff_update', 'user', (int) $member['id']);

        flash('success', 'Данные обновлены.');
        redirect('/admin/staff');
    }

    public function destroy(string $id): void
    {
        $this->validateCsrf();
        $member = $this->users->findByRole((int) $id, 'academic');
        if ($member) {
            $this->users->deleteByRole((int) $member['id'], 'academic');
            $this->audit->record('staff_delete', 'user', (int) $member['id']);
        }
        flash('success', 'Сотрудник удалён.');
        redirect('/admin/staff');
    }

    private function validated(): array
    {
        return [
            'fullname' => trim($_POST['fullname'] ?? ''),
            'login'    => trim($_POST['login'] ?? ''),
            'password' => $_POST['password'] ?? '',
        ];
    }
}
