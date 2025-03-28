<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../config/database.php';

try {
    $db = getDBConnection();
    
    // 創建管理者消息表
    $sql = "CREATE TABLE IF NOT EXISTS admin_messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        message TEXT NOT NULL,
        active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    $db->exec($sql);
    
    echo json_encode([
        'status' => 'success',
        'message' => '資料表創建成功'
    ]);
    
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
