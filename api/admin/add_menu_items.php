<?php
header('Content-Type: application/json; charset=utf-8');
// 關閉 PHP 錯誤輸出
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
    
    if (empty($data['items']) || !is_array($data['items'])) {
        throw new Exception('缺少品項資料');
    }
    
    // 檢查 restaurant_id
    if (empty($data['restaurant_id'])) {
        throw new Exception('缺少餐廳 ID');
    }
    
    $db = getDBConnection();
    
    // 檢查餐廳是否存在
    $checkRestaurant = $db->prepare("SELECT id FROM restaurants WHERE id = ?");
    $checkRestaurant->execute([(int) $data['restaurant_id']]);
    
    if (!$checkRestaurant->fetch()) {
        throw new Exception('指定的餐廳（ID: ' . $data['restaurant_id'] . '）不存在');
    }
    
    $db->beginTransaction();
    
    try {
        // 準備插入語句
        $stmt = $db->prepare("
            INSERT INTO menus (restaurant_id, name, price, category_id, is_available, created_at)
            VALUES (:restaurant_id, :name, :price, :category_id, 1, NOW())
        ");
        
        // 批量插入品項
        foreach ($data['items'] as $item) {
            $stmt->execute([
                ':restaurant_id' => (int) $data['restaurant_id'],
                ':name'          => $item['name'],
                ':price'         => $item['price'],
                ':category_id' => isset($item['category_id']) ? $item['category_id'] : null
            ]);
        }
        
        $db->commit();
        
        echo json_encode([
            'status' => 'success',
            'message' => '品項新增成功'
        ], JSON_UNESCAPED_UNICODE);
        
    } catch (Exception $e) {
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
