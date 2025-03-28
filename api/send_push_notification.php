<?php
header('Content-Type: application/json');
require_once '../../config/database.php';
require_once 'vendor/autoload.php';

use Minishlink\WebPush\WebPush;

try {
    $db = getDBConnection();
    
    // 獲取所有訂閱
    $stmt = $db->query("SELECT * FROM push_subscriptions");
    $subscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($subscriptions)) {
        throw new Exception('沒有找到推送訂閱');
    }
    
    // 準備推送數據
    $data = json_decode(file_get_contents('php://input'), true);
    $payload = json_encode([
        'title' => $data['title'],
        'body' => $data['message'],
        'icon' => $data['icon']
    ]);
    
    // 初始化 WebPush
    $auth = [
        'VAPID' => [
            'subject' => 'mailto:your-email@example.com',
            'publicKey' => 'YOUR_VAPID_PUBLIC_KEY',
            'privateKey' => 'YOUR_VAPID_PRIVATE_KEY'
        ]
    ];
    
    $webPush = new WebPush($auth);
    
    // 發送推送
    $reports = $webPush->sendOneNotification(
        $subscriptions[0],
        $payload
    );
    
    // 處理推送結果
    foreach ($reports as $report) {
        if ($report->isSuccess()) {
            echo json_encode([
                'status' => 'success',
                'message' => '推送通知已發送'
            ]);
        } else {
            // 如果訂閱已過期，從數據庫中刪除
            if ($report->isSubscriptionExpired()) {
                $stmt = $db->prepare("DELETE FROM push_subscriptions WHERE endpoint = ?");
                $stmt->execute([$report->getEndpoint()]);
            }
            
            throw new Exception('推送通知發送失敗：' . $report->getReason());
        }
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
} 