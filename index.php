<?php
// index.php - Main router

require_once __DIR__ . '/config.php';

// --- Define all routes ---
$routes = [
    '/api/v1/files/upload' => [
        'file' => __DIR__ . '/v1/files/upload.php',
        'auth' => true
    ],
    '/api/v1/files/get' => [
        'file' => __DIR__ . '/v1/files/get.php',
        'auth' => true
    ],
];

// --- Get the requested path ---
$requestUri = $_SERVER['REQUEST_URI'];
$requestPath = parse_url($requestUri, PHP_URL_PATH);

// --- Check if route exists ---
if (!isset($routes[$requestPath])) {
    http_response_code(404);
    echo json_encode(['error' => 'Route not found']);
    exit;
}

$route = $routes[$requestPath];

// --- API KEY CHECK (if auth required) ---
if ($route['auth']) {
    $clientApiKey = $_SERVER['HTTP_X_API_KEY'] ?? '';
    if ($clientApiKey !== $API_KEY) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
}

// --- Include the route file ---
if (file_exists($route['file'])) {
    require $route['file'];
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Route file not found']);
    exit;
}
?>
