<?php
header('Content-Type: application/json');
require_once '../../config/database.php';

try {
    $db = getDBConnection();
    
    // 獲取 POST 數據
    $subscription = json_decode(file_get_contents('php://input'), true);
    
    if (!$subscription) {
        throw new Exception('無效的訂閱數據');
    }
    
    // 檢查是否已存在訂閱
    $stmt = $db->prepare("SELECT id FROM push_subscriptions WHERE endpoint = ?");
    $stmt->execute([$subscription['endpoint']]);
    
    if ($stmt->rowCount() > 0) {
        // 更新現有訂閱
        $stmt = $db->prepare("
            UPDATE push_subscriptions 
            SET p256dh = ?, auth = ?, updated_at = NOW()
            WHERE endpoint = ?
        ");
        $stmt->execute([
            $subscription['keys']['p256dh'],
            $subscription['keys']['auth'],
            $subscription['endpoint']
        ]);
    } else {
        // 插入新訂閱
        $stmt = $db->prepare("
            INSERT INTO push_subscriptions (endpoint, p256dh, auth, created_at, updated_at)
            VALUES (?, ?, ?, NOW(), NOW())
        ");
        $stmt->execute([
            $subscription['endpoint'],
            $subscription['keys']['p256dh'],
            $subscription['keys']['auth']
        ]);
    }
    
    echo json_encode([
        'status' => 'success',
        'message' => '推送訂閱已保存'
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
} 