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
$userId = isset($_POST['userId']) ? intval($_POST['userId']) : 0;
$filenameKey = isset($_POST['filename']) ? trim($_POST['filename']) : '';
if ($userId <= 0 || $filenameKey === '' || !isset($_FILES['file'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing userId, filename, or file']);
    exit;
}

// === HANDLE FILE UPLOAD ===
if (!is_dir($UPLOAD_DIR)) {
    mkdir($UPLOAD_DIR, 0777, true);
}

$file = $_FILES['file'];
$storedFilename = $userId . '_' . $filenameKey . '_' . uniqid() . '_' . basename($file['name']);
$filepath = $UPLOAD_DIR . $storedFilename;

if (!move_uploaded_file($file['tmp_name'], $filepath)) {
    http_response_code(500);
    echo json_encode(['error' => 'File upload failed']);
    exit;
}

// === SAVE TO DATABASE ===
try {
    $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME", $DB_USER, $DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'DB connection failed']);
    exit;
}

// Insert OR update (overwrite) if user/filename combo already exists
$stmt = $pdo->prepare(
    "INSERT INTO user_files (user_id, file_name, file_path, original_filename) VALUES (?, ?, ?, ?)
     ON DUPLICATE KEY UPDATE file_path=VALUES(file_path), original_filename=VALUES(original_filename), upload_date=NOW()"
);
if (!$stmt->execute([$userId, $filenameKey, $storedFilename, $file['name']])) {
    http_response_code(500);
    echo json_encode(['error' => 'DB insert failed']);
    exit;
}

// === SUCCESS RESPONSE ===
http_response_code(200);
echo json_encode([
    'success' => true,
    'file_url' => '/uploads/' . $storedFilename
]);
?>