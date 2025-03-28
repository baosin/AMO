<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../../config/database.php';

try {
    $db = getDBConnection();
    
    // 獲取 POST 數據
    $data = json_decode(file_get_contents('php://input'), true);
    error_log("Received data: " . print_r($data, true));
    
    // 從 URL 獲取訂單 ID
    $orderId = isset($_GET['order_id']) ? $_GET['order_id'] : null;
    if (!$orderId) {
        throw new Exception('缺少訂單 ID');
    }
    
    // 開始交易
    $db->beginTransaction();
    
    try {
        // 檢查訂單是否存在
        $stmt = $db->prepare("
            SELECT o.*, m.price, r.id as restaurant_id
            FROM orders o
            JOIN menus m ON o.menu_id = m.id
            JOIN restaurants r ON m.restaurant_id = r.id
            WHERE o.id = ?
        ");
        $stmt->execute([$orderId]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$order) {
            throw new Exception('找不到訂單');
        }
        
        // 更新訂單
        $updateFields = [];
        $updateParams = [];
        
        // 更新姓名
        if (isset($data['user_name'])) {
            $updateFields[] = 'user_name = ?';
            $updateParams[] = $data['user_name'];
        }
        
        // 更新菜單項目
        if (isset($data['menu_id'])) {
            // 檢查菜單項目是否存在
            $stmt = $db->prepare("SELECT id FROM menus WHERE id = ?");
            $stmt->execute([$data['menu_id']]);
            $menu = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$menu) {
                throw new Exception('選擇的菜單項目不存在');
            }
            
            $updateFields[] = 'menu_id = ?';
            $updateParams[] = $data['menu_id'];
        }
        
        // 更新數量
        if (isset($data['quantity'])) {
            if (!is_numeric($data['quantity']) || $data['quantity'] < 1) {
                throw new Exception('數量必須大於 0');
            }
            $updateFields[] = 'quantity = ?';
            $updateParams[] = $data['quantity'];
        }
        
        // 更新備註
        if (isset($data['note'])) {
            $updateFields[] = 'note = ?';
            $updateParams[] = $data['note'];
        }
        
        // 更新飯量選項
        if (isset($data['rice_option'])) {
            $updateFields[] = 'rice_option = ?';
            $updateParams[] = $data['rice_option'];
        }
        
        if (empty($updateFields)) {
            throw new Exception('沒有要更新的欄位');
        }
        
        // 建立更新語句
        $sql = "UPDATE orders SET " . implode(', ', $updateFields) . " WHERE id = ?";
        $updateParams[] = $orderId;
        
        error_log("Update SQL: " . $sql);
        error_log("Update params: " . print_r($updateParams, true));
        
        $stmt = $db->prepare($sql);
        $stmt->execute($updateParams);
        
        // 提交交易
        $db->commit();
        
        echo json_encode([
            'status' => 'success',
            'message' => '訂單已更新'
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
?>
