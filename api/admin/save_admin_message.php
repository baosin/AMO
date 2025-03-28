<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../../config/database.php';

try {
    if (!isset($_POST['message']) || empty($_POST['message'])) {
        throw new Exception('請輸入公告內容');
    }

    $message = trim($_POST['message']); // 移除頭尾空白
    // ✅ 不使用 htmlspecialchars()，避免存入時轉換 `"` 為 `&quot;`

    $db = getDBConnection();
    
    // 確保表格存在
    $createTableSQL = "CREATE TABLE IF NOT EXISTS admin_messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        message TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $db->exec($createTableSQL);
    
    // 開始事務
    $db->beginTransaction();
    
    try {
        // 將新的公告插入到歷史記錄表
        $stmt = $db->prepare("INSERT INTO admin_messages (message) VALUES (?)");
        $stmt->execute([$message]);
        
        // 提交事務
        $db->commit();
        
        echo json_encode([
            'status' => 'success',
            'message' => '公告已保存'
        ], JSON_UNESCAPED_UNICODE);
        
    } catch (Exception $e) {
        // 如果出現錯誤，回滾事務
        $db->rollBack();
        throw $e;
    }
    
} catch(Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
