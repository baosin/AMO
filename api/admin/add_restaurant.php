<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../../config/database.php';

try {
    $data = $_POST;
    
    if (!isset($data['name']) || empty($data['name'])) {
        throw new Exception('請輸入店家名稱');
    }

    $db = getDBConnection();
    
    // 檢查店家名稱是否已存在
    $stmt = $db->prepare("SELECT id FROM restaurants WHERE name = ?");
    $stmt->execute([$data['name']]);
    if ($stmt->fetch()) {
        throw new Exception('店家名稱已存在');
    }
    
    $image_url = '';
    
    // 處理圖片上傳
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../../uploads/restaurants/';
        
        // 確保上傳目錄存在
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        // 產生唯一的檔案名稱
        $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $file_name = uniqid() . '.' . $file_extension;
        $upload_path = $upload_dir . $file_name;
        
        // 移動上傳的檔案
        if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
            $image_url = 'uploads/restaurants/' . $file_name;
        } else {
            throw new Exception('圖片上傳失敗');
        }
    }
    
    // 新增店家
    $stmt = $db->prepare("INSERT INTO restaurants (name, phone, image_url) VALUES (?, ?, ?)");
    $stmt->execute([
        $data['name'],
        isset($data['phone']) ? $data['phone'] : '',
        $image_url
    ]);
    
    echo json_encode([
        'status' => 'success',
        'message' => '新增成功'
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>
