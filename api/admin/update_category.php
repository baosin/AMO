<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');
require_once '../../config/database.php';

// 確保請求方法為 POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

// 獲取請求的 JSON 內容
$data = json_decode(file_get_contents('php://input'), true);

// 檢查必需的參數
if (!isset($data['id'], $data['name'])) {
    echo json_encode(['status' => 'error', 'message' => 'Missing parameters']);
    exit;
}

try {
    $db = getDBConnection();

    $id = $data['id'];
    $name = $data['name'];

    // 更新分類
    $query = "UPDATE menu_categories SET name = :name WHERE id = :id";
    $stmt = $db->prepare($query);
    
    $stmt->bindParam(':name', $name, PDO::PARAM_STR);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update category']);
    }
    
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>