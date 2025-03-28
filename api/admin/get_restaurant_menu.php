<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../../config/database.php';

try {
    if (!isset($_GET['restaurant_id']) || !is_numeric($_GET['restaurant_id'])) {
        throw new Exception('缺少或無效的餐廳 ID');
    }

    $restaurantId = (int)$_GET['restaurant_id'];
    
    $db = getDBConnection();
    
    // 獲取餐廳菜單
    $stmt = $db->prepare("
        SELECT id, name, price, is_available
        FROM menus
        WHERE restaurant_id = ? AND is_available = 1
        ORDER BY name ASC
    ");
    
    $stmt->execute([$restaurantId]);
    $menu = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'status' => 'success',
        'menu' => $menu
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
