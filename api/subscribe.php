<?php
header('Content-Type: application/json');
require_once '../config/database.php';

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data) {
        throw new Exception('無效的訂閱資料');
    }

    $db = getDBConnection();
    
    // 儲存推送訂閱資訊
    $stmt = $db->prepare("
        INSERT INTO push_subscriptions (endpoint, auth_key, p256dh_key)
        VALUES (:endpoint, :auth_key, :p256dh_key)
        ON DUPLICATE KEY UPDATE
        auth_key = :auth_key,
        p256dh_key = :p256dh_key
    ");

    $stmt->execute([
        'endpoint' => $data['endpoint'],
        'auth_key' => $data['keys']['auth'],
        'p256dh_key' => $data['keys']['p256dh']
    ]);

    echo json_encode(['status' => 'success']);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
} 