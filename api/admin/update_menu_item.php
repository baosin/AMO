<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../../config/database.php';

try {
    // 獲取 POST 數據
    $rawData = file_get_contents('php://input');
    $data = json_decode($rawData, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('JSON 解析錯誤：' . json_last_error_msg());
    }
    
    if (empty($data['id'])) {
        throw new Exception('缺少必要資料');
    }
    
    $db = getDBConnection();
    
    // 檢查品項是否存在
    $stmt = $db->prepare("SELECT id FROM menus WHERE id = ?");
    $stmt->execute([$data['id']]);
    if (!$stmt->fetch()) {
        throw new Exception('品項不存在');
    }
    
    // 準備更新的欄位
    $updates = [];
    $params = [];
    
    if (isset($data['name'])) {
        $updates[] = "name = ?";
        $params[] = $data['name'];
    }
    
    if (isset($data['price'])) {
        $updates[] = "price = ?";
        $params[] = $data['price'];
    }
    
    if (isset($data['category_id'])) {
        $updates[] = "category_id = ?";
        $params[] = $data['category_id'];
    }
    
    if (empty($updates)) {
        throw new Exception('沒有要更新的資料');
    }
    
    // 添加 id 到參數陣列
    $params[] = $data['id'];
    
    // 更新品項
    $sql = "UPDATE menus SET " . implode(", ", $updates) . " WHERE id = ?";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    
    echo json_encode([
        'status' => 'success',
        'message' => '品項更新成功'
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
