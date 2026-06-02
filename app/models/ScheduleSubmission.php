<?php

declare(strict_types=1);

namespace App\Models;

class ScheduleSubmission extends BaseModel
{
    public function findLatest(int $scheduleId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM schedule_submissions WHERE schedule_id = ? ORDER BY version DESC LIMIT 1'
        );
        $stmt->execute([$scheduleId]);
        return $stmt->fetch() ?: null;
    }

    public function findDraft(int $scheduleId, int $teacherId): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM schedule_submissions
             WHERE schedule_id = ? AND teacher_id = ? AND status = 'draft'
             ORDER BY version DESC LIMIT 1"
        );
        $stmt->execute([$scheduleId, $teacherId]);
        return $stmt->fetch() ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO schedule_submissions (schedule_id, teacher_id, submit_date, comment, status, file_path, cell_data, version)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $data['schedule_id'],
            $data['teacher_id'],
            $data['submit_date'] ?? null,
            $data['comment'] ?? null,
            $data['status'] ?? 'draft',
            $data['file_path'] ?? null,
            isset($data['cell_data']) ? json_encode($data['cell_data'], JSON_UNESCAPED_UNICODE) : null,
            $data['version'] ?? 1,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $fields = [];
        $params = [];
        foreach (['comment', 'status', 'file_path', 'submit_date'] as $f) {
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
        return $this->db->prepare('UPDATE schedule_submissions SET ' . implode(', ', $fields) . ' WHERE id = ?')->execute($params);
    }

    public function nextVersion(int $scheduleId): int
    {
        $stmt = $this->db->prepare('SELECT COALESCE(MAX(version), 0) + 1 FROM schedule_submissions WHERE schedule_id = ?');
        $stmt->execute([$scheduleId]);
        return (int) $stmt->fetchColumn();
    }

    public function allSubmitted(string $search = '', ?int $teacherId = null, int $limit = 50, int $offset = 0): array
    {
        $sql = "SELECT ss.*, s.title, u.fullname AS teacher_name
                FROM schedule_submissions ss
                INNER JOIN schedules s ON s.id = ss.schedule_id
                INNER JOIN teachers t ON t.id = ss.teacher_id
                INNER JOIN users u ON u.id = t.user_id
                WHERE ss.status = 'submitted'";
        $params = [];
        if ($search !== '') {
            $sql .= ' AND (s.title LIKE ? OR u.fullname LIKE ?)';
            $like = '%' . $search . '%';
            $params[] = $like;
            $params[] = $like;
        }
        if ($teacherId) {
            $sql .= ' AND ss.teacher_id = ?';
            $params[] = $teacherId;
        }
        $sql .= ' ORDER BY ss.submit_date DESC LIMIT ? OFFSET ?';
        $params[] = $limit;
        $params[] = $offset;
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function countSubmitted(string $search = '', ?int $teacherId = null): int
    {
        $sql = "SELECT COUNT(*) FROM schedule_submissions ss
                INNER JOIN schedules s ON s.id = ss.schedule_id
                INNER JOIN teachers t ON t.id = ss.teacher_id
                INNER JOIN users u ON u.id = t.user_id
                WHERE ss.status = 'submitted'";
        $params = [];
        if ($search !== '') {
            $sql .= ' AND (s.title LIKE ? OR u.fullname LIKE ?)';
            $like = '%' . $search . '%';
            $params[] = $like;
            $params[] = $like;
        }
        if ($teacherId) {
            $sql .= ' AND ss.teacher_id = ?';
            $params[] = $teacherId;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }
}
