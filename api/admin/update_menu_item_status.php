<?php
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
error_reporting(0);

require_once '../../config/database.php';

try {
    // 獲取 POST 數據
    $rawData = file_get_contents('php://input');
    $data = json_decode($rawData, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('JSON 解析錯誤：' . json_last_error_msg());
    }
    
    if (!isset($data['item_id']) || !isset($data['is_available'])) {
        throw new Exception('缺少必要參數');
    }
    
    $db = getDBConnection();
    
    // 更新菜單項目狀態
    $stmt = $db->prepare("UPDATE menus SET is_available = ? WHERE id = ?");
    $result = $stmt->execute([
        $data['is_available'] ? 1 : 0,
        (int) $data['item_id']
    ]);
    
    if ($result) {
        echo json_encode([
            'status' => 'success',
            'message' => '更新成功'
        ]);
    } else {
        throw new Exception('更新失敗');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}