<?php
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../config.php';

// === INPUT VALIDATION ===
$userId = isset($_GET['userId']) ? intval($_GET['userId']) : 0;
$filenameKey = isset($_GET['filename']) ? trim($_GET['filename']) : '';
if ($userId <= 0 || $filenameKey === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Missing userId or filename']);
    exit;
}

// === RETRIEVE FILE INFO ===
$pdo = getPDO();
$stmt = $pdo->prepare("SELECT file_path, original_filename FROM user_files WHERE userId = ? AND file_name = ?");
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
