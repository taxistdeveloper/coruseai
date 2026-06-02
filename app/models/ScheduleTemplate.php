<?php

declare(strict_types=1);

namespace App\Models;

class ScheduleTemplate extends BaseModel
{
    public function active(): ?array
    {
        $row = $this->db->query(
            'SELECT * FROM schedule_templates WHERE is_active = 1 ORDER BY id DESC LIMIT 1'
        )->fetch();
        return $row ?: null;
    }

    public function create(array $data): int
    {
        $this->db->exec('UPDATE schedule_templates SET is_active = 0');
        $stmt = $this->db->prepare(
            'INSERT INTO schedule_templates (title, file_path, original_filename, is_active, uploaded_by)
             VALUES (?, ?, ?, 1, ?)'
        );
        $stmt->execute([
            $data['title'] ?? 'Шаблон графика',
            $data['file_path'],
            $data['original_filename'] ?? null,
            $data['uploaded_by'] ?? null,
        ]);
        return (int) $this->db->lastInsertId();
    }
}
