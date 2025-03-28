<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');  // 使用 root 用戶
define('DB_PASS', '');      // XAMPP 預設 root 密碼為空
define('DB_NAME', 'amo_system');

function getDBConnection() {
    try {
        $conn = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
        );
        return $conn;
    } catch(PDOException $e) {
        throw new Exception("資料庫連接失敗: " . $e->getMessage());
    }
}
?>
