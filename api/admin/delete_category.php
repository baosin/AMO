<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../../config/database.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);
try {
    // 獲取 POST 數據
    $rawData = file_get_contents('php://input');
    $data = json_decode($rawData, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('JSON 解析錯誤：' . json_last_error_msg());
    }
    
    if (empty($data['category_id'])) {
        throw new Exception('缺少分類 ID');
    }
    
    $db = getDBConnection();
    
    // 開始事務
    $db->beginTransaction();
    
    try {
        // 檢查分類是否存在
        $stmt = $db->prepare("SELECT id FROM menu_categories WHERE id = ?");
        $stmt->execute([$data['category_id']]);
        if (!$stmt->fetch()) {
            throw new Exception('分類不存在');
        }
        
        // 刪除該分類下的所有菜單項目
        $stmt = $db->prepare("DELETE FROM menus WHERE category_id = :category_id");
        $stmt->bindParam(':category_id', $data['category_id']);
        $stmt->execute();
        
        // 刪除分類
        $stmt = $db->prepare("DELETE FROM menu_categories WHERE id = :category_id");
        $stmt->bindParam(':category_id', $data['category_id']);
        $stmt->execute();
        
        // 提交事務
        $db->commit();
        
        echo json_encode([
            'status' => 'success',
            'message' => '分類已刪除'
        ], JSON_UNESCAPED_UNICODE);
        
    } catch (Exception $e) {
        // 如果出現錯誤，回滾事務
        $db->rollBack();
        throw $e;
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
