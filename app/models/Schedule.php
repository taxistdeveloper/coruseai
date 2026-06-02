<?php

declare(strict_types=1);

namespace App\Models;

class Schedule extends BaseModel
{
    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT s.*, u.fullname AS teacher_name, t.user_id AS teacher_user_id
             FROM schedules s
             LEFT JOIN teachers t ON t.id = s.teacher_id
             LEFT JOIN users u ON u.id = t.user_id
             WHERE s.id = ?'
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO schedules (title, file_path, original_filename, deadline, status, teacher_id, assigned_by, cell_data, progress_percent)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $data['title'],
            $data['file_path'],
            $data['original_filename'] ?? null,
            $data['deadline'] ?? null,
            $data['status'] ?? 'not_assigned',
            $data['teacher_id'] ?? null,
            $data['assigned_by'] ?? null,
            isset($data['cell_data']) ? json_encode($data['cell_data'], JSON_UNESCAPED_UNICODE) : null,
            $data['progress_percent'] ?? 0,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $map = ['title', 'file_path', 'deadline', 'status', 'teacher_id', 'progress_percent', 'original_filename'];
        $fields = [];
        $params = [];
        foreach ($map as $f) {
            if (array_key_exists($f, $data)) {
                $fields[] = "{$f} = ?";
                $params[] = $data[$f];
            }
        }
        if (array_key_exists('cell_data', $data)) {
            $fields[] = 'cell_data = ?';
            $params[] = json_encode($data['cell_data'], JSON_UNESCAPED_UNICODE);
        }
        if ($fields === []) {
            return false;
        }
        $params[] = $id;
        return $this->db->prepare('UPDATE schedules SET ' . implode(', ', $fields) . ' WHERE id = ?')->execute($params);
    }

    public function delete(int $id): bool
    {
        return $this->db->prepare('DELETE FROM schedules WHERE id = ?')->execute([$id]);
    }

    public function adminDashboardRows(string $search = '', ?int $teacherId = null, string $statusFilter = ''): array
    {
        $sql = "SELECT s.id, s.title, s.status, s.deadline, s.updated_at,
                       u.fullname AS teacher_name, s.teacher_id,
                       sub.submit_date, sub.status AS submission_status
                FROM schedules s
                LEFT JOIN teachers t ON t.id = s.teacher_id
                LEFT JOIN users u ON u.id = t.user_id
                LEFT JOIN (
                    SELECT schedule_id, MAX(submit_date) AS submit_date, status
                    FROM schedule_submissions
                    WHERE status = 'submitted'
                    GROUP BY schedule_id
                ) sub ON sub.schedule_id = s.id
                WHERE 1=1";
        $params = [];
        if ($search !== '') {
            $sql .= ' AND (s.title LIKE ? OR u.fullname LIKE ?)';
            $like = '%' . $search . '%';
            $params[] = $like;
            $params[] = $like;
        }
        if ($teacherId) {
            $sql .= ' AND s.teacher_id = ?';
            $params[] = $teacherId;
        }
        if ($statusFilter !== '') {
            $sql .= ' AND s.status = ?';
            $params[] = $statusFilter;
        }
        $sql .= ' ORDER BY u.fullname ASC, s.created_at DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function stats(): array
    {
        $totalTeachers = (int) $this->db->query(
            "SELECT COUNT(*) FROM teachers t INNER JOIN users u ON u.id = t.user_id WHERE u.is_active = 1"
        )->fetchColumn();

        $submitted = (int) $this->db->query(
            "SELECT COUNT(DISTINCT teacher_id) FROM schedules WHERE status = 'submitted' AND teacher_id IS NOT NULL"
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

    public function markOverdue(): void
    {
        $this->db->exec(
            "UPDATE schedules
             SET status = 'overdue'
             WHERE deadline < CURDATE()
               AND status IN ('assigned', 'in_progress')
               AND teacher_id IS NOT NULL"
        );
    }

    public function byTeacher(int $teacherId): array
    {
        $stmt = $this->db->prepare(
            'SELECT s.* FROM schedules s WHERE s.teacher_id = ? ORDER BY s.created_at DESC'
        );
        $stmt->execute([$teacherId]);
        return $stmt->fetchAll();
    }

    public function forExport(string $search = '', ?int $teacherId = null): array
    {
        return $this->adminDashboardRows($search, $teacherId);
    }
}
