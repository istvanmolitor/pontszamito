<?php

declare(strict_types=1);

use App\Router;
use App\ApiResource;
use App\Controllers\ExamLevelController;
use App\Controllers\SubjectController;
use App\Controllers\LanguageExamController;
use App\Controllers\ScoreCalculatorController;
use App\Controllers\UniversityProgramController;

require __DIR__ . '/../vendor/autoload.php';

if (ApiResource::handlePreflight()) {
    exit;
}

$router = new Router();

$router->get('/api/subjects', [SubjectController::class, 'index']);
$router->get('/api/exam-levels', [ExamLevelController::class, 'index']);
$router->get('/api/language-exams', [LanguageExamController::class, 'index']);
$router->get('/api/university-programs', [UniversityProgramController::class, 'index']);
$router->post('/api/calculate', [ScoreCalculatorController::class, 'calculate']);


$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if (($response = $router->dispatch($method, $path ?: '/')) === null) {
    (new ApiResource(['message' => 'Endpoint not found']))->send(404);
    exit;
}

$response->send();
