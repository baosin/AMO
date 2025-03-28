<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../config/database.php';

try {
    $db = getDBConnection();
    
    // 獲取最新的活動消息
    $stmt = $db->prepare("SELECT message FROM admin_messages WHERE active = TRUE ORDER BY created_at DESC LIMIT 1");
    $stmt->execute();
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $message = $result ? $result['message'] : null;
    
    echo json_encode([
        'status' => 'success',
        'message' => $message
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    error_log("Error in get_admin_message.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
