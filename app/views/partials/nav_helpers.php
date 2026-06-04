<?php
$navPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '';
$navBase = rtrim(parse_url(base_url(), PHP_URL_PATH) ?: '', '/');
$navRel = $navBase !== '' && str_starts_with($navPath, $navBase)
    ? substr($navPath, strlen($navBase))
    : $navPath;
$navRel = '/' . trim($navRel, '/');

$navIsActive = static function (string $prefix) use ($navRel): bool {
    if ($prefix === '/admin' && ($navRel === '/admin' || $navRel === '/admin/')) {
        return true;
    }
    return $prefix !== '/admin' && str_starts_with($navRel, $prefix);
};

$navIsAdminHome = static function () use ($navRel): bool {
    if ($navRel !== '/admin' && $navRel !== '/admin/') {
        return false;
    }
    return true;
};

$navIsAdminHomeExclusive = static function () use ($navRel): bool {
    if (!str_starts_with($navRel, '/admin')) {
        return false;
    }
    return !preg_match('#^/admin/(teachers|staff|workloads|submissions|practice-report|audit)#', $navRel);
};

$navIsMoreSection = static function () use ($navRel): bool {
    return (bool) preg_match('#^/admin/(practice-report|audit)#', $navRel);
};
