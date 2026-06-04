<?php

declare(strict_types=1);

namespace App\Models;

class User extends BaseModel
{
    public function findByLogin(string $login): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE login = ? AND is_active = 1 LIMIT 1');
        $stmt->execute([$login]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function allTeachers(string $search = '', int $limit = 50, int $offset = 0): array
    {
        $sql = "SELECT u.*, t.id AS teacher_id, t.department
                FROM users u
                INNER JOIN teachers t ON t.user_id = u.id
                WHERE u.role = 'teacher'";
        $params = [];
        if ($search !== '') {
            $sql .= ' AND (u.fullname LIKE ? OR u.login LIKE ? OR t.department LIKE ?)';
            $like = '%' . $search . '%';
            $params = [$like, $like, $like];
        }
        $sql .= ' ORDER BY u.fullname ASC LIMIT ? OFFSET ?';
        $params[] = $limit;
        $params[] = $offset;
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function countTeachers(string $search = ''): int
    {
        $sql = "SELECT COUNT(*) FROM users u
                INNER JOIN teachers t ON t.user_id = u.id
                WHERE u.role = 'teacher'";
        $params = [];
        if ($search !== '') {
            $sql .= ' AND (u.fullname LIKE ? OR u.login LIKE ? OR t.department LIKE ?)';
            $like = '%' . $search . '%';
            $params = [$like, $like, $like];
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO users (fullname, login, password, role) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([
            $data['fullname'],
            $data['login'],
            $data['password'],
            $data['role'] ?? 'teacher',
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $fields = [];
        $params = [];
        foreach (['fullname', 'login', 'role', 'is_active'] as $f) {
            if (array_key_exists($f, $data)) {
                $fields[] = "{$f} = ?";
                $params[] = $data[$f];
            }
        }
        if (isset($data['password'])) {
            $fields[] = 'password = ?';
            $params[] = $data['password'];
        }
        if ($fields === []) {
            return false;
        }
        $params[] = $id;
        $sql = 'UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = ?';
        return $this->db->prepare($sql)->execute($params);
    }

    public function delete(int $id): bool
    {
        return $this->deleteByRole($id, 'teacher');
    }

    public function deleteByRole(int $id, string $role): bool
    {
        return $this->db->prepare('DELETE FROM users WHERE id = ? AND role = ?')->execute([$id, $role]);
    }

    public function findByRole(int $id, string $role): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE id = ? AND role = ? LIMIT 1');
        $stmt->execute([$id, $role]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function allByRole(string $role, string $search = '', int $limit = 50, int $offset = 0): array
    {
        $sql = 'SELECT * FROM users WHERE role = ?';
        $params = [$role];
        if ($search !== '') {
            $sql .= ' AND (fullname LIKE ? OR login LIKE ?)';
            $like = '%' . $search . '%';
            $params[] = $like;
            $params[] = $like;
        }
        $sql .= ' ORDER BY fullname ASC LIMIT ? OFFSET ?';
        $params[] = $limit;
        $params[] = $offset;
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function countByRoleFiltered(string $role, string $search = ''): int
    {
        $sql = 'SELECT COUNT(*) FROM users WHERE role = ?';
        $params = [$role];
        if ($search !== '') {
            $sql .= ' AND (fullname LIKE ? OR login LIKE ?)';
            $like = '%' . $search . '%';
            $params[] = $like;
            $params[] = $like;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public function loginExists(string $login, ?int $exceptId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM users WHERE login = ?';
        $params = [$login];
        if ($exceptId) {
            $sql .= ' AND id != ?';
            $params[] = $exceptId;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function countByRole(string $role): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM users WHERE role = ? AND is_active = 1');
        $stmt->execute([$role]);
        return (int) $stmt->fetchColumn();
    }
}
