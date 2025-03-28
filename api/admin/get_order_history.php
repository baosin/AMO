<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../../config/database.php';

try {
    $db = getDBConnection();
    
    $stmt = $db->query("
        SELECT 
            h.*,
            old_m.name as old_menu_name,
            new_m.name as new_menu_name
        FROM order_history h
        LEFT JOIN menus old_m ON h.old_menu_id = old_m.id
        LEFT JOIN menus new_m ON h.new_menu_id = new_m.id
        ORDER BY h.modified_at DESC
    ");
    
    $history = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'status' => 'success',
        'history' => $history
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>
