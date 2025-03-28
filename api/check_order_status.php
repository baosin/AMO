<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../config/database.php';

try {
    $db = getDBConnection();
    $date = isset($data['date']) ? $data['date'] : date('Y-m-d');
    
    // 檢查是否已經收單
    $stmt = $db->prepare("
        SELECT COUNT(*) as count 
        FROM closed_orders 
        WHERE DATE(close_date) = ?
    ");
    $stmt->execute([$date]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'status' => 'success',
        'is_closed' => $result['count'] > 0
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>
