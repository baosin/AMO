<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../../config/database.php';

try {
    $db = getDBConnection();
    
    // 確保表格存在
    $createTableSQL = "CREATE TABLE IF NOT EXISTS admin_messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        message TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $db->exec($createTableSQL);
    
    $stmt = $db->prepare("SELECT id, message, created_at FROM admin_messages ORDER BY created_at DESC");
    $stmt->execute();
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'status' => 'success',
        'messages' => $messages
    ], JSON_UNESCAPED_UNICODE);
    
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
