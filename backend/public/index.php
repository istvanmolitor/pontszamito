<?php

declare(strict_types=1);

use App\SubjectRepository;
use App\Router;

require __DIR__ . '/../vendor/autoload.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

header('Content-Type: application/json; charset=utf-8');

$repository = new SubjectRepository();
$router = new Router();

$router->get('/api/subjects', function () use ($repository): void {
    echo json_encode(
        ['subjects' => $repository->all()],
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );
});


$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if (!$router->dispatch($method, $path ?: '/')) {
    http_response_code(404);
    echo json_encode(
        ['message' => 'Endpoint not found'],
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );
}
