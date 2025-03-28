<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../../config/database.php';

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['restaurant_id']) || !isset($data['name']) || !isset($data['price'])) {
        throw new Exception('缺少必要參數');
    }
    
    $db = getDBConnection();
    $stmt = $db->prepare("
        INSERT INTO menus (restaurant_id, name, price, category_id)
        VALUES (?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $data['restaurant_id'],
        $data['name'],
        $data['price'],
        isset($data['category_id']) ? $data['category_id'] : null;
    ]);
    
    $menuId = $db->lastInsertId();
    
    echo json_encode([
        'status' => 'success',
        'message' => '菜單項目已新增',
        'menu_item' => [
            'id' => $menuId,
            'name' => $data['name'],
            'price' => $data['price'],
            'category_id' => $data['category_id'] ?? null
        ]
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
