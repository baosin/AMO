<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>點餐系統管理後台</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        
        .content {
            padding: 20px;
        }
        #menuList {
            margin-top: 20px;
        }
        .menu-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        .restaurant-header {
            background-color: #f8f9fa;
            transition: background-color 0.2s;
        }
        .restaurant-header:hover {
            background-color: #e9ecef;
        }
        .toggle-icon {
            display: inline-block;
            margin-left: 10px;
            transition: transform 0.2s;
        }
        .restaurant-orders {
            transition: all 0.3s ease-in-out;
        }
        .badge {
            font-size: 0.9em;
        }

        /* 導航欄樣式 */
        .navbar {
            background: #566D65 !important;
            box-shadow: 0 2px 8px rgba(74, 105, 221, 0.3);
        }
        .navbar-brand {
            color: #ffffff !important;
            font-weight: 500;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
        }
        .navbar .nav-link {
            color: rgba(255,255,255,.8) !important;
            transition: color 0.2s ease;
        }
        .navbar .nav-link:hover,
        .navbar .nav-link.active {
            color: #ffffff !important;
        }
        .navbar-toggler {
            border-color: rgba(255,255,255,.5) !important;
        }
        .navbar-toggler-icon {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba%28255, 255, 255, 0.8%29' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e") !important;
        }
        
        /* 圖片瀏覽相關樣式 */
        .image-preview-item {
            cursor: pointer;
            transition: transform 0.2s;
        }
        .image-preview-item:hover {
            transform: scale(1.05);
        }
        .image-viewer-modal .modal-dialog {
            max-width: 800px;
        }
        .image-viewer-modal .carousel-item img {
            max-height: 70vh;
            width: auto;
            margin: 0 auto;
            object-fit: contain;
        }
        /* 修改導航按鈕樣式 */
        .image-viewer-modal .carousel-control-prev,
        .image-viewer-modal .carousel-control-next {
            width: 10%;
            opacity: 0.8;
            background: rgba(0, 0, 0, 0.5);
        }
        .image-viewer-modal .carousel-control-prev:hover,
        .image-viewer-modal .carousel-control-next:hover {
            opacity: 1;
            background: rgba(0, 0, 0, 0.7);
        }
        .image-viewer-modal .carousel-control-prev-icon,
        .image-viewer-modal .carousel-control-next-icon {
            width: 40px;
            height: 40px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg" style="background-color: #4a69dd;">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">點餐系統管理</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link active" href="#restaurantsPage" data-page="restaurants">店家管理</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#ordersPage" data-page="orders">訂單管理</a>
                    </li>
                </ul>
            </div>
        </div>

    </nav>

    <div class="container content">
        <div class="container-fluid">
            <!-- 店家管理頁面 -->
            <div id="restaurantsPage">
                <div class="row">
                    <!-- 左側店家列表 -->
                    <div class="col-md-8">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h2>店家管理</h2>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRestaurantModal">
                                新增店家
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>店家圖片</th>
                                        <th>店家名稱</th>
                                        <th>電話</th>
                                        <th>狀態</th>
                                        <th>操作</th>
                                    </tr>
                                </thead>
                                <tbody id="restaurantsList">
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- 右側設定面板 -->
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">系統設定</h5>
                            </div>
                            <div class="card-body">
                                <form id="settingsForm">
                                    <div class="mb-3">
                                        <label for="selectedRestaurant" class="form-label">今日店家</label>
                                        <select class="form-select" id="selectedRestaurant" required>
                                            <option value="">請選擇店家</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="orderDeadline" class="form-label">截止時間</label>
                                        <input type="datetime-local" class="form-control" id="orderDeadline" required>
                                    </div>
                                    <div class="mb-3">
                                        <div class="form-check form-switch">
                                            <input type="checkbox" class="form-check-input" id="isOrderingActive">
                                            <label class="form-check-label" for="isOrderingActive">開放訂餐</label>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary">儲存設定</button>
                                </form>
                            </div>
                        </div>
                        <BR>
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">系統公告設置</h5>
                            </div>
                            <div class="card-body">
                                <form id="adminMessageForm" onsubmit="return false;">
                                    <div class="mb-3">
                                        <label for="adminMessage" class="form-label">公告內容</label>
                                        <textarea class="form-control" id="adminMessage" rows="5" 
                                            style="white-space: pre-wrap;"
                                            placeholder="輸入要顯示給用戶的公告訊息&#13;&#10;可以使用 Enter 鍵換行"></textarea>
                                        <div class="form-text">此訊息將在用戶進入網站時顯示</div>
                                    </div>
                                    <button type="button" class="btn btn-primary" onclick="saveAdminMessage()">發布公告</button>
                                </form>
                                <hr>
                                <h6 class="mb-3">歷史公告</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th style="width: 60%">內容</th>
                                                <th style="width: 25%">發布時間</th>
                                                <th style="width: 15%">操作</th>
                                            </tr>
                                        </thead>
                                        <tbody id="adminMessagesList">
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 訂單管理頁面 -->
            <div id="ordersPage" style="display: none;">
                <div class="row">
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h2>訂單管理</h2>
                            <div>
                                <button class="btn btn-secondary" onclick="openNewWindow()">表格</button>

                                <button type="button" class="btn btn-danger" onclick="closeOrders()">
                                    收單
                                </button>
                            </div>
                        </div>
                        
                        <!-- 訂單查詢區域 -->
                        <div class="card mb-4">
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">店家</label>
                                        <select class="form-select" id="orderRestaurantFilter">
                                            <option value="">全部店家</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">日期</label>
                                        <input type="date" class="form-control" id="orderDateFilter">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">&nbsp;</label>
                                        <button class="btn btn-primary d-block w-100" onclick="loadOrdersList()">查詢</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 訂單列表區域 -->
                        <div id="ordersList">
                            <!-- 訂單摘要和詳細資訊將由 JavaScript 填充 -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <!-- 新增店家 Modal -->
    <div class="modal fade" id="addRestaurantModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">新增店家</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addRestaurantForm">
                        <div class="mb-3">
                            <label for="restaurantName" class="form-label">店家名稱</label>
                            <input type="text" class="form-control" id="restaurantName" required>
                        </div>
                        <div class="mb-3">
                            <label for="restaurantPhone" class="form-label">聯絡電話</label>
                            <input type="text" class="form-control" id="restaurantPhone">
                        </div>
                        <div class="mb-3">
                            <label for="restaurantImage" class="form-label">店家圖片</label>
                            <input type="file" class="form-control" id="restaurantImage" accept="image/*">
                            <div class="form-text">建議上傳寬高比 16:9 的圖片</div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                            <button type="submit" class="btn btn-primary">新增</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

                    <!-- 菜單管理 Modal -->
                    <div class="modal fade" id="menuModal" tabindex="-1">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">菜單管理</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <!-- 分類管理（風琴式） -->
                                    <div class="accordion mb-4" id="categoryAccordion">
                                        <!-- 分類列表將由 JavaScript 動態填充 -->
                                    </div>
                                    <div class="text-end mb-4">
                                        <button type="button" class="btn btn-primary" onclick="showAddCategoryModal()">
                                            <i class="bi bi-plus-lg"></i> 新增分類
                                        </button>
                                    </div>

                                    <!-- 菜單項目列表 -->
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>品項名稱</th>
                                                    <th>價格</th>
                                                    <th>分類</th>
                                                    <th>操作</th>
                                                </tr>
                                            </thead>
                                            <tbody id="menuItemList">
                                                <!-- 菜單項目將由 JavaScript 動態填充 -->
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="text-end mt-3">
                                        <button type="button" class="btn btn-primary" onclick="showAddMenuItemModal()">
                                            <i class="bi bi-plus-lg"></i> 新增品項
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 新增分類 Modal -->
                    <div class="modal fade" id="addCategoryModal" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">新增分類</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <form id="addCategoryForm">
                                        <div class="mb-3">
                                            <label for="categoryName" class="form-label">分類名稱</label>
                                            <input type="text" class="form-control" id="categoryName" required>
                                        </div>
                                    </form>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                                    <button type="button" class="btn btn-primary" onclick="addCategory()">新增</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 新增菜單項目 Modal -->
                    <div class="modal fade" id="addMenuItemModal" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">新增菜單項目</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <form id="addMenuItemForm">
                                        <div class="mb-3">
                                            <label for="itemName" class="form-label">品項名稱</label>
                                            <input type="text" class="form-control" id="itemName" name="name" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="itemPrice" class="form-label">價格</label>
                                            <input type="number" class="form-control" id="itemPrice" name="price" required min="0">
                                        </div>
                                    </form>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                                    <button type="button" class="btn btn-primary" onclick="addMenuItem()">新增</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 編輯店家 Modal -->
                    <div class="modal fade" id="editRestaurantModal" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">編輯店家資訊</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <form id="editRestaurantForm" enctype="multipart/form-data">
                                        <input type="hidden" id="editRestaurantId">
                                        <div class="mb-3">
                                            <label for="editRestaurantName" class="form-label">店家名稱</label>
                                            <input type="text" class="form-control" id="editRestaurantName" required>
                                        </div>
                                        <div class="mb-3">
                                        <label for="editRestaurantPhone" class="form-label">電話</label>
                                            <input type="text" class="form-control" id="editRestaurantPhone">
                                        </div>
                                        <div class="mb-3">
                                            <label for="editRestaurantImage" class="form-label">店家圖片</label>
                                            <input type="file" class="form-control" id="editRestaurantImage" accept="image/*" multiple>
                                            <div class="form-text">可以選擇多張圖片，建議上傳寬高比 16:9 的圖片</div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">目前圖片</label>
                                            <div id="currentImagePreview" class="d-flex flex-wrap gap-2">
                                                <!-- 圖片預覽將由 JavaScript 動態填充 -->
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                                            <button type="submit" class="btn btn-primary">儲存</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                        <!-- 編輯訂單 Modal -->
    <div class="modal fade" id="editOrderModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">編輯訂單</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editOrderForm">
                        <div class="mb-3">
                            <label for="editUserName" class="form-label">訂餐人</label>
                            <input type="text" class="form-control" id="editUserName" required>
                        </div>
                        <div class="mb-3">
                            <label for="editMenuSelect" class="form-label">品項</label>
                            <select class="form-select" id="editMenuSelect" required>
                                <!-- 選項將由 JavaScript 動態填充 -->
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="editQuantity" class="form-label">數量</label>
                            <input type="number" class="form-control" id="editQuantity" min="1" required>
                        </div>
                        <div class="mb-3">
                            <label for="editNote" class="form-label">備註</label>
                            <input type="text" class="form-control" id="editNote">
                        </div>
                        <div class="mb-3" id="editRiceOptionContainer" style="display: none;">
                            <label for="editRiceOption" class="form-label">飯量</label>
                            <select class="form-select" id="editRiceOption">
                                <option value="">正常飯量</option>
                                <option value="半飯">半飯</option>
                                <option value="多飯">多飯</option>
                                <option value="不要飯">不要飯</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                    <button type="button" class="btn btn-primary" onclick="saveOrderEdit()">儲存</button>
                </div>
            </div>
        </div>
    </div>

    <!-- 圖片瀏覽 Modal -->
    <div class="modal fade image-viewer-modal" id="imageViewerModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-dark">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title text-light">店家圖片</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    <div id="imageCarousel" class="carousel slide">
                        <div class="carousel-inner">
                            <!-- 圖片將由 JavaScript 動態填充 -->
                        </div>
                        <button class="carousel-control-prev" type="button" data-bs-target="#imageCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">上一張</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#imageCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">下一張</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/admin.js"></script>
    <script>
        // 全域函數
        function showToast(type, message) {
            const toast = document.createElement('div');
            toast.className = `alert alert-${type} position-fixed top-0 start-50 translate-middle-x mt-3`;
            toast.style.zIndex = '9999';
            toast.textContent = message;
            
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.remove();
            }, 3000);
        }

        // 保存管理者消息
        function saveAdminMessage() {
            console.log('Saving admin message...');
            const message = document.getElementById('adminMessage').value.trim();
            
            if (!message) {
                showToast('warning', '請輸入公告內容');
                return;
            }

            // 儲存消息
            fetch('../api/save_admin_message.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    message: message
                })
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(data => {
                        throw new Error(data.message || '儲存失敗');
                    });
                }
                return response.json();
            })
            .then(data => {
                console.log('Save message response:', data);
                if (data.status === 'success') {
                    showToast('success', '公告已更新');
                    // 清空輸入框
                    document.getElementById('adminMessage').value = '';
                } else {
                    throw new Error(data.message || '儲存失敗');
                }
            })
            .catch(error => {
                console.error('Error saving message:', error);
                showToast('error', error.message || '儲存失敗');
            });
        }
    </script>
    <script>
        // 等待 admin.js 完全載入後再初始化
        window.addEventListener('load', function() {
            // 頁面切換
            document.querySelectorAll('.nav-link').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
                    this.classList.add('active');
                    loadPage(this.dataset.page);
                });
            });

            // 載入初始頁面
            loadPage('restaurants');
        });

        // 載入頁面內容
        function loadPage(page) {
            // 隱藏所有頁面
            document.querySelectorAll('#restaurantsPage, #ordersPage').forEach(div => {
                div.style.display = 'none';
            });

            // 顯示選中的頁面
            switch (page) {
                case 'restaurants':
                    document.getElementById('restaurantsPage').style.display = 'block';
                    break;
                case 'orders':
                    document.getElementById('ordersPage').style.display = 'block';
                    break;
            }

            // 初始化頁面內容
            initializePage(page);
        }
    </script>
</body>
</html>
