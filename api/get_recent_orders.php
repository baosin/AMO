<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../config/database.php';

try {
    $db = getDBConnection();
    
    // 獲取最近的訂單（最新 50 筆）
    $stmt = $db->prepare("
        SELECT o.*, m.name as menu_name, m.price,
               DATE_FORMAT(o.created_at, '%H:%i:%s') as order_time
        FROM orders o
        JOIN menus m ON o.menu_id = m.id
        WHERE DATE(o.created_at) = CURDATE()
        ORDER BY o.created_at DESC
        LIMIT 50
    ");
    $stmt->execute();
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'status' => 'success',
        'orders' => $orders
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>
