<div class="card auth-card shadow">
    <div class="card-body p-5">
        <div class="text-center mb-4">
            <i class="bi bi-calendar3-week display-4 text-primary"></i>
            <h2 class="mt-2"><?= e(config('name')) ?></h2>
            <p class="text-muted">Вход в систему</p>
        </div>
        <form method="post" action="<?= base_url('login') ?>">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label class="form-label">Логин</label>
                <input type="text" name="login" class="form-control form-control-lg" value="<?= old('login') ?>" required autofocus>
            </div>
            <div class="mb-4">
                <label class="form-label">Пароль</label>
                <input type="password" name="password" class="form-control form-control-lg" required>
            </div>
            <button type="submit" class="btn btn-primary btn-lg w-100">Войти</button>
        </form>
        <p class="text-muted small mt-4 mb-0 text-center">
            Демо: admin / password · ivanov / password
        </p>
    </div>
</div>
