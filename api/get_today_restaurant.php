<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../config/database.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
    $db = getDBConnection();
    $db->exec("SET NAMES utf8mb4");
    
    // 檢查設定表格的資料
    $stmt = $db->query("SELECT * FROM settings");
    $settings = $stmt->fetch(PDO::FETCH_ASSOC);
    error_log("Current settings: " . print_r($settings, true));
    
    // 從系統設定獲取選擇的餐廳
    $stmt = $db->prepare("
        SELECT r.id, r.name, r.phone, r.is_active, r.image_url,
               TIME_FORMAT(s.order_deadline, '%H:%i') as order_deadline_time,
               s.is_ordering_active
        FROM settings s
        LEFT JOIN restaurants r ON r.id = s.selected_restaurant_id
        WHERE s.is_ordering_active = 1
        LIMIT 1
    ");
    
    error_log("Executing restaurant query");
    error_log("Restaurant query SQL: " . $stmt->queryString);
    $stmt->execute();
    $restaurant = $stmt->fetch(PDO::FETCH_ASSOC);
    error_log("Restaurant data: " . print_r($restaurant, true));
    
    if ($restaurant && $restaurant['id']) {
        // 先檢查餐廳是否有菜單
        $menuCountStmt = $db->prepare("SELECT COUNT(*) FROM menus WHERE restaurant_id = ?");
        $menuCountStmt->execute([$restaurant['id']]);
        $menuCount = $menuCountStmt->fetchColumn();
        error_log("Menu count for restaurant {$restaurant['id']}: $menuCount");
        
        if ($menuCount > 0) {
            // 獲取餐廳的菜單
            $menuStmt = $db->prepare("
                SELECT id, name, price, is_available
                FROM menus
                WHERE restaurant_id = ?
                ORDER BY price DESC, name
            ");
            error_log("Executing menu query for restaurant " . $restaurant['id']);
            error_log("Menu query SQL: " . $menuStmt->queryString);
            $menuStmt->execute([$restaurant['id']]);
            $menus = $menuStmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("Menu data: " . print_r($menus, true));
            
            // 格式化輸出資料
            $response = [
                'status' => 'success',
                'restaurant' => [
                    'id' => $restaurant['id'],
                    'name' => $restaurant['name'],
                    'image_url' => $restaurant['image_url'],
                    'order_deadline' => $restaurant['order_deadline_time'],
                    'menus' => array_map(function($menu) {
                        return [
                            'id' => $menu['id'],
                            'name' => $menu['name'],
                            'price' => (float)$menu['price']
                        ];
                    }, $menus)
                ]
            ];
        } else {
            $response = [
                'status' => 'success',
                'restaurant' => null,
                'message' => '該餐廳尚未設定菜單'
            ];
        }
    } else {
        // 檢查設定表中是否有資料
        $stmt = $db->query("SELECT COUNT(*) FROM settings");
        error_log("Settings count query SQL: " . $stmt->queryString);
        $hasSettings = $stmt->fetchColumn() > 0;
        error_log("Has settings: " . ($hasSettings ? "yes" : "no"));
        
        if (!$hasSettings) {
            $message = '尚未設定任何餐廳';
        } else {
            $stmt = $db->query("SELECT is_ordering_active, selected_restaurant_id FROM settings LIMIT 1");
            error_log("Settings query SQL: " . $stmt->queryString);
            $settings = $stmt->fetch(PDO::FETCH_ASSOC);
            error_log("Settings data: " . print_r($settings, true));
            
            if (!$settings['selected_restaurant_id']) {
                $message = '尚未選擇餐廳';
            } else if (!$settings['is_ordering_active']) {
                $message = '尚未開放點餐';
            } else {
                $message = '今日尚未選擇餐廳或尚未開放點餐';
            }
        }
        
        $response = [
            'status' => 'success',
            'restaurant' => null,
            'message' => $message
        ];
    }
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => '資料庫錯誤：' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    error_log("General error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => '系統錯誤：' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>
