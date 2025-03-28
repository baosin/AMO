<?php
require_once '../vendor/autoload.php';
require_once '../config/database.php';

use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

function sendPushNotification($message) {
    $db = getDBConnection();
    
    // 獲取所有訂閱
    $stmt = $db->query("SELECT * FROM push_subscriptions");
    $subscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $auth = array(
        'VAPID' => array(
            'subject' => 'mailto:your@email.com', // 您的聯絡 email
            'publicKey' => 'YOUR_PUBLIC_VAPID_KEY', // 您的 VAPID public key
            'privateKey' => 'YOUR_PRIVATE_VAPID_KEY', // 您的 VAPID private key
        ),
    );

    $webPush = new WebPush($auth);

    foreach ($subscriptions as $subscription) {
        $sub = Subscription::create([
            'endpoint' => $subscription['endpoint'],
            'keys' => [
                'p256dh' => $subscription['p256dh_key'],
                'auth' => $subscription['auth_key']
            ]
        ]);

        $webPush->sendNotification($sub, $message);
    }

    // 發送所有通知
    foreach ($webPush->flush() as $report) {
        $endpoint = $report->getRequest()->getUri()->__toString();
        
        // 如果推送失敗，刪除該訂閱
        if (!$report->isSuccess()) {
            $stmt = $db->prepare("DELETE FROM push_subscriptions WHERE endpoint = ?");
            $stmt->execute([$endpoint]);
        }
    }
} 