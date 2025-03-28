<?php
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
error_reporting(0);

require_once '../../config/database.php';

try {
    // 獲取 POST 數據
    $id = $_POST['id'];
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    
    if (empty($id) || empty($name)) {
        throw new Exception('缺少必要參數');
    }
    
    $db = getDBConnection();
    
    // 如果有上傳新圖片
    $image_url = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../../uploads/restaurants/';
        
        // 確保上傳目錄存在
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        // 獲取檔案資訊
        $file_info = pathinfo($_FILES['image']['name']);
        $file_extension = strtolower($file_info['extension']);
        
        // 檢查檔案類型
        $allowed_types = array('jpg', 'jpeg', 'png', 'gif');
        if (!in_array($file_extension, $allowed_types)) {
            throw new Exception('不支援的檔案類型');
        }
        
        // 生成新的檔案名
        $new_filename = uniqid() . '.' . $file_extension;
        $upload_path = $upload_dir . $new_filename;
        
        // 移動上傳的檔案
        if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
            $image_url = 'uploads/restaurants/' . $new_filename;
            
            // 刪除舊圖片
            $stmt = $db->prepare("SELECT image_url FROM restaurants WHERE id = ?");
            $stmt->execute([$id]);
            $old_image = $stmt->fetchColumn();
            
            if ($old_image && file_exists('../../' . $old_image)) {
                unlink('../../' . $old_image);
            }
        } else {
            throw new Exception('圖片上傳失敗');
        }
    }
    
    // 更新資料庫
    if ($image_url) {
        $stmt = $db->prepare("UPDATE restaurants SET name = ?, phone = ?, image_url = ? WHERE id = ?");
        $result = $stmt->execute([$name, $phone, $image_url, $id]);
    } else {
        $stmt = $db->prepare("UPDATE restaurants SET name = ?, phone = ? WHERE id = ?");
        $result = $stmt->execute([$name, $phone, $id]);
    }
    
    if ($result) {
        echo json_encode([
            'status' => 'success',
            'message' => '更新成功'
        ]);
    } else {
        throw new Exception('更新失敗');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>
