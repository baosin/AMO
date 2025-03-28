<?php
header('Content-Type: application/json');
require_once '../config/database.php';

try {
    $db = getDBConnection();
    $db->exec("SET NAMES utf8mb4");
    
    // 使用子查詢找出每個用戶最新的訂單時間
    $stmt = $db->prepare("
        WITH LatestOrders AS (
            SELECT 
                user_name,
                MAX(created_at) as latest_time
            FROM orders
            WHERE DATE(created_at) = CURDATE()
            GROUP BY user_name
        )
        SELECT 
            o.id,
            o.user_name,
            o.created_at,
            m.name as item_name,
            o.quantity,
            o.note,
            o.rice_option
        FROM orders o
        LEFT JOIN menus m ON o.menu_id = m.id
        INNER JOIN LatestOrders lo 
            ON o.user_name = lo.user_name 
            AND o.created_at = lo.latest_time
        WHERE DATE(o.created_at) = CURDATE()
        ORDER BY o.created_at DESC
    ");
    
    $stmt->execute();
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 處理訂單顯示格式
    foreach ($orders as &$order) {
        // 組合訂單項目顯示文字
        $itemText = $order['item_name'];
        if ($order['quantity'] > 1) {
            $itemText .= " x {$order['quantity']}";
        }
        if ($order['rice_option']) {
            $itemText .= " ({$order['rice_option']})";
        }
        if ($order['note']) {
            $itemText .= " - {$order['note']}";
        }
        $order['item_name'] = $itemText;
        
        // 移除不需要的欄位
        unset($order['quantity']);
        unset($order['note']);
        unset($order['rice_option']);
    }

    echo json_encode([
        'status' => 'success',
        'orders' => $orders
    ], JSON_UNESCAPED_UNICODE);
    
} catch(Exception $e) {
    error_log($e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => '資料庫錯誤'
    ]);
}
