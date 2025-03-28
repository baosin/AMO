<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../config/database.php';

try {
    $db = getDBConnection();
    
    // 獲取當前日期
    $today = date('Y-m-d');
    
    // 獲取系統設定
    $stmt = $db->query("
        SELECT 
            s.*,
            r.name as restaurant_name,
            r.phone as restaurant_phone
        FROM settings s
        LEFT JOIN restaurants r ON s.selected_restaurant_id = r.id 
            AND r.is_active = 1
        WHERE s.id = 1
    ");
    $settings = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$settings) {
        // 如果沒有設定，創建預設設定
        $stmt = $db->prepare("
            INSERT INTO settings (id, order_deadline, is_ordering_active) 
            VALUES (1, ?, false)
        ");
        $stmt->execute([date('Y-m-d 11:00:00')]);
        
        $settings = [
            'id' => 1,
            'order_deadline' => date('Y-m-d 11:00:00'),
            'is_ordering_active' => false,
            'selected_restaurant_id' => null,
            'restaurant_name' => null,
            'restaurant_phone' => null
        ];
    }

    // 如果有選擇店家，檢查是否已收單
    if ($settings['selected_restaurant_id']) {
        // 檢查店家是否啟用
        $stmt = $db->prepare("
            SELECT is_active 
            FROM restaurants 
            WHERE id = ?
        ");
        $stmt->execute([$settings['selected_restaurant_id']]);
        $restaurant = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$restaurant || !$restaurant['is_active']) {
            $settings['is_ordering_active'] = false;
        }
    }

    echo json_encode([
        'status' => 'success',
        'settings' => $settings
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>
