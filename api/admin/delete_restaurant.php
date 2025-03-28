<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../../config/database.php';

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['id'])) {
        throw new Exception('缺少店家 ID');
    }

    $db = getDBConnection();
    $db->beginTransaction();

    try {
        // 檢查店家是否存在
        $stmt = $db->prepare("SELECT id FROM restaurants WHERE id = ?");
        $stmt->execute([$data['id']]);
        if (!$stmt->fetch()) {
            throw new Exception('店家不存在');
        }

        // 刪除每日餐廳列表中的記錄
        $stmt = $db->prepare("DELETE FROM daily_restaurants WHERE restaurant_id = ?");
        $stmt->execute([$data['id']]);

        // 刪除已關閉的訂單記錄
        $stmt = $db->prepare("DELETE FROM closed_orders WHERE restaurant_id = ?");
        $stmt->execute([$data['id']]);

        // 先將訂單的 menu_id 設為 NULL
        $stmt = $db->prepare("
            UPDATE orders o
            JOIN menus m ON o.menu_id = m.id
            SET o.menu_id = NULL
            WHERE m.restaurant_id = ?
        ");
        $stmt->execute([$data['id']]);

        // 將菜單的 category_id 設為 NULL
        $stmt = $db->prepare("
            UPDATE menus 
            SET category_id = NULL 
            WHERE restaurant_id = ?
        ");
        $stmt->execute([$data['id']]);

        // 刪除菜單類別
        $stmt = $db->prepare("DELETE FROM menu_categories WHERE restaurant_id = ?");
        $stmt->execute([$data['id']]);

        // 刪除菜單
        $stmt = $db->prepare("DELETE FROM menus WHERE restaurant_id = ?");
        $stmt->execute([$data['id']]);

        // 最後刪除店家
        $stmt = $db->prepare("DELETE FROM restaurants WHERE id = ?");
        $stmt->execute([$data['id']]);

        $db->commit();
        
        echo json_encode([
            'status' => 'success',
            'message' => '店家已刪除'
        ], JSON_UNESCAPED_UNICODE);

    } catch (Exception $e) {
        $db->rollBack();
        throw $e;
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>
