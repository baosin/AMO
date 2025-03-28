<?php
echo "<h2>🍱 AMO 點餐系統：啟動成功！</h2>";
require_once __DIR__ . "/config/database.php";
try {
    $db = getDBConnection();
    echo "<p>✅ SQLite 資料庫連線成功</p>";
} catch (Exception $e) {
    echo "<p>❌ 錯誤：" . $e->getMessage() . "</p>";
}
?>