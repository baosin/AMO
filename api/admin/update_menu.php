<?php
header('Content-Type: application/json');
require_once '../../config/database.php';

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['id']) || !isset($data['price'])) {
        throw new Exception('缺少必要資料');
    }

    $db = getDBConnection();
    
    $stmt = $db->prepare("UPDATE menus SET price = ? WHERE id = ?");
    $stmt->execute([$data['price'], $data['id']]);

    echo json_encode([
        'status' => 'success',
        'message' => '更新成功'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>
