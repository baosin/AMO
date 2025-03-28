<?php
// 設置錯誤處理
error_reporting(E_ALL);
ini_set('display_errors', 0);

// 設置響應頭
header('Content-Type: application/json; charset=utf-8');

try {
    require_once '../../config/database.php';
    
    $db = getDBConnection();
    $stmt = $db->prepare("SELECT * FROM restaurants ORDER BY name");
    $stmt->execute();
    $restaurants = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'success',
        'restaurants' => $restaurants
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>
