<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../config/database.php';

try {
    $db = getDBConnection();
    $db->exec("SET NAMES utf8mb4");

    // 獲取 POST 數據
    $rawData = file_get_contents('php://input');
    error_log("Received data: " . $rawData);
    
    $data = json_decode($rawData, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('JSON 解析錯誤：' . json_last_error_msg());
    }
    
    error_log("Decoded data: " . print_r($data, true));
    
    if (!isset($data['user_name']) || !isset($data['items']) || empty($data['items'])) {
        throw new Exception('缺少必要資料');
    }

    $db->beginTransaction();

    try {
        foreach ($data['items'] as $item) {
            if (!isset($item['id']) || !is_numeric($item['id'])) {
                throw new Exception('缺少或無效的菜單ID');
            }
            if (!isset($item['quantity']) || !is_numeric($item['quantity']) || $item['quantity'] < 1) {
                throw new Exception('缺少或無效的數量');
            }
            
            // 插入訂單
            $stmt = $db->prepare("
                INSERT INTO orders (user_name, menu_id, quantity, note, rice_option, created_at)
                VALUES (:user_name, :menu_id, :quantity, :note, :rice_option, NOW())
            ");
            
            $stmt->execute([
                'user_name' => $data['user_name'],
                'menu_id' => $item['id'],
                'quantity' => $item['quantity'],
                'note' => isset($item['note']) ? $item['note'] : '', // 使用個別餐點的備註
                'rice_option' => isset($item['rice_option']) ? $item['rice_option'] : null
            ]);
        }

        $db->commit();
        
        echo json_encode([
            'status' => 'success',
            'message' => '訂單已成功送出'
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
