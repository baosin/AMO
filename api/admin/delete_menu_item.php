<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../../config/database.php';

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['id'])) {
        throw new Exception('缺少菜單項目 ID');
    }

    $db = getDBConnection();
    
    // 開始交易
    $db->beginTransaction();
    
    try {
        // 先將相關訂單的 menu_id 設為 NULL
        $stmt = $db->prepare("UPDATE orders SET menu_id = NULL WHERE menu_id = ?");
        $stmt->execute([$data['id']]);
        
        // 執行刪除
        $stmt = $db->prepare("DELETE FROM menus WHERE id = ?");
        $stmt->execute([$data['id']]);

        if ($stmt->rowCount() === 0) {
            throw new Exception('菜單項目不存在或已被刪除');
        }

        // 提交交易
        $db->commit();

        echo json_encode([
            'status' => 'success',
            'message' => '刪除成功'
        ], JSON_UNESCAPED_UNICODE);

    } catch (Exception $e) {
        // 回滾交易
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
