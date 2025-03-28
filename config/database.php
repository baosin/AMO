<?php
function getDBConnection() {
    try {
        $conn = new PDO("sqlite:" . __DIR__ . "/../amo_system.db");
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch (PDOException $e) {
        throw new Exception("資料庫連線失敗: " . $e->getMessage());
    }
}
?>