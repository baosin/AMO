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
    
    if (empty($data['id']) || !isset($data['name'])) {
        throw new Exception('缺少必要資料');
    }
    
    $db = getDBConnection();
    
    // 檢查分類是否存在
    $stmt = $db->prepare("SELECT id FROM menu_categories WHERE id = ?");
    $stmt->execute([$data['id']]);
    if (!$stmt->fetch()) {
        throw new Exception('分類不存在');
    }
    
    // 更新分類
    $stmt = $db->prepare("
        UPDATE menu_categories 
        SET name = ?
        WHERE id = ?
    ");
    
    $stmt->execute([
        $data['name'],
        $data['id']
    ]);
    
    echo json_encode([
        'status' => 'success',
        'message' => '分類修改成功'
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}