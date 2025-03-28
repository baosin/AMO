<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../config/database.php';

try {
    $db = getDBConnection();
    
    // 獲取今天的訂單和餐廳開放時間
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
            r.name as restaurant_name,
            r.id as restaurant_id,
            s.created_at as order_open_time
        FROM orders o
        JOIN menus m ON o.menu_id = m.id
        JOIN restaurants r ON m.restaurant_id = r.id
        LEFT JOIN settings s ON DATE(s.created_at) = DATE(o.created_at)
            AND s.selected_restaurant_id = r.id
            AND s.is_ordering_active = 1
        WHERE DATE(o.created_at) = CURDATE()
        ORDER BY s.created_at DESC, o.created_at DESC
    ");
    $stmt->execute();
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 按餐廳分組訂單
    $groupedOrders = [];
    foreach ($orders as $order) {
        $restaurantId = $order['restaurant_id'];
        if (!isset($groupedOrders[$restaurantId])) {
            $orderOpenTime = $order['order_open_time'] 
                ? date('m/d H:i', strtotime($order['order_open_time']))
                : null;
            
            $groupedOrders[$restaurantId] = [
                'restaurant_name' => $order['restaurant_name'],
                'order_open_time' => $orderOpenTime,
                'raw_open_time' => $order['order_open_time'], // 用於排序
                'orders' => []
            ];
        }
        
        // 格式化訂單時間
        $order['created_at'] = date('m/d H:i', strtotime($order['created_at']));
        
        unset($order['restaurant_id'], $order['order_open_time']); // 移除多餘的欄位
        $groupedOrders[$restaurantId]['orders'][] = $order;
    }
    
    // 按開放時間排序餐廳
    uasort($groupedOrders, function($a, $b) {
        if (!$a['raw_open_time'] && !$b['raw_open_time']) return 0;
        if (!$a['raw_open_time']) return 1;
        if (!$b['raw_open_time']) return -1;
        return strtotime($b['raw_open_time']) - strtotime($a['raw_open_time']);
    });
    
    // 移除用於排序的原始時間
    foreach ($groupedOrders as &$restaurant) {
        unset($restaurant['raw_open_time']);
    }
    
    echo json_encode([
        'status' => 'success',
        'grouped_orders' => $groupedOrders
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    error_log("Error in get_order_feed.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>
