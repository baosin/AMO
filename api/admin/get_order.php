<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../../config/database.php';

try {
    if (!isset($_GET['order_id']) || !is_numeric($_GET['order_id'])) {
        throw new Exception('缺少或無效的訂單 ID');
    }

    $orderId = (int)$_GET['order_id'];
    
    $db = getDBConnection();
    
    // 獲取訂單資訊
    $stmt = $db->prepare("
        SELECT o.*, m.name as menu_name, m.price, r.id as restaurant_id, r.name as restaurant_name
        FROM orders o
        JOIN menus m ON o.menu_id = m.id
        JOIN restaurants r ON m.restaurant_id = r.id
        WHERE o.id = ?
    ");
    
    $stmt->execute([$orderId]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$order) {
        throw new Exception('找不到指定的訂單');
    }
    
    echo json_encode([
        'status' => 'success',
        'order' => $order
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
