<?php
header('Content-Type: application/json');
require_once '../../config/database.php';

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['restaurant_id']) || !isset($data['deadline'])) {
        throw new Exception('缺少必要資料');
    }

    $db = getDBConnection();
    $db->beginTransaction();

    // 更新系統設定
    $stmt = $db->prepare("
        INSERT INTO settings (order_deadline, is_ordering_active) 
        VALUES (?, 1)
        ON DUPLICATE KEY UPDATE 
            order_deadline = VALUES(order_deadline),
            is_ordering_active = 1
    ");
    $stmt->execute([$data['deadline']]);

    // 停用所有其他店家的今日設定
    $stmt = $db->prepare("
        UPDATE daily_restaurants 
        SET is_active = 0 
        WHERE date = CURRENT_DATE
    ");
    $stmt->execute();

    // 新增或更新今日店家
    $stmt = $db->prepare("
        INSERT INTO daily_restaurants (restaurant_id, date, is_active) 
        VALUES (?, CURRENT_DATE, 1)
        ON DUPLICATE KEY UPDATE 
            is_active = 1
    ");
    $stmt->execute([$data['restaurant_id']]);

    $db->commit();

    echo json_encode([
        'status' => 'success',
        'message' => '今日店家設定成功'
    ]);

} catch (Exception $e) {
    if (isset($db)) {
        $db->rollBack();
    }
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>
