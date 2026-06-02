USE ecollege_schedules;

ALTER TABLE schedule_templates
    ADD COLUMN form_schema JSON NULL COMMENT 'Структура таблицы из Word' AFTER original_filename;

ALTER TABLE workloads
    ADD COLUMN form_data JSON NULL COMMENT 'Заполненные ячейки' AFTER comment,
    ADD COLUMN progress_percent TINYINT UNSIGNED NOT NULL DEFAULT 0 AFTER form_data;

ALTER TABLE workload_versions
    ADD COLUMN form_data JSON NULL AFTER workload_id;

ALTER TABLE workload_versions
    MODIFY file_path VARCHAR(500) NULL;

ALTER TABLE schedule_templates
    MODIFY file_path VARCHAR(500) NULL;
