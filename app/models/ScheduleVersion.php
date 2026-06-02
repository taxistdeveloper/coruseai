<?php

declare(strict_types=1);

namespace App\Models;

class ScheduleVersion extends BaseModel
{
    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO schedule_versions (schedule_id, teacher_id, version, cell_data, file_path, action, created_by)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $data['schedule_id'],
            $data['teacher_id'] ?? null,
            $data['version'],
            isset($data['cell_data']) ? json_encode($data['cell_data'], JSON_UNESCAPED_UNICODE) : null,
            $data['file_path'] ?? null,
            $data['action'],
            $data['created_by'] ?? null,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function bySchedule(int $scheduleId): array
    {
        $stmt = $this->db->prepare(
            'SELECT sv.*, u.fullname AS author_name
             FROM schedule_versions sv
             LEFT JOIN users u ON u.id = sv.created_by
             WHERE sv.schedule_id = ?
             ORDER BY sv.version DESC'
        );
        $stmt->execute([$scheduleId]);
        return $stmt->fetchAll();
    }

    public function nextVersion(int $scheduleId): int
    {
        $stmt = $this->db->prepare('SELECT COALESCE(MAX(version), 0) + 1 FROM schedule_versions WHERE schedule_id = ?');
        $stmt->execute([$scheduleId]);
        return (int) $stmt->fetchColumn();
    }
}
