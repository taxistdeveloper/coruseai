<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AuditLog;

class AuditService
{
    private AuditLog $model;

    public function __construct()
    {
        $this->model = new AuditLog();
    }

    public function record(string $action, ?string $entityType = null, ?int $entityId = null, ?string $details = null): void
    {
        $this->model->log(auth_id(), $action, $entityType, $entityId, $details);
    }
}
