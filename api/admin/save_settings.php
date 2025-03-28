<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../../config/database.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 獲取 POST 資料
$input = file_get_contents('php://input');
error_log("Received input: " . $input);

$data = json_decode($input, true);
error_log("Decoded data: " . print_r($data, true));

if (!$data) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => '無效的請求資料'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $db = getDBConnection();
    error_log("Database connection established");
    
    $db->exec("SET NAMES utf8mb4");
    
    // 開始交易
    $db->beginTransaction();
    error_log("Transaction started");
    
    // 檢查是否已有設定
    $stmt = $db->query("SELECT COUNT(*) FROM settings");
    $hasSettings = $stmt->fetchColumn() > 0;
    error_log("Has existing settings: " . ($hasSettings ? "yes" : "no"));
    
    // 將 order_deadline 從 "2025-02-20T17:30" 格式轉換為 MySQL datetime 格式
    $orderDeadline = date('Y-m-d H:i:s', strtotime($data['order_deadline']));
    error_log("Formatted order_deadline: " . $orderDeadline);
    
    if ($hasSettings) {
        // 更新設定
        $stmt = $db->prepare("
            UPDATE settings 
            SET order_deadline = ?, 
                selected_restaurant_id = ?, 
                is_ordering_active = ?,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = (SELECT id FROM (SELECT id FROM settings LIMIT 1) as s)
        ");
        error_log("Prepared UPDATE statement");
    } else {
        // 插入新設定
        $stmt = $db->prepare("
            INSERT INTO settings 
            (order_deadline, selected_restaurant_id, is_ordering_active, created_at, updated_at)
            VALUES (?, ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
        ");
        error_log("Prepared INSERT statement");
    }
    
    // 執行更新或插入
    $params = [
        $orderDeadline,
        $data['selected_restaurant_id'],
        $data['is_ordering_active'] ? 1 : 0
    ];
    error_log("Parameters: " . print_r($params, true));
    
    $result = $stmt->execute($params);
    error_log("Statement executed with result: " . ($result ? "success" : "failure"));
    
    // 如果開放訂餐，清除收單記錄
    if ($data['is_ordering_active']) {
        $stmt = $db->prepare("DELETE FROM closed_orders WHERE DATE(created_at) = CURDATE()");
        $stmt->execute();
        error_log("Cleared closed orders for today");
    }
    
    // 提交交易
    $db->commit();
    error_log("Transaction committed");
    
    echo json_encode([
        'status' => 'success',
        'message' => '設定已儲存'
    ], JSON_UNESCAPED_UNICODE);
    
} catch (PDOException $e) {
    // 發生錯誤時回滾交易
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
        error_log("Transaction rolled back");
    }
    
    error_log("Database error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => '資料庫錯誤：' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    // 發生錯誤時回滾交易
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
        error_log("Transaction rolled back");
    }
    
    error_log("General error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => '系統錯誤：' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>
