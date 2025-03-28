<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../../config/database.php';

try {
    $db = getDBConnection();
    
    // 獲取訂單總數和總金額
    $stmt = $db->query("
        SELECT 
            COUNT(*) as total_count,
            SUM(o.quantity * m.price) as total_amount
        FROM orders o
        JOIN menus m ON o.menu_id = m.id
    ");
    $totals = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // 獲取各便當統計
    $stmt = $db->query("
        SELECT 
            m.name,
            m.price,
            SUM(o.quantity) as count,
            SUM(o.quantity * m.price) as subtotal
        FROM orders o
        JOIN menus m ON o.menu_id = m.id
        GROUP BY m.id, m.name, m.price
        ORDER BY m.name
    ");
    $menu_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'status' => 'success',
        'statistics' => [
            'total_count' => (int)$totals['total_count'],
            'total_amount' => (float)$totals['total_amount'],
            'menu_items' => array_map(function($item) {
                return [
                    'name' => $item['name'],
                    'count' => (int)$item['count'],
                    'price' => (float)$item['price'],
                    'subtotal' => (float)$item['subtotal']
                ];
            }, $menu_items)
        ]
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>
