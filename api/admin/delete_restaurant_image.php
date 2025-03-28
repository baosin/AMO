<?php
require_once '../../config/database.php';

header('Content-Type: application/json');

function sendJsonResponse($status, $message, $data = null) {
    echo json_encode([
        'status' => $status,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

// 檢查請求方法
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse('error', '無效的請求方法');
}

// 獲取 POST 數據
$data = json_decode(file_get_contents('php://input'), true);
$imageId = $data['image_id'] ?? '';
$restaurantId = $data['restaurant_id'] ?? '';

if (empty($imageId) || empty($restaurantId)) {
    sendJsonResponse('error', '必要參數不能為空');
}

try {
    $conn = getDBConnection();
    if (!$conn) {
        throw new Exception('無法連接到資料庫');
    }

    // 開始事務
    $conn->beginTransaction();

    // 獲取圖片路徑
    $stmt = $conn->prepare("SELECT image_path FROM restaurant_images WHERE id = ? AND restaurant_id = ?");
    $stmt->execute([$imageId, $restaurantId]);
    $image = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$image) {
        throw new Exception('找不到指定的圖片');
    }

    // 刪除資料庫記錄
    $stmt = $conn->prepare("DELETE FROM restaurant_images WHERE id = ? AND restaurant_id = ?");
    $stmt->execute([$imageId, $restaurantId]);

    // 刪除實體檔案
    $filePath = '../../' . $image['image_path'];
    if (file_exists($filePath)) {
        unlink($filePath);
    }

    // 提交事務
    $conn->commit();

    sendJsonResponse('success', '圖片已刪除');

} catch (Exception $e) {
    if (isset($conn)) {
        $conn->rollBack();
    }
    sendJsonResponse('error', '刪除失敗：' . $e->getMessage());
}