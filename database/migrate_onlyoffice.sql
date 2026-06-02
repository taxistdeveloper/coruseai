USE ecollege_schedules;

ALTER TABLE workloads
    ADD COLUMN document_path VARCHAR(500) NULL COMMENT 'Копия docx для редактирования' AFTER comment;
