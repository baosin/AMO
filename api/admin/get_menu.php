<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../../config/database.php';

try {
    if (!isset($_GET['restaurant_id'])) {
        throw new Exception('缺少餐廳 ID');
    }
    
    $db = getDBConnection();
    $stmt = $db->prepare("
        SELECT m.id, m.name, m.price, m.category_id, m.is_available, c.name as category_name
        FROM menus m
        LEFT JOIN menu_categories c ON m.category_id = c.id
        WHERE m.restaurant_id = ?
        ORDER BY c.id ASC, m.name ASC
    ");
    
    $stmt->execute([$_GET['restaurant_id']]);
    $menu = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 同時獲取分類列表
    $stmt = $db->prepare("
        SELECT *
        FROM menu_categories
        WHERE restaurant_id = ?
        ORDER BY id ASC
    ");
    
    $stmt->execute([$_GET['restaurant_id']]);
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'status' => 'success',
        'menu' => $menu,
        'categories' => $categories
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
