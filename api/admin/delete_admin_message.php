<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../../config/database.php';

try {
    if (!isset($_POST['id']) || empty($_POST['id'])) {
        throw new Exception('缺少必要參數');
    }

    $id = $_POST['id'];
    $db = getDBConnection();
    
    $stmt = $db->prepare("DELETE FROM admin_messages WHERE id = ?");
    $stmt->execute([$id]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'status' => 'success',
            'message' => '公告已刪除'
        ], JSON_UNESCAPED_UNICODE);
    } else {
        throw new Exception('找不到指定的公告');
    }
    
} catch(Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
