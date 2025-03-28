<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../../config/database.php';

try {
    if (!isset($_GET['id'])) {
        throw new Exception('缺少店家 ID');
    }

    $db = getDBConnection();
    $stmt = $db->prepare("SELECT * FROM restaurants WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $restaurant = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$restaurant) {
        throw new Exception('找不到指定的店家');
    }

    echo json_encode([
        'status' => 'success',
        'restaurant' => $restaurant
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>
