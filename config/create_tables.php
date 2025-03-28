<?php
require_once 'database.php';

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
    echo "管理者消息表創建成功\n";
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
