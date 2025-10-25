<?php
require_once __DIR__ . '/config.php';

// === API KEY CHECK ===
$clientApiKey = $_SERVER['HTTP_X_API_KEY'] ?? '';
if ($clientApiKey !== $API_KEY) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// === INPUT VALIDATION ===
$userId = isset($_GET['userId']) ? intval($_GET['userId']) : 0;
$filenameKey = isset($_GET['filename']) ? trim($_GET['filename']) : '';
if ($userId <= 0 || $filenameKey === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Missing userId or filename']);
    exit;
}

// === CONNECT TO DATABASE ===
try {
    $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME", $DB_USER, $DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'DB connection failed']);
    exit;
}

// === RETRIEVE FILE INFO ===
$stmt = $pdo->prepare("SELECT file_path, original_filename FROM user_files WHERE user_id = ? AND file_name = ?");
if (!$stmt->execute([$userId, $filenameKey])) {
    http_response_code(500);
    echo json_encode(['error' => 'DB query failed']);
    exit;
}

$result = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$result) {
    http_response_code(404);
    echo json_encode(['error' => 'File not found']);
    exit;
}

// === SERVE FILE ===
$filepath = $UPLOAD_DIR . $result['file_path'];
if (!file_exists($filepath)) {
    http_response_code(404);
    echo json_encode(['error' => 'File not found on server']);
    exit;
}

// Determine content type
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $filepath);
finfo_close($finfo);

// Send file
header('Content-Type: ' . $mimeType);
header('Content-Disposition: attachment; filename="' . basename($result['original_filename']) . '"');
header('Content-Length: ' . filesize($filepath));
readfile($filepath);
?>
