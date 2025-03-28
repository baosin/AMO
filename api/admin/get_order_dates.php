<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../../config/database.php';

try {
    $db = getDBConnection();
    
    // 獲取所有有訂單的日期
    $stmt = $db->prepare("
        SELECT DISTINCT DATE(created_at) as order_date 
        FROM orders 
        ORDER BY order_date DESC
    ");
    $stmt->execute();
    $dates = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'status' => 'success',
        'dates' => $dates
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
