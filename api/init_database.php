<?php
header('Content-Type: application/json; charset=utf-8');

try {
    $conn = new PDO(
        "mysql:host=localhost",
        "root",
        "",
        array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
    );
    
    // 創建資料庫（如果不存在）
    $conn->exec("CREATE DATABASE IF NOT EXISTS amo_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $conn->exec("USE amo_system");
    
    // 創建管理者消息表
    $sql = "CREATE TABLE IF NOT EXISTS admin_messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        message TEXT NOT NULL,
        active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $conn->exec($sql);
    
    echo json_encode([
        'status' => 'success',
        'message' => '資料庫初始化成功'
    ], JSON_UNESCAPED_UNICODE);
    
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
