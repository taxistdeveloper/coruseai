<?php

declare(strict_types=1);

namespace App\Models;

class WorkloadVersion extends BaseModel
{
    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO workload_versions (workload_id, form_data, file_path, original_filename, version, action, created_by)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $data['workload_id'],
            isset($data['form_data']) ? json_encode($data['form_data'], JSON_UNESCAPED_UNICODE) : null,
            $data['file_path'] ?? null,
            $data['original_filename'] ?? null,
            $data['version'],
            $data['action'] ?? 'submit',
            $data['created_by'] ?? null,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function nextVersion(int $workloadId): int
    {
        $stmt = $this->db->prepare('SELECT COALESCE(MAX(version), 0) + 1 FROM workload_versions WHERE workload_id = ?');
        $stmt->execute([$workloadId]);
        return (int) $stmt->fetchColumn();
    }

    public function byWorkload(int $workloadId): array
    {
        $stmt = $this->db->prepare(
            'SELECT wv.*, u.fullname AS author_name
             FROM workload_versions wv
             LEFT JOIN users u ON u.id = wv.created_by
             WHERE wv.workload_id = ?
             ORDER BY wv.version DESC'
        );
        $stmt->execute([$workloadId]);
        return $stmt->fetchAll();
    }
}
