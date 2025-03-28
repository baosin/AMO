<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../config/database.php';

try {
    $db = getDBConnection();
    $db->exec("SET NAMES utf8mb4");
    
    // 檢查是否有 since 參數
    $since = isset($_GET['since']) ? intval($_GET['since']) : 0;
    
    // 如果有 since 參數，只獲取新訂單
    if ($since > 0) {
        $stmt = $db->prepare("
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
            WHERE DATE(o.created_at) = CURDATE()
            AND UNIX_TIMESTAMP(o.created_at) * 1000 > ?
            ORDER BY o.created_at DESC
            LIMIT 10
        ");
        $stmt->execute([$since]);
    } else {
        // 獲取所有今日訂單
        $stmt = $db->prepare("
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
            WHERE DATE(o.created_at) = CURDATE()
            ORDER BY o.created_at DESC
            LIMIT 10
        ");
        $stmt->execute();
    }

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
        'hasNewOrders' => count($orders) > 0,
        'orders' => $orders
    ], JSON_UNESCAPED_UNICODE);
    
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => '資料庫錯誤：' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    error_log("General error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => '系統錯誤：' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
