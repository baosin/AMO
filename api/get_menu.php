<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../config/database.php';

try {
    if (!isset($_GET['restaurant_id'])) {
        throw new Exception('缺少餐廳 ID');
    }

    $db = getDBConnection();
    
    // 先獲取所有分類
    $stmt = $db->prepare("
        SELECT id, name
        FROM menu_categories
        WHERE restaurant_id = ?
        ORDER BY sort_order ASC
    ");
    $stmt->execute([$_GET['restaurant_id']]);
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 獲取所有菜單項目
    $stmt = $db->prepare("
        SELECT m.*, c.name as category_name
        FROM menus m
        LEFT JOIN menu_categories c ON m.category_id = c.id
        WHERE m.restaurant_id = ? AND m.is_available = 1
        ORDER BY c.sort_order ASC, m.name ASC
    ");
    $stmt->execute([$_GET['restaurant_id']]);
    $menuItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 將菜單項目按分類組織
    $organizedMenu = [];
    $uncategorized = [];
    
    foreach ($menuItems as $item) {
        if ($item['category_id']) {
            if (!isset($organizedMenu[$item['category_id']])) {
                $organizedMenu[$item['category_id']] = [
                    'name' => $item['category_name'],
                    'items' => []
                ];
            }
            $organizedMenu[$item['category_id']]['items'][] = [
                'id' => $item['id'],
                'name' => $item['name'],
                'price' => $item['price']
            ];
        } else {
            $uncategorized[] = [
                'id' => $item['id'],
                'name' => $item['name'],
                'price' => $item['price']
            ];
        }
    }
    
    // 如果有未分類的項目，添加到最後
    if (!empty($uncategorized)) {
        $organizedMenu['uncategorized'] = [
            'name' => '其他',
            'items' => $uncategorized
        ];
    }
    
    echo json_encode([
        'status' => 'success',
        'menu' => array_values($organizedMenu)
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
