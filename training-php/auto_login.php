<?php
session_start();
header('Content-Type: application/json');

// Đọc dữ liệu JSON từ body
$data = json_decode(file_get_contents('php://input'), true);
if (empty($data['token'])) {
    echo json_encode(['success' => false]);
    exit;
}

$token = $data['token'];
$redis = new Redis();
$redis->connect('web-redis', 6379);

$userId = $redis->get('login_token_' . $token);
if ($userId) {
    $_SESSION['id'] = $userId; 
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}
