<?php
require_once '../../config/database.php';

try {
    $db = getDBConnection();
    
    // 獲取日期參數
    $date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

    // 查詢訂單數據（新增分類 category 欄位）
    $stmt = $db->prepare("
        SELECT 
            o.user_name AS customer_name,
            m.name AS menu_name,
            COALESCE(mc.name, '未分類') AS category,
            o.rice_option,
            SUM(o.quantity) AS total_quantity,
            m.price,
            SUM(o.quantity * m.price) AS total_amount,
            o.note
        FROM orders o
        JOIN menus m ON o.menu_id = m.id
        LEFT JOIN menu_categories mc ON m.category_id = mc.id
        WHERE DATE(o.created_at) = :date
        GROUP BY o.user_name, m.name, mc.name, o.rice_option, m.price, o.note
        ORDER BY o.user_name, mc.name, m.name, o.rice_option
    ");
    
    $stmt->execute(['date' => $date]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 記錄每個品項的總數量（包含分類）
    $menuTotals = [];
    $userTotalAmount = [];
    $userOrderCount = [];
    foreach ($orders as $order) {
        // 建立品項顯示名稱，包含飯量和備註
        $displayName = trim($order['menu_name']);
        if ($order['rice_option']) {
            $displayName .= ' (' . trim($order['rice_option']) . ')';
        }
        if ($order['note']) {
            $displayName .= ' [' . trim($order['note']) . ']';
        }
        
        $category = trim($order['category']);

        if (!isset($menuTotals[$displayName])) {
            $menuTotals[$displayName] = ['quantity' => 0, 'category' => $category];
        }
        $menuTotals[$displayName]['quantity'] += $order['total_quantity'];

        // 計算使用者總金額
        if (!isset($userTotalAmount[$order['customer_name']])) {
            $userTotalAmount[$order['customer_name']] = 0;
        }
        $userTotalAmount[$order['customer_name']] += $order['total_amount'];

        // 記錄每個使用者的訂單筆數
        if (!isset($userOrderCount[$order['customer_name']])) {
            $userOrderCount[$order['customer_name']] = 0;
        }
        $userOrderCount[$order['customer_name']]++;
    }

    // 將品項按分類分組
    $menuByCategory = [];
    foreach ($menuTotals as $menu => $data) {
        $category = $data['category'] ?: '未分類';
        if (!isset($menuByCategory[$category])) {
            $menuByCategory[$category] = [];
        }
        $menuByCategory[$category][$menu] = $data['quantity'];
    }

    // 計算每個分類應該分配到哪一欄
    $totalCategories = count($menuByCategory);
    $leftColumnCategories = array_slice(array_keys($menuByCategory), 0, ceil($totalCategories / 2));
    $rightColumnCategories = array_slice(array_keys($menuByCategory), ceil($totalCategories / 2));

    // **HTML 表格樣式**
    $htmlContent = "<html><head><meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>訂單報表</title>
        <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css' rel='stylesheet'>
        <style>
            body {
                background-color: #f8f9fa;
                padding: 20px;
            }
            .container {
                background-color: white;
                border-radius: 8px;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                padding: 30px;
                margin-top: 20px;
            }
            @media print {
                body {
                    background-color: white;
                    padding: 0;
                }
                .container {
                    box-shadow: none;
                    padding: 0;
                }
                .no-print {
                    display: none !important;
                }
                .menu-container, .section-title:first-of-type {
                    display: none !important;
                }
                .table {
                    width: 100% !important;
                    margin: 0 !important;
                }
                .table th, .table td {
                    padding: 8px !important;
                    font-size: 14px !important;
                }
                .print-header {
                    margin-bottom: 20px !important;
                }
                .order-details {
                    margin-top: 0 !important;
                }
            }
            .table {
                font-size: 14px;
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 20px;
                background-color: white;
            }
            .table th, .table td {
                border: 1px solid #dee2e6;
                padding: 12px;
                text-align: center;
                vertical-align: middle;
            }
            .table th {
                background-color: #566D65 !important;
                color: white;
                font-weight: 500;
            }
            .print-header {
                margin-bottom: 30px;
                padding-bottom: 20px;
                border-bottom: 2px solid #dee2e6;
                text-align: center;
            }
            .print-header h2 {
                color: #566D65;
                margin-bottom: 10px;
            }
            .menu-container {
                display: flex;
                justify-content: space-between;
                gap: 30px;
                margin-bottom: 40px;
            }
            .menu-column {
                flex: 1;
                background-color: white;
                border-radius: 8px;
                overflow: hidden;
            }
            .menu-column table {
                width: 100%;
                margin-bottom: 0;
            }
            .category-header {
                background-color: #f8f9fa !important;
                font-weight: bold;
                color: #566D65;
            }
            .category-header td {
                padding: 10px !important;
            }
            .order-details {
                margin-top: 40px;
                background-color: white;
                border-radius: 8px;
                overflow: hidden;
            }
            .section-title {
                font-size: 1.3em;
                margin-bottom: 20px;
                color: #566D65;
                font-weight: 500;
                padding-bottom: 10px;
                border-bottom: 2px solid #dee2e6;
            }
            .btn-print {
                background-color: #566D65;
                color: white;
                border: none;
                padding: 10px 20px;
                border-radius: 5px;
                cursor: pointer;
                transition: background-color 0.3s;
            }
            .btn-print:hover {
                background-color: #455a54;
                color: white;
            }
            .print-date {
                color: #6c757d;
                font-size: 1.1em;
            }
            .total-row td {
                font-weight: bold;
                background-color: #f8f9fa;
            }
        </style>
    </head><body>";

    $htmlContent .= "<div class='container'>";
    $htmlContent .= "<div class='no-print mb-4'>";
    $htmlContent .= "<button onclick='window.print()' class='btn btn-print'>";
    $htmlContent .= "<i class='bi bi-printer'></i> 列印報表";
    $htmlContent .= "</button>";
    $htmlContent .= "</div>";
    
    $htmlContent .= "<div class='print-header'>";
    $htmlContent .= "<h2>訂單報表</h2>";
    $htmlContent .= "<div class='print-date'>" . date('Y年m月d日', strtotime($date)) . "</div>";
    $htmlContent .= "</div>";

    // 品項數量統計
    $htmlContent .= "<div class='section-title'>品項數量統計</div>";
    $htmlContent .= "<div class='menu-container'>";
    
    // 左欄
    $htmlContent .= "<div class='menu-column'><table class='table table-bordered'>
        <thead>
            <tr><th colspan='2'>品項數量總計</th></tr>
            <tr><th>品項</th><th width='80'>數量</th></tr>
        </thead>
        <tbody>";
    foreach ($leftColumnCategories as $category) {
        $htmlContent .= "<tr class='category-header'><td colspan='2'>{$category}</td></tr>";
        foreach ($menuByCategory[$category] as $menu => $quantity) {
            $htmlContent .= "<tr><td>{$menu}</td><td>{$quantity}</td></tr>";
        }
    }
    $htmlContent .= "</tbody></table></div>";

    // 右欄
    $htmlContent .= "<div class='menu-column'><table class='table table-bordered'>
        <thead>
            <tr><th colspan='2'>品項數量總計</th></tr>
            <tr><th>品項</th><th width='80'>數量</th></tr>
        </thead>
        <tbody>";
    foreach ($rightColumnCategories as $category) {
        $htmlContent .= "<tr class='category-header'><td colspan='2'>{$category}</td></tr>";
        foreach ($menuByCategory[$category] as $menu => $quantity) {
            $htmlContent .= "<tr><td>{$menu}</td><td>{$quantity}</td></tr>";
        }
    }
    $htmlContent .= "</tbody></table></div>";
    
    $htmlContent .= "</div>";

    // 訂單詳細資料
    $htmlContent .= "<div class='order-details'>";
    $htmlContent .= "<div class='section-title'>訂單詳細資料</div>";
    $htmlContent .= "<table class='table table-bordered'>
        <thead>
            <tr>
                <th width='100'>姓名</th>
                <th>品項</th>
                <th>分類</th>
                <th width='80'>數量</th>
                <th width='80'>單價</th>
                <th width='100'>總額</th>
                <th width='80'>飯量</th>
                <th>備註</th>
            </tr>
        </thead>
        <tbody>";

    $previousUser = "";
    $totalAmount = 0;
    foreach ($orders as $order) {
        $htmlContent .= "<tr>";

        if ($order['customer_name'] !== $previousUser) {
            $htmlContent .= "<td rowspan='{$userOrderCount[$order['customer_name']]}'>{$order['customer_name']}</td>";
            $htmlContent .= "<td>{$order['menu_name']}</td>";
            $htmlContent .= "<td>{$order['category']}</td>";
            $htmlContent .= "<td>{$order['total_quantity']}</td>";
            $htmlContent .= "<td>{$order['price']}</td>";
            $htmlContent .= "<td rowspan='{$userOrderCount[$order['customer_name']]}'>{$userTotalAmount[$order['customer_name']]}</td>";
            $htmlContent .= "<td>{$order['rice_option']}</td>";
            $htmlContent .= "<td>" . ($order['note'] ? htmlspecialchars($order['note']) : '-') . "</td>";
            $previousUser = $order['customer_name'];
            $totalAmount += $userTotalAmount[$order['customer_name']];
        } else {
            $htmlContent .= "<td>{$order['menu_name']}</td>";
            $htmlContent .= "<td>{$order['category']}</td>";
            $htmlContent .= "<td>{$order['total_quantity']}</td>";
            $htmlContent .= "<td>{$order['price']}</td>";
            $htmlContent .= "<td>{$order['rice_option']}</td>";
            $htmlContent .= "<td>" . ($order['note'] ? htmlspecialchars($order['note']) : '-') . "</td>";
        }

        $htmlContent .= "</tr>";
    }
    
    // 添加總計行
    $htmlContent .= "<tr class='total-row'>
        <td colspan='5' class='text-end'>總計金額：</td>
        <td colspan='3'>{$totalAmount}</td>
    </tr>";
    
    $htmlContent .= "</tbody></table>";
    $htmlContent .= "</div>";

    $htmlContent .= "</div></body></html>";

    echo $htmlContent;

} catch (Exception $e) {
    echo "<p>錯誤: " . $e->getMessage() . "</p>";
}
?>