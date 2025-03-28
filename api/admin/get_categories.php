<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../../config/database.php';

try {
    if (!isset($_GET['restaurant_id'])) {
        throw new Exception('缺少餐廳 ID');
    }
    
    $db = getDBConnection();
    $stmt = $db->prepare("
        SELECT id, name, sort_order
        FROM menu_categories
        WHERE restaurant_id = ?
        ORDER BY sort_order ASC
    ");
    
    $stmt->execute([$_GET['restaurant_id']]);
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'status' => 'success',
        'categories' => $categories
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
