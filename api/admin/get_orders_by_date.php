<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../../config/database.php';

try {
    $db = getDBConnection();
    
    // 獲取過去 7 天的訂單
    $stmt = $db->prepare("
        SELECT 
            o.id,
            o.user_name,
            o.quantity,
            o.created_at,
            o.note,
            o.rice_option,
            m.name as menu_name,
            m.price,
            m.id as menu_id,
            r.name as restaurant_name,
            r.id as restaurant_id,
            DATE(o.created_at) as order_date
        FROM orders o
        JOIN menus m ON o.menu_id = m.id
        JOIN restaurants r ON m.restaurant_id = r.id
        WHERE o.created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
        ORDER BY o.created_at DESC
    ");
    $stmt->execute();
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 按日期和餐廳分組訂單
    $groupedOrders = [];
    foreach ($orders as $order) {
        $date = $order['order_date'];
        $restaurantId = $order['restaurant_id'];
        
        if (!isset($groupedOrders[$date])) {
            $groupedOrders[$date] = [];
        }
        if (!isset($groupedOrders[$date][$restaurantId])) {
            $groupedOrders[$date][$restaurantId] = [
                'restaurant_name' => $order['restaurant_name'],
                'orders' => []
            ];
        }
        
        $groupedOrders[$date][$restaurantId]['orders'][] = [
            'id' => $order['id'],
            'user_name' => $order['user_name'],
            'menu_name' => $order['menu_name'],
            'quantity' => $order['quantity'],
            'price' => $order['price'],
            'rice_option' => $order['rice_option'],
            'note' => $order['note'],
            'menu_id' => $order['menu_id'],
            'created_at' => date('H:i', strtotime($order['created_at']))
        ];
    }
    
    // 將日期作為鍵的關聯數組轉換為數組，並按日期降序排序
    $sortedOrders = [];
    foreach ($groupedOrders as $date => $restaurants) {
        $sortedOrders[] = [
            'date' => $date,
            'restaurants' => array_values($restaurants)
        ];
    }
    usort($sortedOrders, function($a, $b) {
        return strcmp($b['date'], $a['date']);
    });
    
    echo json_encode([
        'status' => 'success',
        'orders' => $sortedOrders
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    error_log("Error in get_orders_by_date.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>
