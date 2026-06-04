-- Роль «учебный процесс» (academic): нагрузки и отчёты
ALTER TABLE users
    MODIFY COLUMN role ENUM('admin', 'academic', 'teacher') NOT NULL DEFAULT 'teacher';

INSERT INTO users (fullname, login, password, role)
SELECT 'Отдел учебного процесса', 'uchebny', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'academic'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM users WHERE login = 'uchebny');

-- Пароль демо: password
