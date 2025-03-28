<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>點餐吧!</title>
    <link rel="manifest" href="/AMO/manifest.json">
    <meta name="theme-color" content="#000000">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="apple-mobile-web-app-title" content="AMO System">
    <link rel="apple-touch-icon" href="/AMO/assets/icons/icon-192x192.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            padding-top: 56px;
        }
        .btn-theme {
            background-color: #CA5851;
            color: #ffffff;
            border: none;
            transition: all 0.3s ease;
        }
        .btn-theme:hover {
            background-color: #b54c45;
            color: #ffffff;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(202, 88, 81, 0.2);
        }
        .btn-theme-outline {
            background-color: transparent;
            color: #ffffff;
            border: 1px solid #ffffff;
            transition: all 0.3s ease;
        }
        .btn-theme-outline:hover {
            background-color: rgba(255, 255, 255, 0.1);
            color: #ffffff;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(255, 255, 255, 0.1);
        }
        .btn-theme-outline.dark {
            color: #CA5851;
            border-color: #CA5851;
        }
        .btn-theme-outline.dark:hover {
            background-color: rgba(202, 88, 81, 0.1);
            color: #b54c45;
        }
        .navbar {
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1030;
            background: #CA5851;
            box-shadow: 0 2px 8px rgba(255, 107, 107, 0.3);
        }
        .navbar-brand {
            color: #ffffff !important;
            font-weight: 500;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
        }
        .btn-outline-light:hover {
            background-color: rgba(255,255,255,0.2);
            border-color: #ffffff;
        }
        .content {
            padding: 20px;
        }
        /* 菜單項目卡片樣式 */
        .menu-item-card {
            border: 1px solid #e9ecef;
            padding: 15px;
            margin-bottom: 12px;
            border-radius: 8px;
            transition: all 0.2s ease;
            background-color: #fff;
        }
        .menu-item-card:hover {
            background-color: #f8f9fa;
            transform: translateY(-1px);
            box-shadow: 0 3px 6px rgba(0,0,0,0.08);
        }
        /* 選擇框組樣式 */
        .menu-item-card .input-group {
            margin-top: 8px;
        }
        .menu-item-card .form-select {
            border-color: #dee2e6;
            background-color: #fff;
            font-size: 0.875rem;
            min-width: 110px;
        }
        .menu-item-card .form-select:focus {
            border-color: #86b7fe;
            box-shadow: 0 0 0 0.25rem rgba(13,110,253,.25);
        }
        /* 按鈕樣式 */
        .menu-item-card .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
        .menu-item-card .btn-outline-primary:hover {
            background-color: #0d6efd;
            color: #fff;
        }
        /* 文字樣式 */
        .menu-item-card h6 {
            color: #212529;
            font-size: 1rem;
            margin-bottom: 4px;
        }
        .menu-item-card .text-muted {
            font-size: 0.813rem;
            color: #6c757d;
        }
        .menu-item-card .text-primary {
            font-weight: 500;
            font-size: 0.938rem;
        }
        /* 選項布局 */
        .menu-item-card .d-flex {
            gap: 8px;
            align-items: center;
        }
        .menu-item-card .input-group-sm > .form-select,
        .menu-item-card .input-group-sm > .btn {
            border-radius: 4px;
        }
        /* 分類卡片樣式 */
        .category-card {
            margin-bottom: 24px;
            border: none;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .category-header {
            background-color: #f8f9fa;
            border-bottom: 2px solid #e9ecef;
            padding: 12px 16px;
        }
        .category-header h5 {
            margin: 0;
            color: #495057;
            font-size: 1.1rem;
            font-weight: 500;
        }
        /* 訂單動態相關樣式 */
        .order-feed-modal .modal-body {
            max-height: 70vh;
            overflow-y: auto;
        }
        .order-feed-item {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        .order-feed-item:last-child {
            border-bottom: none;
        }
        .notification-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }
        /* 訂單卡片樣式 */
        #orderItems .card {
            border: 1px solid #e9ecef;
            margin-bottom: 10px;
            border-radius: 6px;
        }
        #orderItems .card-body {
            padding: 12px;
        }
        #orderItems h6 {
            font-size: 1rem;
            margin-bottom: 4px;
            color: #212529;
        }
        #orderItems .text-muted {
            font-size: 0.813rem;
            color: #6c757d;
            display: block;
            margin-bottom: 8px;
        }
        #orderItems .input-group-sm {
            margin-top: 8px;
        }
        #orderItems .form-control {
            font-size: 0.875rem;
            border-color: #dee2e6;
        }
        #orderItems .btn-outline-danger {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
        /* 分類和飯量標籤 */
        .item-options {
            display: inline-flex;
            gap: 8px;
            align-items: center;
            font-size: 0.813rem;
            color: #6c757d;
        }
        .item-options span {
            padding: 2px 6px;
            background-color: #f8f9fa;
            border-radius: 4px;
            border: 1px solid #e9ecef;
        }
        /* 數量調整按鈕組 */
        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .quantity-controls .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
        .quantity-controls input {
            width: 50px;
            text-align: center;
            font-size: 0.875rem;
        }
        /* 分類導覽列樣式 */
        .category-nav {
            position: sticky;
            top: 76px;
            max-height: calc(100vh - 96px);
            overflow-y: auto;
            padding: 15px;
            background-color: #fff;
            border: 1px solid #dee2e6;
            border-radius: 8px;
        }
        .category-nav .nav-link {
            color: #495057;
            padding: 8px 12px;
            transition: all 0.2s ease;
            font-size: 0.95rem;
            color: #0d6efd;
        }
        .category-nav .nav-link:hover {
            color: #0d6efd;
            transform: translateX(3px);
        }
        .category-nav .nav-link.active {
            color: #0d6efd;
            font-weight: 500;
        }
        .category-nav h5 {
            color: #212529;
            font-size: 1.2rem;
            padding: 0 12px;
            margin-bottom: 15px;
        }
        /* 分類區塊樣式 */
        .category-title {
            padding: 30px 0 15px;
            position: sticky;
            top: 76px;
            background-color: #fff;
        }
        .category-title h5 {
            margin: 0;
            color: #212529;
            font-size: 1.2rem;
        }
        .category-section {
            padding: 0 0 30px;
        }
        /* 菜單項目卡片樣式 */
        .menu-item-card {
            border: 1px solid #dee2e6;
            padding: 15px;
            margin-bottom: 12px;
            border-radius: 8px;
            background-color: #fff;
            transition: all 0.2s ease;
        }
        .menu-item-card:hover {
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .menu-item-card h6 {
            font-size: 1rem;
            color: #212529;
        }
        .menu-item-card .text-primary {
            font-size: 1rem;
            font-weight: 500;
        }
        /* 適配手機版面 */
        @media (max-width: 767.98px) {
            .category-nav {
                position: relative;
                top: 0;
                max-height: none;
                margin-bottom: 20px;
                padding: 15px;
            }
            .category-title {
                top: 56px;
                padding: 20px 0 10px;
            }
        }
        #restaurantImage {
            height: 300px;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-color: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #666;
            font-size: 1rem;
            margin-bottom: 20px;
            border-radius: 8px;
            overflow: hidden;
        }
        .quantity-input input {
            width: 60px;
        }
        .note-input {
            resize: none;
        }
        /* 訂單卡片樣式 */
        #orderItems .card {
            border: 1px solid #e9ecef;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="container-fluid" >
            <span class="navbar-brand" style="cursor: pointer;" onclick="scrollToTop()">點餐吧!</span>
            <div class="d-flex gap-3">
                <button type="button" class="btn btn-theme-outline d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#orderModal">
                    <i class="bi bi-cart-fill me-2"></i>我的訂單
                </button>
                <button type="button" class="btn btn-theme-outline d-flex align-items-center position-relative" data-bs-toggle="modal" data-bs-target="#orderFeedModal">
                    <i class="bi bi-bell-fill me-2"></i>最新訂單
                    <span class="badge bg-danger notification-badge" id="newOrderCount" style="display: none;">0</span>
                </button>
            </div>
        </div>
    </nav>

    <!-- 訂單動態 Modal -->
    <div class="modal fade order-feed-modal" id="orderFeedModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">最新訂單動態</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="orderFeedContent">
                        <!-- 訂單動態消息會在這裡動態添加 -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 管理者消息 Modal -->
    <div class="modal fade" id="adminMessageModal" tabindex="-1" aria-labelledby="adminMessageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="adminMessageModalLabel">系統公告</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="adminMessageContent" style="white-space: pre-wrap;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-theme" data-bs-dismiss="modal">我知道了</button>
                </div>
            </div>
        </div>
    </div>

    <!-- 訂單 Modal -->
    <div class="modal fade" id="orderModal" tabindex="-1" aria-labelledby="orderModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="orderModalLabel">我的訂單</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="orderForm">
                        <div class="form-group mb-3">
                            <label for="userName">訂餐人姓名 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="userName" required>
                        </div>
                        <div id="orderItems" class="mb-3">
                            <!-- 訂單項目將由 JavaScript 動態添加 -->
                        </div>
                        <div class="modal-footer justify-content-between">
                            <div>
                                總金額：<span id="totalAmount" class="h5 mb-0">$0</span>
                            </div>
                            <div>
                                <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">繼續點餐</button>
                                <button type="button" class="btn btn-theme" onclick="submitOrder()">送出訂單</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="container content">
        <!-- 餐廳資訊 -->
        <div id="restaurantInfo">
            <div id="restaurantImage">
                <span>尚未選擇店家</span>
            </div>
            <div class="text-center mb-4">
                <h2 id="restaurantName" class="mb-2">載入中...</h2>
                <p id="orderDeadline" class="text-muted mb-0"></p>
            </div>
        </div>

        <!-- 主要內容區域 -->
        <div class="row">
            <!-- 左側分類導覽 -->
            <div class="col-md-3">
                <div class="category-nav" id="categoryNav">
                    <h5 class="mb-3">分類導覽</h5>
                    <nav class="nav flex-column" id="categoryNavLinks">
                        <!-- 分類連結將由 JavaScript 動態載入 -->
                    </nav>
                </div>
            </div>
            
            <!-- 中間菜單 -->
            <div class="col-md-6">
                <div id="menuContainer">
                    <!-- 菜單內容將由 JavaScript 動態載入 -->
                </div>
            </div>

            <!-- 右側訂單 -->
            <div class="col-md-3">
                <!-- 訂單內容已經移到彈出視窗 -->
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/main.js"></script>
    <script src="/AMO/js/pwa.js"></script>
    <script>
        function scrollToTop() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }
    </script>
</body>
</html>