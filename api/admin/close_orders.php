<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../../config/database.php';

try {
    $db = getDBConnection();
    
    // 獲取 POST 數據
    $data = json_decode(file_get_contents('php://input'), true);
    $date = isset($data['date']) ? $data['date'] : date('Y-m-d');
    
    if (!$date) {
        throw new Exception('缺少日期參數');
    }
    
    // 開始交易
    $db->beginTransaction();
    
    try {
        // 檢查是否已經收單
        $stmt = $db->prepare("
            SELECT COUNT(*) as count 
            FROM closed_orders 
            WHERE DATE(close_date) = ?
        ");
        $stmt->execute([$date]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['count'] > 0) {
            throw new Exception('指定日期已經收單');
        }
        
        // 取得所有活動中的餐廳
        $stmt = $db->prepare("
            SELECT id 
            FROM restaurants 
            WHERE is_active = 1
        ");
        $stmt->execute();
        $restaurants = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // 為每個活動中的餐廳新增收單記錄
        $insertStmt = $db->prepare("
            INSERT INTO closed_orders (restaurant_id, close_date) 
            VALUES (?, ?)
        ");
        
        foreach ($restaurants as $restaurant) {
            $insertStmt->execute([$restaurant['id'], $date]);
        }
        
        // 提交交易
        $db->commit();
        
        echo json_encode([
            'status' => 'success',
            'message' => '收單成功'
        ], JSON_UNESCAPED_UNICODE);
        
    } catch (Exception $e) {
        // 回滾交易
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
?>
