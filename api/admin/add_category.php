<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../../config/database.php';

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['restaurant_id']) || !isset($data['name'])) {
        throw new Exception('缺少必要參數');
    }
    
    $db = getDBConnection();
    
    // 獲取最大的排序值
    $stmt = $db->prepare("SELECT MAX(sort_order) as max_order FROM menu_categories WHERE restaurant_id = ?");
    $stmt->execute([$data['restaurant_id']]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $nextOrder = (isset($result['max_order']) ? $result['max_order'] : 0) + 1;
    
    $stmt = $db->prepare("
        INSERT INTO menu_categories (restaurant_id, name, sort_order)
        VALUES (?, ?, ?)
    ");
    
    $stmt->execute([
        $data['restaurant_id'],
        $data['name'],
        $nextOrder
    ]);
    
    $categoryId = $db->lastInsertId();
    
    echo json_encode([
        'status' => 'success',
        'message' => '分類已新增',
        'category' => [
            'id' => $categoryId,
            'name' => $data['name'],
            'sort_order' => $nextOrder
        ]
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
