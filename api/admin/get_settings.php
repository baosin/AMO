<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../../config/database.php';

try {
    $db = getDBConnection();
    $stmt = $db->query("SELECT * FROM settings WHERE id = 1");
    $settings = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$settings) {
        // 如果沒有設定，創建默認設定
        $defaultDeadline = date('Y-m-d H:i:s', strtotime('tomorrow 11:00'));
        $stmt = $db->prepare("
            INSERT INTO settings (order_deadline, is_ordering_active) 
            VALUES (?, ?)
        ");
        $stmt->execute([$defaultDeadline, true]);
        
        $settings = [
            'order_deadline' => $defaultDeadline,
            'is_ordering_active' => true,
            'selected_restaurant_id' => null
        ];
    } else {
        // 確保日期時間格式正確
        if ($settings['order_deadline']) {
            $settings['order_deadline'] = date('Y-m-d H:i:s', strtotime($settings['order_deadline']));
        } else {
            $settings['order_deadline'] = date('Y-m-d H:i:s', strtotime('tomorrow 11:00'));
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
