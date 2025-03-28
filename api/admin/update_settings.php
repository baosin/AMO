<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../../config/database.php';

try {
    // 獲取 POST 數據
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['order_deadline']) || !isset($data['is_ordering_active'])) {
        throw new Exception('缺少必要參數');
    }

    $db = getDBConnection();
    
    // 更新或插入設定
    $stmt = $db->prepare("
        INSERT INTO settings (id, order_deadline, is_ordering_active, selected_restaurant_id) 
        VALUES (1, :deadline, :active, :restaurant_id)
        ON DUPLICATE KEY UPDATE 
        order_deadline = VALUES(order_deadline),
        is_ordering_active = VALUES(is_ordering_active),
        selected_restaurant_id = VALUES(selected_restaurant_id)
    ");

    $stmt->execute([
        ':deadline' => $data['order_deadline'],
        ':active' => $data['is_ordering_active'] ? 1 : 0,
        ':restaurant_id' => $data['selected_restaurant_id'] ?: null
    ]);

    echo json_encode([
        'status' => 'success',
        'message' => '設定更新成功'
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>
