<?php
header('Content-Type: application/json');
require_once '../config/database.php';

try {
    $db = getDBConnection();

    // 獲取今日店家和設定
    $stmt = $db->prepare("
        SELECT r.*, s.order_deadline, s.is_ordering_active
        FROM daily_restaurants dr
        JOIN restaurants r ON dr.restaurant_id = r.id
        JOIN settings s ON 1=1
        WHERE dr.date = CURRENT_DATE 
        AND dr.is_active = 1 
        AND r.is_active = 1
        LIMIT 1
    ");
    $stmt->execute();
    $restaurant = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$restaurant) {
        throw new Exception('今日尚未設定店家');
    }

    if (!$restaurant['is_ordering_active']) {
        throw new Exception('目前不在訂餐時間');
    }

    // 檢查是否超過截止時間
    $currentTime = date('H:i:s');
    if ($currentTime > $restaurant['order_deadline']) {
        throw new Exception('已超過今日訂餐截止時間');
    }

    // 獲取店家菜單
    $stmt = $db->prepare("
        SELECT id, name, price 
        FROM menus 
        WHERE restaurant_id = ? 
        AND is_available = 1
        ORDER BY name
    ");
    $stmt->execute([$restaurant['id']]);
    $menu = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 準備回傳資料
    $restaurantInfo = [
        'id' => $restaurant['id'],
        'name' => $restaurant['name'],
        'phone' => $restaurant['phone']
    ];

    echo json_encode([
        'status' => 'success',
        'restaurant' => $restaurantInfo,
        'deadline' => $restaurant['order_deadline'],
        'menu' => $menu
    ]);

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>
