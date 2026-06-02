-- Миграция со старой схемы (Excel) на новую (Word + нагрузки)
-- Выполните только если уже была установлена прежняя версия

USE ecollege_schedules;

DROP TABLE IF EXISTS schedule_versions;
DROP TABLE IF EXISTS schedule_submissions;
DROP TABLE IF EXISTS schedules;

CREATE TABLE IF NOT EXISTS schedule_templates (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL DEFAULT 'Шаблон графика',
    file_path VARCHAR(500) NOT NULL,
    original_filename VARCHAR(255) DEFAULT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    uploaded_by INT UNSIGNED DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_template_user FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS workloads (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    teacher_id INT UNSIGNED NOT NULL,
    module_name VARCHAR(255) NOT NULL,
    practice_hours INT UNSIGNED NOT NULL DEFAULT 0,
    deadline DATE NOT NULL,
    status ENUM('assigned', 'in_progress', 'submitted', 'overdue') NOT NULL DEFAULT 'assigned',
    submitted_file_path VARCHAR(500) DEFAULT NULL,
    submitted_filename VARCHAR(255) DEFAULT NULL,
    submitted_at TIMESTAMP NULL DEFAULT NULL,
    comment TEXT DEFAULT NULL,
    assigned_by INT UNSIGNED DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_workload_teacher FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE CASCADE,
    CONSTRAINT fk_workload_assigned FOREIGN KEY (assigned_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS workload_versions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    workload_id INT UNSIGNED NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    original_filename VARCHAR(255) DEFAULT NULL,
    version INT UNSIGNED NOT NULL DEFAULT 1,
    action ENUM('upload', 'submit', 'resubmit') NOT NULL DEFAULT 'upload',
    created_by INT UNSIGNED DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_wv_workload FOREIGN KEY (workload_id) REFERENCES workloads(id) ON DELETE CASCADE,
    CONSTRAINT fk_wv_user FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;
