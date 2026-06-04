-- Система графиков преподавателей (нагрузка практики + Word)

CREATE DATABASE IF NOT EXISTS ecollege_schedules
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE ecollege_schedules;

CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    fullname VARCHAR(255) NOT NULL,
    login VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'academic', 'teacher') NOT NULL DEFAULT 'teacher',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_users_role (role),
    INDEX idx_users_login (login)
) ENGINE=InnoDB;

CREATE TABLE teachers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL UNIQUE,
    department VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_teachers_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Шаблон графика Word (grafik.docx) — загружает администратор
CREATE TABLE schedule_templates (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL DEFAULT 'Шаблон графика',
    file_path VARCHAR(500) DEFAULT NULL,
    original_filename VARCHAR(255) DEFAULT NULL,
    form_schema JSON NULL COMMENT 'Таблица шаблона для веб-формы',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    uploaded_by INT UNSIGNED DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_template_user FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Нагрузка практики, назначенная преподавателю
CREATE TABLE workloads (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    teacher_id INT UNSIGNED NOT NULL,
    module_name VARCHAR(255) NOT NULL COMMENT 'Название модуля',
    study_group VARCHAR(100) DEFAULT NULL COMMENT 'Учебная группа',
    practice_hours INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Нагрузка практики, часов',
    deadline DATE NOT NULL COMMENT 'Срок сдачи',
    status ENUM('assigned', 'in_progress', 'submitted', 'overdue') NOT NULL DEFAULT 'assigned',
    submitted_file_path VARCHAR(500) DEFAULT NULL,
    submitted_filename VARCHAR(255) DEFAULT NULL,
    submitted_at TIMESTAMP NULL DEFAULT NULL,
    comment TEXT DEFAULT NULL,
    document_path VARCHAR(500) DEFAULT NULL COMMENT 'Копия docx графика',
    form_data JSON NULL COMMENT 'График занятий (entries: модуль, раздел, тема, дата, время, место, ДОТ)',
    progress_percent TINYINT UNSIGNED NOT NULL DEFAULT 0,
    assigned_by INT UNSIGNED DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_workload_teacher FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE CASCADE,
    CONSTRAINT fk_workload_assigned FOREIGN KEY (assigned_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_workload_teacher (teacher_id),
    INDEX idx_workload_status (status),
    INDEX idx_workload_deadline (deadline)
) ENGINE=InnoDB;

-- История загрузок Word-файлов
CREATE TABLE workload_versions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    workload_id INT UNSIGNED NOT NULL,
    form_data JSON NULL,
    file_path VARCHAR(500) DEFAULT NULL,
    original_filename VARCHAR(255) DEFAULT NULL,
    version INT UNSIGNED NOT NULL DEFAULT 1,
    action ENUM('upload', 'submit', 'resubmit') NOT NULL DEFAULT 'upload',
    created_by INT UNSIGNED DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_wv_workload FOREIGN KEY (workload_id) REFERENCES workloads(id) ON DELETE CASCADE,
    CONSTRAINT fk_wv_user FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE audit_logs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED DEFAULT NULL,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(50) DEFAULT NULL,
    entity_id INT UNSIGNED DEFAULT NULL,
    details TEXT DEFAULT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_audit_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_audit_created (created_at)
) ENGINE=InnoDB;

INSERT INTO users (fullname, login, password, role) VALUES
('Администратор системы', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('Отдел учебного процесса', 'uchebny', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'academic'),
('Иванов Иван Иванович', 'ivanov', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher'),
('Петрова Мария Сергеевна', 'petrova', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher');

INSERT INTO teachers (user_id, department) VALUES
(2, 'Кафедра информатики'),
(3, 'Кафедра математики');

INSERT INTO workloads (teacher_id, module_name, study_group, practice_hours, deadline, status, assigned_by) VALUES
(1, 'Педагогическая практика', 'ИС-21', 120, DATE_ADD(CURDATE(), INTERVAL 14 DAY), 'assigned', 1),
(2, 'Производственная практика', 'ПИ-22', 90, DATE_ADD(CURDATE(), INTERVAL 21 DAY), 'assigned', 1);

-- Пароль демо: password
