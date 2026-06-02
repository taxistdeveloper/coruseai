<?php

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\DocumentController;
use App\Controllers\Admin\DashboardController as AdminDashboard;
use App\Controllers\Admin\TeacherController as AdminTeacher;
use App\Controllers\Admin\WorkloadController as AdminWorkload;
use App\Controllers\Admin\TemplateController as AdminTemplate;
use App\Controllers\Admin\SubmissionController as AdminSubmission;
use App\Controllers\Admin\AuditController as AdminAudit;
use App\Controllers\Teacher\DashboardController as TeacherDashboard;
use App\Controllers\Teacher\WorkloadController as TeacherWorkload;
use App\Middleware\AuthMiddleware;
use App\Middleware\RoleMiddleware;

/** @var \App\Core\Router $router */

$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/logout', [AuthController::class, 'logout'], [AuthMiddleware::class]);
$router->get('/', [AuthController::class, 'home']);

// ONLYOFFICE: доступ по токену (без сессии)
$router->get('/documents/workload/{id}', [DocumentController::class, 'downloadWorkload']);
$router->post('/documents/callback', [DocumentController::class, 'callback']);

// Admin
$router->get('/admin', [AdminDashboard::class, 'index'], [RoleMiddleware::class . '::admin']);

$router->get('/admin/teachers', [AdminTeacher::class, 'index'], [RoleMiddleware::class . '::admin']);
$router->get('/admin/teachers/create', [AdminTeacher::class, 'create'], [RoleMiddleware::class . '::admin']);
$router->post('/admin/teachers', [AdminTeacher::class, 'store'], [RoleMiddleware::class . '::admin']);
$router->get('/admin/teachers/{id}/edit', [AdminTeacher::class, 'edit'], [RoleMiddleware::class . '::admin']);
$router->post('/admin/teachers/{id}', [AdminTeacher::class, 'update'], [RoleMiddleware::class . '::admin']);
$router->post('/admin/teachers/{id}/delete', [AdminTeacher::class, 'destroy'], [RoleMiddleware::class . '::admin']);

$router->get('/admin/template', [AdminTemplate::class, 'index'], [RoleMiddleware::class . '::admin']);
$router->post('/admin/template', [AdminTemplate::class, 'upload'], [RoleMiddleware::class . '::admin']);
$router->get('/admin/workloads', [AdminWorkload::class, 'index'], [RoleMiddleware::class . '::admin']);
$router->get('/admin/workloads/create', [AdminWorkload::class, 'create'], [RoleMiddleware::class . '::admin']);
$router->post('/admin/workloads', [AdminWorkload::class, 'store'], [RoleMiddleware::class . '::admin']);
$router->get('/admin/workloads/{id}', [AdminWorkload::class, 'show'], [RoleMiddleware::class . '::admin']);
$router->get('/admin/workloads/{id}/file', [AdminWorkload::class, 'file'], [RoleMiddleware::class . '::admin']);
$router->get('/admin/workloads/{id}/doc', [AdminWorkload::class, 'downloadDoc'], [RoleMiddleware::class . '::admin']);
$router->get('/admin/workloads/{id}/edit', [AdminWorkload::class, 'edit'], [RoleMiddleware::class . '::admin']);
$router->post('/admin/workloads/{id}', [AdminWorkload::class, 'update'], [RoleMiddleware::class . '::admin']);
$router->post('/admin/workloads/{id}/delete', [AdminWorkload::class, 'destroy'], [RoleMiddleware::class . '::admin']);
$router->get('/admin/submissions', [AdminSubmission::class, 'index'], [RoleMiddleware::class . '::admin']);
$router->get('/admin/submissions/export', [AdminSubmission::class, 'export'], [RoleMiddleware::class . '::admin']);

$router->get('/admin/audit', [AdminAudit::class, 'index'], [RoleMiddleware::class . '::admin']);

// Teacher
$router->get('/teacher', [TeacherDashboard::class, 'index'], [RoleMiddleware::class . '::teacher']);
$router->get('/teacher/workloads/{id}', [TeacherWorkload::class, 'show'], [RoleMiddleware::class . '::teacher']);
$router->get('/teacher/workloads/{id}/file', [TeacherWorkload::class, 'file'], [RoleMiddleware::class . '::teacher']);
$router->get('/teacher/workloads/{id}/download', [TeacherWorkload::class, 'download'], [RoleMiddleware::class . '::teacher']);
$router->post('/teacher/workloads/{id}/upload', [TeacherWorkload::class, 'upload'], [RoleMiddleware::class . '::teacher']);
$router->post('/teacher/workloads/{id}/submit', [TeacherWorkload::class, 'submit'], [RoleMiddleware::class . '::teacher']);
