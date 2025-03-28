<?php
require_once '../../config/database.php';

// 獲取指定日期，如果沒有提供則使用今天
$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

// 設置輸出 Excel 檔案的 header
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="訂單統計_' . $date . '.xls"');
header('Cache-Control: max-age=0');
header('Content-Type: text/html; charset=utf-8');

try {
    $db = getDBConnection();
    
    // 獲取指定日期的訂單
    $stmt = $db->prepare("
        SELECT 
            o.id as order_id,
            o.user_name,
            m.name as menu_name,
            o.quantity,
            m.price,
            o.rice_option,
            o.note,
            (o.quantity * m.price) as subtotal,
            DATE_FORMAT(o.created_at, '%H:%i') as order_time,
            r.name as restaurant_name
        FROM orders o
        JOIN menus m ON o.menu_id = m.id
        JOIN restaurants r ON m.restaurant_id = r.id
        WHERE DATE(o.created_at) = :date
        ORDER BY o.created_at DESC
    ");
    
    $stmt->bindParam(':date', $date);
    $stmt->execute();
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($orders)) {
        die("該日期沒有訂單記錄");
    }

    // 計算品項統計
    $menuStats = [];
    foreach ($orders as $order) {
        $menuName = $order['menu_name'];
        if (!isset($menuStats[$menuName])) {
            $menuStats[$menuName] = [
                'name' => $menuName,
                'count' => 0,
                'total_quantity' => 0,
                'price' => $order['price'],
                'total_amount' => 0
            ];
        }
        $menuStats[$menuName]['count']++;
        $menuStats[$menuName]['total_quantity'] += $order['quantity'];
        $menuStats[$menuName]['total_amount'] += $order['subtotal'];
    }

    // 輸出 BOM 標記，確保 Excel 正確識別 UTF-8 編碼
    echo chr(0xEF) . chr(0xBB) . chr(0xBF);
    
    // 開始輸出 Excel 內容
    echo '<?xml version="1.0" encoding="UTF-8"?>
    <?mso-application progid="Excel.Sheet"?>
    <Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
     xmlns:o="urn:schemas-microsoft-com:office:office"
     xmlns:x="urn:schemas-microsoft-com:office:excel"
     xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">
     <DocumentProperties xmlns="urn:schemas-microsoft-com:office:office">
      <Version>12.00</Version>
     </DocumentProperties>
     <Styles>
      <Style ss:ID="Default" ss:Name="Normal">
       <Alignment ss:Vertical="Center"/>
       <Font ss:FontName="新細明體" ss:Size="12"/>
      </Style>
      <Style ss:ID="Header">
       <Font ss:FontName="新細明體" ss:Size="12" ss:Bold="1"/>
       <Interior ss:Color="#CCCCCC" ss:Pattern="Solid"/>
      </Style>
      <Style ss:ID="Title">
       <Font ss:FontName="新細明體" ss:Size="14" ss:Bold="1"/>
       <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
      </Style>
     </Styles>
     <Worksheet ss:Name="訂單統計">
      <Table ss:DefaultColumnWidth="100">
       <Column ss:Width="150"/>
       <Column ss:Width="80"/>
       <Column ss:Width="80"/>
       <Column ss:Width="80"/>
       <Column ss:Width="100"/>';

    // 輸出品項統計標題
    echo '<Row>
        <Cell ss:StyleID="Title" ss:MergeAcross="4"><Data ss:Type="String">品項統計表</Data></Cell>
    </Row>
    <Row>
        <Cell ss:StyleID="Header"><Data ss:Type="String">品項名稱</Data></Cell>
        <Cell ss:StyleID="Header"><Data ss:Type="String">訂單數</Data></Cell>
        <Cell ss:StyleID="Header"><Data ss:Type="String">總數量</Data></Cell>
        <Cell ss:StyleID="Header"><Data ss:Type="String">單價</Data></Cell>
        <Cell ss:StyleID="Header"><Data ss:Type="String">總金額</Data></Cell>
    </Row>';

    // 輸出品項統計資料
    $totalAmount = 0;
    foreach ($menuStats as $stat) {
        echo '<Row>
            <Cell><Data ss:Type="String">' . htmlspecialchars($stat['name']) . '</Data></Cell>
            <Cell><Data ss:Type="Number">' . $stat['count'] . '</Data></Cell>
            <Cell><Data ss:Type="Number">' . $stat['total_quantity'] . '</Data></Cell>
            <Cell><Data ss:Type="Number">' . $stat['price'] . '</Data></Cell>
            <Cell><Data ss:Type="Number">' . $stat['total_amount'] . '</Data></Cell>
        </Row>';
        $totalAmount += $stat['total_amount'];
    }

    // 輸出總計
    echo '<Row>
        <Cell ss:StyleID="Header"><Data ss:Type="String">總計</Data></Cell>
        <Cell><Data ss:Type="String"></Data></Cell>
        <Cell><Data ss:Type="String"></Data></Cell>
        <Cell><Data ss:Type="String"></Data></Cell>
        <Cell ss:StyleID="Header"><Data ss:Type="Number">' . $totalAmount . '</Data></Cell>
    </Row>';

    // 空白行
    echo '<Row></Row><Row></Row>';

    // 輸出詳細訂單標題
    echo '<Row>
        <Cell ss:StyleID="Title" ss:MergeAcross="9"><Data ss:Type="String">詳細訂單列表</Data></Cell>
    </Row>
    <Row>
        <Cell ss:StyleID="Header"><Data ss:Type="String">訂單編號</Data></Cell>
        <Cell ss:StyleID="Header"><Data ss:Type="String">訂餐時間</Data></Cell>
        <Cell ss:StyleID="Header"><Data ss:Type="String">姓名</Data></Cell>
        <Cell ss:StyleID="Header"><Data ss:Type="String">店家</Data></Cell>
        <Cell ss:StyleID="Header"><Data ss:Type="String">品項</Data></Cell>
        <Cell ss:StyleID="Header"><Data ss:Type="String">數量</Data></Cell>
        <Cell ss:StyleID="Header"><Data ss:Type="String">單價</Data></Cell>
        <Cell ss:StyleID="Header"><Data ss:Type="String">總額</Data></Cell>
        <Cell ss:StyleID="Header"><Data ss:Type="String">白飯</Data></Cell>
        <Cell ss:StyleID="Header"><Data ss:Type="String">備註</Data></Cell>
    </Row>';

    // 輸出詳細訂單資料
    foreach ($orders as $order) {
        echo '<Row>
            <Cell><Data ss:Type="Number">' . $order['order_id'] . '</Data></Cell>
            <Cell><Data ss:Type="String">' . $order['order_time'] . '</Data></Cell>
            <Cell><Data ss:Type="String">' . htmlspecialchars($order['user_name']) . '</Data></Cell>
            <Cell><Data ss:Type="String">' . htmlspecialchars($order['restaurant_name']) . '</Data></Cell>
            <Cell><Data ss:Type="String">' . htmlspecialchars($order['menu_name']) . '</Data></Cell>
            <Cell><Data ss:Type="Number">' . $order['quantity'] . '</Data></Cell>
            <Cell><Data ss:Type="Number">' . $order['price'] . '</Data></Cell>
            <Cell><Data ss:Type="Number">' . $order['subtotal'] . '</Data></Cell>
            <Cell><Data ss:Type="String">' . htmlspecialchars($order['rice_option']) . '</Data></Cell>
            <Cell><Data ss:Type="String">' . htmlspecialchars($order['note']) . '</Data></Cell>
        </Row>';
    }

    // 結束 Excel 文件
    echo '</Table></Worksheet></Workbook>';

} catch (Exception $e) {
    die("匯出失敗：" . $e->getMessage());
}
