<?php

declare(strict_types=1);

namespace App\Models;

class Teacher extends BaseModel
{
    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT t.*, u.fullname, u.login, u.is_active
             FROM teachers t
             INNER JOIN users u ON u.id = t.user_id
             WHERE t.id = ?'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findByUserId(int $userId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT t.*, u.fullname, u.login
             FROM teachers t
             INNER JOIN users u ON u.id = t.user_id
             WHERE t.user_id = ?'
        );
        $stmt->execute([$userId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function create(int $userId, ?string $department = null): int
    {
        $stmt = $this->db->prepare('INSERT INTO teachers (user_id, department) VALUES (?, ?)');
        $stmt->execute([$userId, $department]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $fields = [];
        $params = [];
        foreach (['department'] as $f) {
            if (array_key_exists($f, $data)) {
                $fields[] = "{$f} = ?";
                $params[] = $data[$f];
            }
        }
        if ($fields === []) {
            return false;
        }
        $params[] = $id;
        return $this->db->prepare('UPDATE teachers SET ' . implode(', ', $fields) . ' WHERE id = ?')
            ->execute($params);
    }

    public function allForSelect(): array
    {
        return $this->db->query(
            "SELECT t.id, u.fullname, t.department
             FROM teachers t
             INNER JOIN users u ON u.id = t.user_id
             WHERE u.is_active = 1
             ORDER BY u.fullname"
        )->fetchAll();
    }
}
