<?php

declare(strict_types=1);

// Class Aid - Main Application Entry Point

$baseDir = dirname(__DIR__);

// 1. Require Composer Autoloader
require_once $baseDir . '/vendor/autoload.php';

use App\Core\Config;
use App\Core\Session;
use App\Core\Request;
use App\Core\Router;
use App\Controllers\InstallController;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\StudentController;
use App\Controllers\CallLogController;
use App\Controllers\AttendanceController;
use App\Controllers\ClassController;
use App\Controllers\ResultController;

// 2. Initialize Session and Configuration
Session::start();
Config::load($baseDir);

// 3. Error reporting based on APP_DEBUG
if (Config::get('APP_DEBUG', false)) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(0);
}

// 4. Initialize Router
$router = new Router();

// --- Setup / Installer Routes ---
$router->get('/install', [InstallController::class, 'showInstall']);
$router->post('/install/process', [InstallController::class, 'processInstall']);

// --- Auth Routes ---
$router->get('/login', [AuthController::class, 'showLogin'], ['guest']);
$router->post('/login', [AuthController::class, 'login'], ['guest']);
$router->get('/logout', [AuthController::class, 'logout']);

// --- Dashboard Routes ---
$router->get('/', [DashboardController::class, 'index'], ['auth']);
$router->get('/dashboard', [DashboardController::class, 'index'], ['auth']);

// --- Student Directory Routes ---
$router->get('/students', [StudentController::class, 'index'], ['auth']);
$router->get('/students/create', [StudentController::class, 'create'], ['auth']);
$router->post('/students', [StudentController::class, 'store'], ['auth']);
$router->get('/students/search/api', [StudentController::class, 'searchApi'], ['auth']);
$router->get('/students/{id}', [StudentController::class, 'show'], ['auth']);
$router->get('/students/{id}/edit', [StudentController::class, 'edit'], ['auth']);
$router->post('/students/{id}', [StudentController::class, 'update'], ['auth']);

// --- Parent Communication Journal Routes ---
$router->get('/calls', [CallLogController::class, 'index'], ['auth']);
$router->get('/calls/create', [CallLogController::class, 'create'], ['auth']);
$router->post('/calls', [CallLogController::class, 'store'], ['auth']);
$router->post('/calls/toggle-status/{id}', [CallLogController::class, 'toggleStatus'], ['auth']);
$router->get('/calls/export/csv', [CallLogController::class, 'exportCsv'], ['auth']);
$router->get('/calls/print/student/{id}', [CallLogController::class, 'printStudent'], ['auth']);
$router->get('/calls/print/class/{id}', [CallLogController::class, 'printClass'], ['auth']);

// --- Attendance Register Routes ---
$router->get('/attendance', [AttendanceController::class, 'index'], ['auth']);
$router->get('/attendance/sheet/{id}', [AttendanceController::class, 'sheet'], ['auth']);
$router->post('/attendance/sheet/{id}', [AttendanceController::class, 'save'], ['auth']);

// --- Classes & Subjects Management Routes ---
$router->get('/classes', [ClassController::class, 'index'], ['auth']);
$router->post('/classes', [ClassController::class, 'store'], ['auth']);
$router->post('/classes/subjects', [ClassController::class, 'storeSubject'], ['auth']);

// --- Examinations & Results Routes ---
$router->get('/results', [ResultController::class, 'index'], ['auth']);
$router->post('/results/exams', [ResultController::class, 'createExam'], ['auth']);
$router->get('/results/enter/{id}', [ResultController::class, 'enterMarks'], ['auth']);
$router->post('/results/enter/{id}', [ResultController::class, 'saveMarks'], ['auth']);
$router->get('/results/report/{id}', [ResultController::class, 'studentReport'], ['auth']);

// 5. Dispatch Request
$request = new Request();
$router->dispatch($request);
