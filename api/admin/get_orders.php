<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../../config/database.php';

try {
    $db = getDBConnection();
    $db->exec("SET NAMES utf8mb4");
    
    // 構建查詢條件
    $conditions = [];
    $params = [];
    
    // 日期過濾
    if (isset($_GET['date']) && !empty($_GET['date'])) {
        $conditions[] = "DATE(o.created_at) = ?";
        $params[] = $_GET['date'];
    }
    
    // 餐廳過濾
    if (isset($_GET['restaurant_id']) && !empty($_GET['restaurant_id'])) {
        $conditions[] = "r.id = ?";
        $params[] = $_GET['restaurant_id'];
    }
    
    // 組合 WHERE 子句
    $whereClause = !empty($conditions) ? " AND " . implode(" AND ", $conditions) : "";
    
    // 使用 LEFT JOIN 找出相同用戶中較舊的訂單
    $sql = "
        SELECT 
            o.id,
            o.user_name,
            o.quantity,
            o.note,
            o.rice_option,
            DATE(o.created_at) as order_date,
            o.created_at,
            m.id as menu_id,
            m.name as menu_name,
            m.price,
            r.id as restaurant_id,
            r.name as restaurant_name,
            mc.name as category_name
        FROM orders o
        JOIN menus m ON o.menu_id = m.id
        JOIN restaurants r ON m.restaurant_id = r.id
        LEFT JOIN menu_categories mc ON m.category_id = mc.id
        LEFT JOIN orders o2 ON o.user_name = o2.user_name 
            AND o.created_at < o2.created_at
        WHERE o2.id IS NULL AND 1=1 $whereClause
        ORDER BY o.created_at DESC
    ";
    
    error_log("SQL Query: " . $sql);
    error_log("Parameters: " . print_r($params, true));
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 獲取所有餐廳（包括歷史店家）
    $restaurantStmt = $db->query("
        SELECT DISTINCT r.id, r.name
        FROM restaurants r
        JOIN menus m ON m.restaurant_id = r.id
        JOIN orders o ON o.menu_id = m.id
        ORDER BY r.name ASC
    ");
    $restaurants = $restaurantStmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'status' => 'success',
        'orders' => $orders,
        'restaurants' => $restaurants
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    error_log("Error in get_orders.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
