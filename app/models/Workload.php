<?php

declare(strict_types=1);

namespace App\Models;

class Workload extends BaseModel
{
    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT w.*, u.fullname AS teacher_name, t.user_id AS teacher_user_id
             FROM workloads w
             INNER JOIN teachers t ON t.id = w.teacher_id
             INNER JOIN users u ON u.id = t.user_id
             WHERE w.id = ?'
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO workloads (teacher_id, module_name, practice_hours, deadline, status, assigned_by)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $data['teacher_id'],
            $data['module_name'],
            $data['practice_hours'],
            $data['deadline'],
            $data['status'] ?? 'assigned',
            $data['assigned_by'] ?? null,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $map = ['module_name', 'practice_hours', 'deadline', 'status', 'submitted_file_path',
            'submitted_filename', 'submitted_at', 'comment', 'progress_percent', 'document_path'];
        $fields = [];
        $params = [];
        foreach ($map as $f) {
            if (array_key_exists($f, $data)) {
                $fields[] = "{$f} = ?";
                $params[] = $data[$f];
            }
        }
        if (array_key_exists('form_data', $data)) {
            $fields[] = 'form_data = ?';
            $params[] = json_encode($data['form_data'], JSON_UNESCAPED_UNICODE);
        }
        if ($fields === []) {
            return false;
        }
        $params[] = $id;
        return $this->db->prepare('UPDATE workloads SET ' . implode(', ', $fields) . ' WHERE id = ?')->execute($params);
    }

    public function delete(int $id): bool
    {
        return $this->db->prepare('DELETE FROM workloads WHERE id = ?')->execute([$id]);
    }

    public function byTeacher(int $teacherId): array
    {
        $stmt = $this->db->prepare(
            'SELECT w.* FROM workloads w WHERE w.teacher_id = ? ORDER BY w.deadline ASC'
        );
        $stmt->execute([$teacherId]);
        return $stmt->fetchAll();
    }

    public function markOverdue(): void
    {
        $this->db->exec(
            "UPDATE workloads SET status = 'overdue'
             WHERE deadline < CURDATE() AND status IN ('assigned', 'in_progress')"
        );
    }

    public function stats(): array
    {
        $totalTeachers = (int) $this->db->query(
            "SELECT COUNT(*) FROM teachers t INNER JOIN users u ON u.id = t.user_id WHERE u.is_active = 1"
        )->fetchColumn();

        $submitted = (int) $this->db->query(
            "SELECT COUNT(DISTINCT teacher_id) FROM workloads WHERE status = 'submitted'"
        )->fetchColumn();

        $notSubmitted = max(0, $totalTeachers - $submitted);
        $percent = $totalTeachers > 0 ? round(($submitted / $totalTeachers) * 100, 1) : 0;

        return [
            'total_teachers' => $totalTeachers,
            'submitted'      => $submitted,
            'not_submitted'  => $notSubmitted,
            'percent'        => $percent,
        ];
    }

    public function dashboardRows(string $search = '', ?int $teacherId = null, string $statusFilter = ''): array
    {
        $sql = "SELECT w.id AS workload_id, w.module_name, w.practice_hours, w.deadline, w.status,
                       w.submitted_at, w.progress_percent, u.fullname AS teacher_name, t.id AS teacher_id
                FROM workloads w
                INNER JOIN teachers t ON t.id = w.teacher_id
                INNER JOIN users u ON u.id = t.user_id
                WHERE u.is_active = 1";
        $params = [];

        if ($search !== '') {
            $sql .= ' AND (u.fullname LIKE ? OR w.module_name LIKE ?)';
            $like = '%' . $search . '%';
            $params[] = $like;
            $params[] = $like;
        }
        if ($teacherId) {
            $sql .= ' AND t.id = ?';
            $params[] = $teacherId;
        }
        if ($statusFilter !== '') {
            $sql .= ' AND w.status = ?';
            $params[] = $statusFilter;
        }

        $sql .= ' ORDER BY w.deadline ASC, u.fullname ASC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function teachersWithoutSubmission(): array
    {
        return $this->db->query(
            "SELECT u.fullname, t.id AS teacher_id
             FROM teachers t
             INNER JOIN users u ON u.id = t.user_id
             WHERE u.is_active = 1
               AND t.id NOT IN (
                   SELECT DISTINCT teacher_id FROM workloads WHERE status = 'submitted'
               )
             ORDER BY u.fullname"
        )->fetchAll();
    }

    public function submittedList(string $search = '', ?int $teacherId = null, int $limit = 50, int $offset = 0): array
    {
        $sql = "SELECT w.*, u.fullname AS teacher_name
                FROM workloads w
                INNER JOIN teachers t ON t.id = w.teacher_id
                INNER JOIN users u ON u.id = t.user_id
                WHERE w.status = 'submitted'";
        $params = [];
        if ($search !== '') {
            $sql .= ' AND (u.fullname LIKE ? OR w.module_name LIKE ?)';
            $like = '%' . $search . '%';
            $params[] = $like;
            $params[] = $like;
        }
        if ($teacherId) {
            $sql .= ' AND w.teacher_id = ?';
            $params[] = $teacherId;
        }
        $sql .= ' ORDER BY w.submitted_at DESC LIMIT ? OFFSET ?';
        $params[] = $limit;
        $params[] = $offset;
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function countSubmitted(string $search = '', ?int $teacherId = null): int
    {
        $sql = "SELECT COUNT(*) FROM workloads w
                INNER JOIN teachers t ON t.id = w.teacher_id
                INNER JOIN users u ON u.id = t.user_id
                WHERE w.status = 'submitted'";
        $params = [];
        if ($search !== '') {
            $sql .= ' AND (u.fullname LIKE ? OR w.module_name LIKE ?)';
            $like = '%' . $search . '%';
            $params[] = $like;
            $params[] = $like;
        }
        if ($teacherId) {
            $sql .= ' AND w.teacher_id = ?';
            $params[] = $teacherId;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }
}
