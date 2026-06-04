<div class="auth-shell auth-shell--mobile">
    <aside class="auth-brand" aria-hidden="true">
        <div class="auth-brand__glow"></div>
        <div class="auth-brand__inner">
            <div class="auth-brand__logo">
                <i class="bi bi-calendar3-week" aria-hidden="true"></i>
            </div>
            <h1 class="auth-brand__title"><?= e(config('name')) ?></h1>
            <p class="auth-brand__tagline">Управление графиками и нагрузкой преподавателей</p>
            <ul class="auth-brand__features">
                <li><i class="bi bi-calendar-check" aria-hidden="true"></i> Графики занятий</li>
                <li><i class="bi bi-people" aria-hidden="true"></i> Нагрузка преподавателей</li>
                <li><i class="bi bi-bar-chart-line" aria-hidden="true"></i> Отчёты и контроль</li>
            </ul>
        </div>
    </aside>

    <div class="auth-panel">
        <div class="card auth-card">
            <div class="card-body">
                <div class="auth-panel__header d-lg-none">
                    <div class="auth-brand__logo auth-brand__logo--sm">
                        <i class="bi bi-calendar3-week" aria-hidden="true"></i>
                    </div>
                    <h2 class="auth-panel__title"><?= e(config('name')) ?></h2>
                    <p class="auth-subtitle mb-0">Вход в систему</p>
                </div>

                <div class="auth-panel__header d-none d-lg-block">
                    <h2 class="auth-panel__title mb-1">Добро пожаловать</h2>
                    <p class="auth-subtitle mb-0">Войдите, чтобы продолжить работу</p>
                </div>

                <form method="post" action="<?= base_url('login') ?>" class="auth-form">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label" for="login">Логин</label>
                        <div class="auth-input-wrap">
                            <span class="auth-input-icon" aria-hidden="true">
                                <i class="bi bi-person"></i>
                            </span>
                            <input type="text"
                                   name="login"
                                   id="login"
                                   class="form-control form-control-lg auth-input"
                                   value="<?= old('login') ?>"
                                   placeholder="Введите логин"
                                   required
                                   autofocus
                                   autocomplete="username">
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label" for="password">Пароль</label>
                        <div class="auth-input-wrap">
                            <span class="auth-input-icon" aria-hidden="true">
                                <i class="bi bi-lock"></i>
                            </span>
                            <input type="password"
                                   name="password"
                                   id="password"
                                   class="form-control form-control-lg auth-input auth-input--password"
                                   placeholder="Введите пароль"
                                   required
                                   autocomplete="current-password">
                            <button type="button"
                                    class="auth-password-toggle"
                                    id="togglePassword"
                                    aria-label="Показать пароль"
                                    aria-pressed="false">
                                <i class="bi bi-eye" aria-hidden="true"></i>
                            </button>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-lg w-100 auth-submit">
                        <span>Войти</span>
                        <i class="bi bi-arrow-right-short" aria-hidden="true"></i>
                    </button>
                </form>

                
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    var toggle = document.getElementById('togglePassword');
    var password = document.getElementById('password');
    var login = document.getElementById('login');

    if (toggle && password) {
        toggle.addEventListener('click', function () {
            var show = password.type === 'password';
            password.type = show ? 'text' : 'password';
            toggle.setAttribute('aria-pressed', show ? 'true' : 'false');
            toggle.setAttribute('aria-label', show ? 'Скрыть пароль' : 'Показать пароль');
            toggle.querySelector('i').className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
        });
    }

    document.querySelectorAll('.auth-demo-chip').forEach(function (chip) {
        chip.addEventListener('click', function () {
            if (login) login.value = chip.dataset.login || '';
            if (password) password.value = chip.dataset.password || '';
            login && login.focus();
        });
    });
})();
</script>
