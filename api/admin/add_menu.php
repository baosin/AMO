<?php
header('Content-Type: application/json');
require_once '../../config/database.php';

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['name']) || !isset($data['price'])) {
        throw new Exception('缺少必要資料');
    }

    $db = getDBConnection();
    
    $stmt = $db->prepare("INSERT INTO menus (name, price) VALUES (?, ?)");
    $stmt->execute([$data['name'], $data['price']]);

    echo json_encode([
        'status' => 'success',
        'message' => '新增成功'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>
