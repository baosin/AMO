<?php
// 設置錯誤處理
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // 連接到 MySQL（不指定資料庫）
    $conn = new PDO(
        "mysql:host=localhost;charset=utf8mb4",
        "root",
        "",
        array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
    );

    // 讀取 SQL 檔案內容
    $sql = file_get_contents('database.sql');

    // 執行 SQL
    $conn->exec($sql);
    
    echo "資料庫和表格創建成功！\n";

} catch (PDOException $e) {
    die("資料庫錯誤: " . $e->getMessage() . "\n");
}
?>
