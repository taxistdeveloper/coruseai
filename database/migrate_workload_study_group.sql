-- Группа в workloads (БД как в app/config/database.php, обычно ecollege)
-- Если ADD уже выполнен — пропустите блок 1. Безопасный запуск: php database/migrate_workload_study_group.php

USE ecollege;

-- 1) Добавить колонку (пропустите, если ошибка #1060 «Дублирующееся имя столбца»)
-- ALTER TABLE workloads
--     ADD COLUMN study_group VARCHAR(100) DEFAULT NULL COMMENT 'Учебная группа' AFTER module_name;

-- 2) Перенос из teachers (только если у teachers ещё есть study_group)
-- UPDATE workloads w
-- INNER JOIN teachers t ON t.id = w.teacher_id
-- SET w.study_group = t.study_group
-- WHERE (w.study_group IS NULL OR TRIM(w.study_group) = '')
--   AND t.study_group IS NOT NULL AND TRIM(t.study_group) != '';

-- ALTER TABLE teachers DROP COLUMN study_group;
