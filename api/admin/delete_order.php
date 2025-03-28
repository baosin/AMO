<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../../config/database.php';

try {
    $db = getDBConnection();
    
    $orderId = isset($_GET['order_id']) ? $_GET['order_id'] : null;
    error_log("Deleting all orders related to order ID: " . $orderId);
    
    if (!$orderId) {
        throw new Exception('缺少訂單 ID');
    }

    $db->beginTransaction();

    try {
        // 取得該筆訂單的資訊
        $stmt = $db->prepare("
            SELECT o.user_name, o.created_at, r.id as restaurant_id
            FROM orders o
            JOIN menus m ON o.menu_id = m.id
            JOIN restaurants r ON m.restaurant_id = r.id
            WHERE o.id = ?
        ");
        $stmt->execute([$orderId]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            throw new Exception('找不到訂單');
        }

        // 檢查是否已收單
        $stmt = $db->prepare("
            SELECT COUNT(*) as count 
            FROM closed_orders 
            WHERE restaurant_id = ? AND DATE(close_date) = DATE(?)
        ");
        $stmt->execute([$order['restaurant_id'], $order['created_at']]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result['count'] > 0) {
            throw new Exception('訂單已收單，無法刪除');
        }

        // ������ 刪除相同 user_name、restaurant_id、同一日的所有訂單
        $stmt = $db->prepare("
            DELETE o FROM orders o
            JOIN menus m ON o.menu_id = m.id
            WHERE o.user_name = ? 
              AND m.restaurant_id = ? 
              AND DATE(o.created_at) = DATE(?)
        ");
        $stmt->execute([
            $order['user_name'],
            $order['restaurant_id'],
            $order['created_at']
        ]);

        $db->commit();

        echo json_encode([
            'status' => 'success',
            'message' => '已刪除該使用者本日所有訂單'
        ], JSON_UNESCAPED_UNICODE);

    } catch (Exception $e) {
        $db->rollBack();
        throw $e;
    }

} catch (Exception $e) {
    error_log("Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>
