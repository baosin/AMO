// 等待 DOM 載入完成
document.addEventListener('DOMContentLoaded', function() {
    // 綁定導航事件
    document.querySelectorAll('.nav-link').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            // 移除所有 active 類
            document.querySelectorAll('.nav-link').forEach(navLink => {
                navLink.classList.remove('active');
            });
            
            // 添加 active 類到當前點擊的連結
            this.classList.add('active');
            
            // 獲取目標頁面
            const page = this.getAttribute('data-page');
            
            // 隱藏所有頁面
            document.querySelectorAll('#restaurantsPage, #ordersPage').forEach(div => {
                div.style.display = 'none';
            });
            
            // 顯示目標頁面
            const targetPage = document.getElementById(page + 'Page');
            if (targetPage) {
                targetPage.style.display = 'block';
                
                // 如果是訂單頁面，載入訂單列表
                if (page === 'orders') {
                    loadOrdersList();
                }
                
                // 初始化頁面
                initializePage(page);
            }
        });
    });

    // 載入初始資料
    loadRestaurants();
    loadSettings();
    
    // 預設顯示店家管理頁面
    document.querySelector('.nav-link[data-page="restaurants"]').click();

    // 綁定新增店家表單提交事件
    document.getElementById('addRestaurantForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData();
        formData.append('name', document.getElementById('restaurantName').value);
        formData.append('phone', document.getElementById('restaurantPhone').value);
        
        const imageFile = document.getElementById('restaurantImage').files[0];
        if (imageFile) {
            formData.append('image', imageFile);
        }
        
        fetch('../api/admin/add_restaurant.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                alert('新增成功');
                // 關閉 Modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('addRestaurantModal'));
                modal.hide();
                // 清空表單
                document.getElementById('addRestaurantForm').reset();
                // 重新載入店家列表
                loadRestaurants();
            } else {
                alert(data.message || '新增失敗');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('新增失敗');
        });
    });

    // 綁定儲存設定按鈕
    const settingsForm = document.getElementById('settingsForm');
    if (settingsForm) {
        settingsForm.addEventListener('submit', function(e) {
            e.preventDefault();
            saveSettings();
        });
    }

    // 載入現有設置
    loadSettings();
    
    // 綁定管理者消息表單提交事件
    const adminMessageForm = document.getElementById('adminMessageForm');
    if (adminMessageForm) {
        adminMessageForm.addEventListener('submit', function(e) {
            e.preventDefault();
            saveAdminMessage();
        });
    }
});

// 初始化頁面
function initializePage(page) {
    switch (page) {
        case 'restaurants':
            loadRestaurants();
            loadSettings();
            loadAdminMessages();
            break;
        case 'orders':
            // 設置當前日期
            const dateFilter = document.getElementById('orderDateFilter');
            if (dateFilter) {
               // dateFilter.value = new Date().toISOString().split('T')[0];
            }
            loadOrdersList();
            break;
    }
}

// 載入餐廳列表
function loadRestaurants() {
    fetch('../api/admin/get_restaurants.php')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const restaurantList = document.getElementById('restaurantsList');
                if (!restaurantList) return;

                let html = '';
                data.restaurants.forEach(restaurant => {
                    const imageHtml = restaurant.image_url 
                        ? `<img src="../${restaurant.image_url}" alt="${restaurant.name}" 
                             class="restaurant-thumbnail" 
                             style="width: 100px; height: 60px; object-fit: cover; border-radius: 4px; cursor: pointer;"
                             onclick="previewImage('../${restaurant.image_url}', '${restaurant.name}')">`
                        : `<div class="no-image" style="width: 100px; height: 60px; background-color: #f0f0f0; display: flex; align-items: center; justify-content: center; border-radius: 4px;"><span>無圖片</span></div>`;
                    
                    html += `
                        <tr>
                            <td>${imageHtml}</td>
                            <td>${restaurant.name}</td>
                            <td>${restaurant.phone || '-'}</td>
                            <td>
                                <div class="form-check form-switch">
                                    <input type="checkbox" class="form-check-input" 
                                           ${restaurant.is_active ? 'checked' : ''}
                                           onchange="updateRestaurantStatus(${restaurant.id}, this.checked)">
                                </div>
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-info me-2" 
                                        onclick="editRestaurant(${restaurant.id}, '${restaurant.name}', '${restaurant.phone || ''}')">
                                    編輯
                                </button>
                                <button type="button" class="btn btn-sm btn-primary me-2" 
                                        onclick="editRestaurantMenu(${restaurant.id})">
                                    編輯菜單
                                </button>
                                <button type="button" class="btn btn-sm btn-danger" 
                                        onclick="deleteRestaurant(${restaurant.id})">
                                    刪除
                                </button>
                            </td>
                        </tr>
                    `;
                });
                restaurantList.innerHTML = html;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('載入店家列表失敗');
        });
}

// 載入系統設定
function loadSettings() {
    // 首先載入所有店家
    fetch('../api/admin/get_restaurants.php')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success' && Array.isArray(data.restaurants)) {
                const select = document.getElementById('selectedRestaurant');
                if (select) {
                    // 清空現有選項
                    select.innerHTML = '<option value="">請選擇店家</option>';
                    // 添加店家選項
                    data.restaurants.forEach(restaurant => {
                        if (restaurant.is_active) {
                            const option = document.createElement('option');
                            option.value = restaurant.id;
                            option.textContent = restaurant.name;
                            select.appendChild(option);
                        }
                    });
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('載入店家列表失敗');
        });

    // 載入系統設定
    fetch('../api/admin/get_settings.php')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success' && data.settings) {
                const settings = data.settings;
                
                // 設定截止時間
                const deadlineInput = document.getElementById('orderDeadline');
                if (settings.order_deadline) {
                    try {
                        // 將 MySQL datetime 格式轉換為本地時間
                        const deadline = new Date(settings.order_deadline);
                        if (!isNaN(deadline.getTime())) {
                            deadlineInput.value = formatDateTime(deadline);
                        } else {
                            throw new Error('Invalid date');
                        }
                    } catch (error) {
                        console.error('Error parsing date:', error);
                        // 如果解析失敗，設置為明天早上 11:00
                        const tomorrow = new Date();
                        tomorrow.setDate(tomorrow.getDate() + 1);
                        tomorrow.setHours(11, 0, 0, 0);
                        deadlineInput.value = formatDateTime(tomorrow);
                    }
                } else {
                    // 預設為明天早上 11:00
                    const tomorrow = new Date();
                    tomorrow.setDate(tomorrow.getDate() + 1);
                    tomorrow.setHours(11, 0, 0, 0);
                    deadlineInput.value = formatDateTime(tomorrow);
                }

                // 設定其他選項
                document.getElementById('isOrderingActive').checked = 
                    settings.is_ordering_active === '1' || settings.is_ordering_active === true;
                
                if (settings.selected_restaurant_id) {
                    document.getElementById('selectedRestaurant').value = settings.selected_restaurant_id;
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('載入系統設定失敗');
        });
}

// 格式化日期時間為 YYYY-MM-DDThh:mm
function formatDateTime(date) {
    if (!(date instanceof Date) || isNaN(date.getTime())) {
        console.error('Invalid date:', date);
        return '';
    }
    return date.toISOString().slice(0, 16);
}

// 載入訂單列表
function loadOrdersList() {
    const restaurantFilter = document.getElementById('orderRestaurantFilter').value;
    const dateFilter = document.getElementById('orderDateFilter').value;

    fetch(`../api/admin/get_orders.php?restaurant_id=${restaurantFilter}&date=${dateFilter}`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const ordersList = document.getElementById('ordersList');
                if (!ordersList) return;

                // 按餐廳分組訂單
                const ordersByRestaurant = {};
                data.orders.forEach(order => {
                    if (!ordersByRestaurant[order.restaurant_name]) {
                        ordersByRestaurant[order.restaurant_name] = {
                            ordersByUser: {},
                            total_amount: 0,
                            total_quantity: 0,
                            order_date: order.order_date
                        };
                    }
                    if (!ordersByRestaurant[order.restaurant_name].ordersByUser[order.user_name]) {
                        ordersByRestaurant[order.restaurant_name].ordersByUser[order.user_name] = [];
                    }
                    ordersByRestaurant[order.restaurant_name].ordersByUser[order.user_name].push(order);

                    const orderAmount = parseFloat(order.price) * parseInt(order.quantity);
                    ordersByRestaurant[order.restaurant_name].total_amount += orderAmount;
                    ordersByRestaurant[order.restaurant_name].total_quantity += parseInt(order.quantity);
                });

                // 生成 HTML
                let html = '';
                for (const [restaurantName, restaurantData] of Object.entries(ordersByRestaurant)) {
                    // 格式化日期
                    const [year, month, day] = (restaurantData.order_date || '').split('-').map(num => parseInt(num, 10));
                    const orderDate = new Date(year, month - 1, day); // 月份要減1，因為 JavaScript 的月份是從0開始
                    
                    // 檢查日期是否有效
                    const formattedDate = !isNaN(orderDate.getTime()) 
                        ? orderDate.toLocaleDateString('zh-TW', { 
                            year: 'numeric', 
                            month: '2-digit', 
                            day: '2-digit',
                            weekday: 'short'
                        })
                        : '日期無效';

                    html += `
                        <div class="card mb-3">
                            <div class="card-header restaurant-header" style="cursor: pointer;" 
                                 onclick="toggleRestaurantOrders('${restaurantName.replace(/'/g, "\\'")}')">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">
                                        ${formattedDate}
                                        <span class="mx-2">-</span>
                                        <span class="text-primary">${restaurantName}</span>
                                    </h5>
                                    <div>
                                        <span class="badge bg-primary">總數量：${String(restaurantData.total_quantity).padStart(3, '0')} 個</span>
                                        <span class="badge bg-info ms-2">總金額：$${restaurantData.total_amount.toFixed(2)}</span>
                                        <span class="toggle-icon">▼</span>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body restaurant-orders" id="restaurant-${restaurantName.replace(/\s+/g, '-')}" style="display: none;">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>姓名</th>
                                                <th>品項</th>
                                                <th>分類</th>
                                                <th>數量</th>
                                                <th>單價</th>
                                                <th>金額</th>
                                                <th>飯量</th>
                                                <th>備註</th>
                                                <th>操作</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                    `;

                    // 按使用者分組，合併相同使用者的「姓名」欄位
                    for (const [userName, userOrders] of Object.entries(restaurantData.ordersByUser)) {
                        let isFirstRow = true;
                        let userRowSpan = userOrders.length;

                        userOrders.forEach((order, index) => {
                            html += `<tr>`;

                            // 只有第一筆訂單才顯示姓名，並合併 rowspan
                            if (isFirstRow) {
                                html += `<td rowspan="${userRowSpan}" style="vertical-align: middle;">${userName}</td>`;
                                isFirstRow = false;
                            }

                            const orderAmount = parseFloat(order.price) * parseInt(order.quantity);

                            html += `
                                <td>${order.menu_name}</td>
                                <td>${order.category_name || '未分類'}</td>
                                <td>${order.quantity}</td>
                                <td>$${parseFloat(order.price).toFixed(2)}</td>
                                <td>$${orderAmount.toFixed(2)}</td>
                                <td>${order.rice_option || '-'}</td>
                                <td>${order.note || '-'}</td>
                                <td>
                                    <button class="btn btn-sm btn-info me-2" onclick="editOrderModal(${order.id})">
                                        編輯
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteOrder(${order.id})">
                                        刪除
                                    </button>
                                </td>
                            </tr>
                            `;
                        });
                    }

                    html += `
                                        </tbody>
                                        <tfoot>
                                            <tr class="table-secondary">
                                                <td colspan="2">小計</td>
                                                <td>${restaurantData.total_quantity}</td>
                                                <td></td>
                                                <td>$${restaurantData.total_amount.toFixed(2)}</td>
                                                <td colspan="3"></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    `;
                }
                ordersList.innerHTML = html || '<div class="alert alert-info">目前沒有訂單</div>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showSystemMessage('載入訂單列表失敗', 'error');
        });
}


// 切換餐廳訂單的顯示/隱藏
function toggleRestaurantOrders(restaurantName) {
    const ordersDiv = document.getElementById(`restaurant-${restaurantName.replace(/\s+/g, '-')}`);
    const header = ordersDiv.previousElementSibling;
    const toggleIcon = header.querySelector('.toggle-icon');
    
    if (ordersDiv.style.display === 'none') {
        ordersDiv.style.display = 'block';
        toggleIcon.textContent = '▲';
    } else {
        ordersDiv.style.display = 'none';
        toggleIcon.textContent = '▼';
    }
}

// 收單
function closeOrders() {
    if (!confirm('確定要收單嗎？')) {
        return;
    }

    fetch('../api/admin/close_orders.php', {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            showSystemMessage('收單成功', 'success');
            // 通知前端收單
            window.parent.postMessage({ 
                type: 'orderClosed'
            }, '*');
            
            // 更新介面狀態
            document.getElementById('isOrderingActive').checked = false;
        } else {
            showSystemMessage(data.message || '收單失敗', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showSystemMessage('收單失敗', 'danger');
    });
}

// 下載 Excel
function openNewWindow() {
    let newWindow = window.open('../api/admin/download_orders_excel.php', '_blank');
    if (!newWindow) {
        alert("請允許瀏覽器開啟新視窗來顯示表格！");
    }
}


// 刪除店家
function deleteRestaurant(restaurantId) {
    if (!confirm('確定要刪除此店家嗎？此操作無法復原。')) {
        return;
    }

    fetch('../api/admin/delete_restaurant.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            id: restaurantId
        })
    })
    .then(response => {
        return response.json().then(data => {
            if (!response.ok) {
                throw new Error(data.message || '刪除失敗');
            }
            return data;
        });
    })
    .then(data => {
        if (data.status === 'success') {
            alert('店家已成功刪除');
            loadRestaurants();
        } else {
            alert(data.message || '刪除失敗');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('刪除失敗：' + error.message);
    });
}

// 刪除訂單
function deleteOrder(orderId) {
    if (!confirm('確定要刪除此訂單嗎？')) {
        return;
    }
    
    fetch(`../api/admin/delete_order.php?order_id=${orderId}`, {
        method: 'DELETE'
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            showSystemMessage('訂單已刪除', 'success');
            // 重新載入訂單列表
            loadOrdersList();
        } else {
            throw new Error(data.message || '刪除失敗');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showSystemMessage(error.message || '刪除失敗', 'danger');
    });
}

// 更新店家狀態
function updateRestaurantStatus(restaurantId, isActive) {
    fetch('../api/admin/update_restaurant.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(restaurantData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            loadRestaurants();
            loadSettings();
        } else {
            alert(data.message || '更新失敗');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('更新失敗');
    });
}

// 編輯店家菜單
function editRestaurantMenu(restaurantId) {
    // 載入菜單
    Promise.all([
        fetch(`../api/admin/get_menu.php?restaurant_id=${restaurantId}`).then(r => r.json()),
        fetch(`../api/admin/get_categories.php?restaurant_id=${restaurantId}`).then(r => r.json())
    ])
    .then(([menuData, categoryData]) => {
        if (menuData.status === 'success') {
            const modal = document.getElementById('menuModal');
            if (!modal) return;

            // 更新 Modal 內容
            modal.querySelector('.modal-title').textContent = '編輯菜單';
            
            // 創建分類管理區塊
            let categoriesHtml = `
                <div class="accordion mb-4" id="menuAccordion">
                    <!-- 分類管理區塊 -->
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingCategories">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCategories">
                                分類管理
                            </button>
                        </h2>
                        <div id="collapseCategories" class="accordion-collapse collapse" data-bs-parent="#menuAccordion">
                            <div class="accordion-body">
                                <div class="d-flex justify-content-end mb-3">
                                    <button type="button" class="btn btn-sm btn-primary" onclick="addCategory(${restaurantId})">
                                        新增分類
                                    </button>
                                </div>
                                <div class="list-group">
                                    ${categoryData.categories.map(category => `
                                        <div class="list-group-item d-flex justify-content-between align-items-center">
                                            <span>${category.name}</span>
                                            <div>
                                                <button class="btn btn-sm btn-outline-primary me-2" onclick="editCategory(${category.id}, '${category.name}')">
                                                    <i class="bi bi-pencil"></i> 編輯
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger" onclick="deleteCategory(${category.id})">
                                                    <i class="bi bi-trash"></i> 刪除
                                                </button>
                                            </div>
                                        </div>
                                    `).join('')}
                                    <div class="modal fade" id="editCategoryModal" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">編輯分類</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <input type="hidden" id="editCategoryId">
                                                    <input type="text" id="editCategoryName" class="form-control">
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                                                    <button type="button" class="btn btn-primary" onclick="saveCategoryEdit()">
                                                        儲存
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- 菜單管理區塊 -->
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingMenu">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseMenu">
                                菜單管理
                            </button>
                        </h2>
                        <div id="collapseMenu" class="accordion-collapse collapse" data-bs-parent="#menuAccordion">
                            <div class="accordion-body">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>品項名稱</th>
                                                <th>價格</th>
                                                <th>分類</th>
                                                <th>狀態</th>
                                                <th>操作</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            ${menuData.menu.map(item => `
                                                <tr>
                                                    <td>
                                                        <input type="text" class="form-control" value="${item.name}" 
                                                               onchange="updateMenuItem(${item.id}, {name: this.value})">
                                                    </td>
                                                    <td>
                                                        <input type="number" class="form-control" value="${item.price}" 
                                                               onchange="updateMenuItem(${item.id}, {price: this.value})">
                                                    </td>
                                                    <td>
                                                        <select class="form-select" onchange="updateMenuItem(${item.id}, {category_id: this.value})">
                                                            <option value="">無分類</option>
                                                            ${categoryData.categories.map(category => `
                                                                <option value="${category.id}" ${item.category_id == category.id ? 'selected' : ''}>
                                                                    ${category.name}
                                                                </option>
                                                            `).join('')}
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <div class="form-check form-switch">
                                                            <input type="checkbox" class="form-check-input" 
                                                                   ${item.is_available ? 'checked' : ''}
                                                                   onchange="updateMenuItemStatus(${item.id}, this.checked)">
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <button type="button" class="btn btn-sm btn-danger" onclick="deleteMenuItem(${item.id}, ${restaurantId})">
                                                            刪除
                                                        </button>
                                                    </td>
                                                </tr>
                                            `).join('')}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            // 創建新增品項表單
            let addItemHtml = `
                <div class="mt-3">
                    <h5>新增品項</h5>
                    <textarea class="form-control" id="bulkMenuInput" rows="5" 
                              placeholder="輸入多個品項，每行一個，格式：品名 $價格 分類"></textarea>
                    <button type="button" class="btn btn-primary mt-2" onclick="submitBulkMenu(${restaurantId})">
                        新增品項
                    </button>
                </div>
            `;
            
            const modalBody = modal.querySelector('.modal-body');
            modalBody.innerHTML = categoriesHtml + addItemHtml;

            // 綁定新增品項表單提交事件
            document.getElementById('addMenuItemForm')?.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = {
                    restaurant_id: document.getElementById('addMenuRestaurantId').value,
                    name: document.getElementById('addMenuItemName').value,
                    price: document.getElementById('addMenuItemPrice').value,
                    category_id: document.getElementById('addMenuItemCategory').value || null
                };

                fetch('../api/admin/add_menu_items.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(formData)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        // 重新載入菜單
                        editRestaurantMenu(restaurantId);
                        // 清空表單
                        e.target.reset();
                    } else {
                        alert(data.message || '新增失敗');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('新增失敗');
                });
            });

            // 顯示 Modal
            const modalInstance = bootstrap.Modal.getInstance(modal);
            if (!modalInstance) {
                new bootstrap.Modal(modal).show();
            }

            // 顯示 Modal
            new bootstrap.Modal(modal).show();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('載入菜單失敗');
    });
}

function submitBulkMenu(restaurantId) {
    if (!restaurantId) {
        alert('錯誤：找不到餐廳 ID');
        return;
    }
    submitBulkItems(restaurantId);
}

function submitBulkItems(restaurantId) {
    const inputText = document.getElementById('bulkMenuInput').value.trim();
    if (!inputText) {
        alert('請輸入品項資訊');
        return;
    }

    // 解析輸入內容，每行一個品項
    const lines = inputText.split('\n');
    const items = [];
    const newCategories = new Set(); // 用於儲存新的分類

    for (let line of lines) {
        line = line.trim();
        if (!line) continue;

        // 使用正則表達式解析「品名 $價格 分類」
        const match = line.match(/^(.+?)\s*\$(\d+(?:\.\d+)?)(?:\s+(.*))?$/);
        if (match) {
            const name = match[1].trim();
            const price = parseInt(match[2], 10);
            const category = match[3] ? match[3].trim() : ''; 

            items.push({
                name: name,
                price: price,
                category: category || null  // 也可以用 null 代表「無分類」
              });
            newCategories.add(category); // 添加到新分類集合中
        } else {
            alert(`格式錯誤：${line}\n正確格式：品項名稱 $價格 分類`);
            return;
        }
    }

    if (items.length === 0) {
        alert('沒有有效的品項');
        return;
    }

    // 先檢查現有分類
    fetch(`../api/admin/get_categories.php?restaurant_id=${restaurantId}`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const existingCategories = new Set(data.categories.map(c => c.name));
                const categoriesToAdd = [...newCategories].filter(c => !existingCategories.has(c));

                // 如果有新分類需要添加
                if (categoriesToAdd.length > 0) {
                    // 建立添加分類的 Promise 陣列
                    const categoryPromises = categoriesToAdd.map(categoryName => 
                        fetch('../api/admin/add_category.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({
                                restaurant_id: restaurantId,
                                name: categoryName
                            })
                        }).then(r => r.json())
                    );

                    // 等待所有分類添加完成
                    Promise.all(categoryPromises)
                        .then(() => {
                            // 重新獲取分類列表，以取得新分類的 ID
                            return fetch(`../api/admin/get_categories.php?restaurant_id=${restaurantId}`)
                                .then(r => r.json());
                        })
                        .then(newCategoryData => {
                            // 建立分類名稱到 ID 的映射
                            const categoryMap = {};
                            newCategoryData.categories.forEach(c => {
                                categoryMap[c.name] = c.id;
                            });

                            // 更新品項的分類 ID
                            items.forEach(item => {
                                item.category_id = categoryMap[item.category];
                                delete item.category;
                            });

                            // 提交品項
                            return submitItemsToServer(restaurantId, items);
                        });
                } else {
                    // 如果沒有新分類，直接提交品項
                    const categoryMap = {};
                    data.categories.forEach(c => {
                        categoryMap[c.name] = c.id;
                    });

                    items.forEach(item => {
                        item.category_id = categoryMap[item.category];
                        delete item.category;
                    });

                    submitItemsToServer(restaurantId, items);
                }
            }
        });
}

// 提交品項到伺服器的輔助函數
function submitItemsToServer(restaurantId, items) {
    const requestData = { 
        restaurant_id: parseInt(restaurantId, 10), 
        items 
    };

    console.log('發送的資料：', requestData);

    return fetch('../api/admin/add_menu_items.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(requestData)
    })
    .then(response => {
        if (!response.ok) {
            return response.text().then(text => {
                console.error('回應內容：', text);
                throw new Error('伺服器回應錯誤');
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.status === 'success') {
            alert('品項新增成功');
            document.getElementById('bulkMenuInput').value = ''; // 清空輸入框
            
            // 先關閉當前 modal
            const modal = document.getElementById('menuModal');
            const modalInstance = bootstrap.Modal.getInstance(modal);
            if (modalInstance) {
                modalInstance.hide();
                // 等待 modal 完全關閉後再重新載入
                modal.addEventListener('hidden.bs.modal', function handler() {
                    modal.removeEventListener('hidden.bs.modal', handler);
                    // 重新載入菜單
                    editRestaurantMenu(restaurantId);
                });
            } else {
                // 如果沒有 modal 實例就直接重新載入
                editRestaurantMenu(restaurantId);
            }
        } else {
            alert(data.message || '新增失敗');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('新增失敗：' + error.message);
    });
}

// 儲存分類編輯
function saveCategoryEdit() {
    console.log('正在儲存分類編輯...');
    
    const categoryId = document.getElementById('editCategoryId').value;
    const categoryName = document.getElementById('editCategoryName').value;

    if (!categoryId || !categoryName.trim()) {
        alert('請輸入有效的分類名稱');
        return;
    }

    fetch('../api/admin/edit_category.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            id: categoryId,
            name: categoryName
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            alert('分類修改成功');
            
            // 關閉 modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('editCategoryModal'));
            modal.hide();
            editRestaurantMenu(restaurantId); // 強制刷新 UI
            
            // 重新載入分類列表
            loadCategories();
        } else {
            alert(data.message || '分類修改失敗');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('分類修改失敗');
    });
}

// 新增分類
function addCategory(restaurantId) {
    const categoryName = prompt('請輸入分類名稱：');
    if (!categoryName) return;

    fetch('../api/admin/add_category.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            restaurant_id: restaurantId,
            name: categoryName
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            // 重新載入菜單編輯視窗
            editRestaurantMenu(restaurantId);
        } else {
            alert(data.message || '新增分類失敗');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('新增分類失敗');
    });
}

// 編輯店家
function editRestaurant(id, name, phone, imageUrls = []) {
    // 填充表單
    document.getElementById('editRestaurantId').value = id;
    document.getElementById('editRestaurantName').value = name;
    document.getElementById('editRestaurantPhone').value = phone;
    
    // 處理圖片預覽
    const previewContainer = document.getElementById('currentImagePreview');
    previewContainer.innerHTML = ''; // 清空現有預覽
    
    if (imageUrls.length > 0) {
        imageUrls.forEach((url, index) => {
            const imgContainer = document.createElement('div');
            imgContainer.className = 'image-preview-item';
            
            const img = document.createElement('img');
            img.src = url;
            img.className = 'img-thumbnail';
            img.style.maxWidth = '200px';
            img.style.marginRight = '10px';
            img.style.marginBottom = '10px';
            
            // 添加點擊事件來打開圖片瀏覽器
            img.onclick = () => openImageViewer(imageUrls, index);
            
            imgContainer.appendChild(img);
            previewContainer.appendChild(imgContainer);
        });
        previewContainer.style.display = 'block';
    } else {
        previewContainer.style.display = 'none';
    }

    // 清除之前可能選擇的檔案
    document.getElementById('editRestaurantImage').value = '';
    
    // 顯示 Modal
    const modal = new bootstrap.Modal(document.getElementById('editRestaurantModal'));
    modal.show();
}

// 綁定編輯店家表單提交事件
document.getElementById('editRestaurantForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData();
    formData.append('id', document.getElementById('editRestaurantId').value);
    formData.append('name', document.getElementById('editRestaurantName').value);
    formData.append('phone', document.getElementById('editRestaurantPhone').value);

    // 如果有選擇圖片，加入到 FormData
    const imageInput = document.getElementById('editRestaurantImage');
    if (imageInput && imageInput.files.length > 0) {
        formData.append('image', imageInput.files[0]);
    }
    
    fetch('../api/admin/update_restaurant.php', {
        method: 'POST',
        body: formData // 不要設置 Content-Type，讓瀏覽器自動設置
    })
    .then(response => {
        return response.json().then(data => {
            if (!response.ok) {
                throw new Error(data.message || '更新失敗');
            }
            return data;
        });
    })
    .then(data => {
        if (data.status === 'success') {
            alert('店家資訊已更新');
            // 關閉 Modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('editRestaurantModal'));
            modal.hide();
            // 重新載入店家列表
            loadRestaurants();
        } else {
            alert(data.message || '更新失敗');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('更新失敗：' + error.message);
    });
});


// 儲存系統設定
function saveSettings() {
    console.log('Saving settings...');
    const settings = {
        order_deadline: document.getElementById('orderDeadline').value,
        selected_restaurant_id: document.getElementById('selectedRestaurant').value,
        is_ordering_active: document.getElementById('isOrderingActive').checked
    };

    console.log('Settings to save:', settings);

    fetch('../api/admin/save_settings.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(settings)
    })
    .then(response => response.json())
    .then(data => {
        console.log('Save settings response:', data);
        if (data.status === 'success') {
            showSystemMessage('設定已儲存', 'success');
            
            // 如果開放訂餐，通知前端重新載入
            if (settings.is_ordering_active && settings.selected_restaurant_id) {
                console.log('Notifying frontend about order opening...');
                // 修改：確保消息能傳到前端
                try {
                    // 如果在 iframe 中
                    if (window.parent !== window) {
                        window.parent.postMessage({ 
                            type: 'orderOpened',
                            restaurantId: settings.selected_restaurant_id
                        }, '*');
                    } 
                    // 如果在新視窗中
                    else if (window.opener) {
                        window.opener.postMessage({ 
                            type: 'orderOpened',
                            restaurantId: settings.selected_restaurant_id
                        }, '*');
                    }
                    // 如果在同一個視窗中
                    else {
                        // 直接觸發事件
                        const event = new CustomEvent('orderOpened', {
                            detail: {
                                restaurantId: settings.selected_restaurant_id
                            }
                        });
                        window.dispatchEvent(event);
                        
                        // 重新載入頁面
                        window.location.href = '../index.php';
                    }
                    console.log('Message sent to frontend');
                } catch (error) {
                    console.error('Error sending message to frontend:', error);
                }
            }
        } else {
            showSystemMessage(data.message || '儲存設定失敗', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showSystemMessage('儲存設定失敗', 'danger');
    });
}

// 編輯訂單（Modal）
function editOrderModal(orderId) {
    // 先載入訂單資料
    fetch(`../api/admin/get_order.php?order_id=${orderId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(orderData => {
            if (orderData.status === 'success') {
                // 載入該餐廳的菜單
                return fetch(`../api/admin/get_restaurant_menu.php?restaurant_id=${orderData.order.restaurant_id}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(menuData => {
                        if (menuData.status === 'success') {
                            // 取得所有需要的元素
                            const elements = {
                                userName: document.getElementById('editUserName'),
                                menuSelect: document.getElementById('editMenuSelect'),
                                quantity: document.getElementById('editQuantity'),
                                note: document.getElementById('editNote'),
                                riceOption: document.getElementById('editRiceOption'),
                                riceOptionContainer: document.getElementById('editRiceOptionContainer'),
                                modal: document.getElementById('editOrderModal')
                            };

                            // 檢查所有元素是否存在
                            for (const [key, element] of Object.entries(elements)) {
                                if (!element) {
                                    throw new Error(`找不到元素：${key}`);
                                }
                            }

                            // 設置菜單選項
                            elements.menuSelect.innerHTML = '';
                            menuData.menu.forEach(item => {
                                const option = document.createElement('option');
                                option.value = item.id;
                                option.textContent = `${item.name} ($${item.price})`;
                                if (item.id === orderData.order.menu_id) {
                                    option.selected = true;
                                }
                                elements.menuSelect.appendChild(option);
                            });

                            // 設置訂單資料
                            elements.userName.value = orderData.order.user_name || '';
                            elements.quantity.value = orderData.order.quantity || 1;
                            elements.note.value = orderData.order.note || '';
                            elements.riceOption.value = orderData.order.rice_option || '';
                            
                            // 檢查是否需要顯示飯量選項
                            const selectedMenu = menuData.menu.find(item => item.id === orderData.order.menu_id);
                            if (selectedMenu && (selectedMenu.name.endsWith('飯') || selectedMenu.name.endsWith('便當'))) {
                                elements.riceOptionContainer.style.display = 'block';
                            } else {
                                elements.riceOptionContainer.style.display = 'none';
                                elements.riceOption.value = '';
                            }

                            // 儲存當前編輯的訂單資料
                            window.currentEditOrder = {
                                id: orderId,
                                ...orderData.order
                            };

                            // 儲存菜單資料以供後續使用
                            window.currentMenuData = menuData.menu;

                            // 綁定菜單選擇變更事件
                            elements.menuSelect.addEventListener('change', function() {
                                const selectedMenuId = this.value;
                                const selectedMenu = window.currentMenuData.find(item => item.id === selectedMenuId);
                                if (selectedMenu && (selectedMenu.name.endsWith('飯') || selectedMenu.name.endsWith('便當'))) {
                                    elements.riceOptionContainer.style.display = 'block';
                                } else {
                                    elements.riceOptionContainer.style.display = 'none';
                                    elements.riceOption.value = '';
                                }
                            });

                            // 顯示 Modal
                            const modal = new bootstrap.Modal(elements.modal);
                            modal.show();
                        } else {
                            throw new Error(menuData.message || '載入菜單失敗');
                        }
                    });
            } else {
                throw new Error(orderData.message || '載入訂單資料失敗');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showSystemMessage(`載入失敗：${error.message}`, 'error');
        });
}

// 儲存訂單修改
function saveOrderEdit() {
    // 取得所有需要的元素
    const elements = {
        userName: document.getElementById('editUserName'),
        menuSelect: document.getElementById('editMenuSelect'),
        quantity: document.getElementById('editQuantity'),
        note: document.getElementById('editNote'),
        riceOption: document.getElementById('editRiceOption'),
        riceOptionContainer: document.getElementById('editRiceOptionContainer')
    };

    // 檢查必填欄位
    if (!elements.userName.value.trim()) {
        showSystemMessage('請輸入訂餐人姓名', 'warning');
        elements.userName.focus();
        return;
    }

    if (!elements.menuSelect.value) {
        showSystemMessage('請選擇品項', 'warning');
        elements.menuSelect.focus();
        return;
    }

    const quantity = parseInt(elements.quantity.value);
    if (isNaN(quantity) || quantity < 1) {
        showSystemMessage('請輸入有效的數量', 'warning');
        elements.quantity.focus();
        return;
    }

    // 準備更新資料
    const data = {
        user_name: elements.userName.value.trim(),
        menu_id: elements.menuSelect.value,
        quantity: quantity,
        note: elements.note.value.trim(),
        rice_option: elements.riceOptionContainer.style.display === 'block' ? elements.riceOption.value : ''
    };

    // 發送更新請求
    fetch(`../api/admin/update_order.php?order_id=${window.currentEditOrder.id}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            id: window.currentEditOrder.id,
            ...data
        })
    })
    .then(response => response.json())
    .then(result => {
        if (result.status === 'success') {
            showSystemMessage('訂單更新成功', 'success');
            const modal = bootstrap.Modal.getInstance(document.getElementById('editOrderModal'));
            modal.hide();
            loadOrdersList(); // 重新載入訂單列表
        } else {
            throw new Error(result.message || '更新失敗');
        }
    })
    .catch(error => {
        showSystemMessage(error.message, 'danger');
    });
}

// 監聽菜單選擇變更
document.getElementById('editMenuSelect')?.addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const riceOptionContainer = document.getElementById('editRiceOptionContainer');
    if (riceOptionContainer) {
        if (selectedOption.text.endsWith('飯') || selectedOption.text.endsWith('便當')) {
            riceOptionContainer.style.display = 'block';
        } else {
            riceOptionContainer.style.display = 'none';
            const riceOption = document.getElementById('editRiceOption');
            if (riceOption) {
                riceOption.value = '';
            }
        }
    }
});

// 載入訂單管理
function loadOrderManagement() {
    loadOrderStatistics();
    loadOrders();
    loadOrderHistory();
}

// 載入訂單統計
function loadOrderStatistics() {
    fetch('../api/admin/get_order_statistics.php')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                document.getElementById('totalOrders').textContent = data.total_orders || '0';
                document.getElementById('totalAmount').textContent = data.total_amount || '0';
                document.getElementById('averageOrderAmount').textContent = data.average_order_amount || '0';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showSystemMessage('載入訂單統計失敗', 'error');
        });
}

// 載入訂單列表
function loadOrders() {
    fetch('../api/admin/get_orders.php')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const ordersList = document.getElementById('ordersList');
                if (!ordersList) return;

                let html = '';
                data.orders.forEach(order => {
                    // 解析日期字符串（格式：YYYY-MM-DD）
                    const [year, month, day] = (order.order_date || '').split('-').map(num => parseInt(num, 10));
                    const orderDate = new Date(year, month - 1, day); // 月份要減1，因為 JavaScript 的月份是從0開始
                    
                    // 檢查日期是否有效
                    const formattedDate = !isNaN(orderDate.getTime()) 
                        ? orderDate.toLocaleDateString('zh-TW', { 
                            year: 'numeric', 
                            month: '2-digit', 
                            day: '2-digit',
                            weekday: 'short'
                        })
                        : '日期無效';
                    
                    html += `
                        <tr>
                            <td>${formattedDate}</td>
                            <td>${order.user_name}</td>
                            <td>${order.restaurant_name}</td>
                            <td>${order.menu_name}</td>
                            <td>${order.quantity}</td>
                            <td>${order.note || '-'}</td>
                            <td>
                                <button class="btn btn-sm btn-info me-2" onclick="editOrderInline(${order.id}, 'user_name', this)">
                                    編輯
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="deleteOrder(${order.id})">
                                    刪除
                                </button>
                            </td>
                        </tr>
                    `;
                });
                ordersList.innerHTML = html;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showSystemMessage('載入訂單列表失敗', 'error');
        });
}

// 載入修改歷史
function loadOrderHistory() {
    fetch('../api/admin/get_order_history.php')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const historyList = document.getElementById('orderHistoryList');
                if (!historyList) return;

                let html = '';
                data.history.forEach(record => {
                    html += `
                        <tr>
                            <td>${record.modified_at}</td>
                            <td>${record.user_name}</td>
                            <td>${record.action}</td>
                            <td>${record.details}</td>
                        </tr>
                    `;
                });
                historyList.innerHTML = html;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showSystemMessage('載入修改歷史失敗', 'error');
        });
}

// 顯示系統訊息
function showSystemMessage(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3`;
    alertDiv.style.zIndex = '1050';
    alertDiv.role = 'alert';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(alertDiv);
    
    // 3秒後自動消失
    setTimeout(() => {
        alertDiv.remove();
    }, 3000);
}

// 編輯訂單（折疊式）
function editOrderInline(orderId, field, element) {
    const currentValue = element.dataset.value;
    const input = document.createElement('input');
    input.type = 'text';
    input.value = currentValue;
    input.className = 'form-control form-control-sm';
    
    // 儲存原始內容
    const originalContent = element.innerHTML;
    
    // 替換為輸入框
    element.innerHTML = '';
    element.appendChild(input);
    input.focus();
    
    // 處理完成編輯
    function finishEdit(save) {
        const newValue = input.value.trim();
        
        if (save && newValue !== currentValue) {
            // 發送更新請求
            fetch('../api/admin/update_order.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    order_id: orderId,
                    field: field,
                    value: newValue
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    element.dataset.value = newValue;
                    element.innerHTML = newValue || '-';
                    // 重新載入訂單列表以更新總計
                    loadOrdersList();
                } else {
                    throw new Error(data.message || '更新失敗');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                element.innerHTML = originalContent;
                alert('更新失敗：' + error.message);
            });
        } else {
            element.innerHTML = originalContent;
        }
    }
    
    // 綁定事件
    input.addEventListener('blur', () => finishEdit(true));
    input.addEventListener('keyup', (e) => {
        if (e.key === 'Enter') {
            finishEdit(true);
        } else if (e.key === 'Escape') {
            finishEdit(false);
        }
    });
}

// 載入店家選項
function loadRestaurantOptions() {
    fetch('../api/admin/get_orders.php')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success' && data.restaurants) {
                const restaurantFilter = document.getElementById('orderRestaurantFilter');
                if (restaurantFilter) {
                    let html = '<option value="">全部店家</option>';
                    data.restaurants.forEach(restaurant => {
                        html += `<option value="${restaurant.id}">${restaurant.name}</option>`;
                    });
                    restaurantFilter.innerHTML = html;
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showSystemMessage('載入店家選項失敗', 'error');
        });
}

// 預覽圖片
function previewImage(imageSrc, title) {
    // 創建 Modal 元素
    const modal = document.createElement('div');
    modal.className = 'modal fade';
    modal.id = 'imagePreviewModal';
    modal.tabIndex = '-1';
    
    modal.innerHTML = `
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">${title || '圖片預覽'}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <img src="${imageSrc}" alt="${title}" style="max-width: 100%; max-height: 80vh;">
                </div>
            </div>
        </div>
    `;
    
    // 移除舊的 Modal（如果存在）
    const oldModal = document.getElementById('imagePreviewModal');
    if (oldModal) {
        oldModal.remove();
    }
    
    // 添加新的 Modal 到 body
    document.body.appendChild(modal);
    
    // 顯示 Modal
    const bsModal = new bootstrap.Modal(modal);
    bsModal.show();
    
    // Modal 關閉後清理 DOM
    modal.addEventListener('hidden.bs.modal', function () {
        modal.remove();
    });
}

// 頁面初始化
document.addEventListener('DOMContentLoaded', function() {
    // 設置預設日期為今天
    //const today = new Date().toISOString().split('T')[0];
    //document.getElementById('orderDateFilter').value = today;
    
    // 載入店家選項
    loadRestaurantOptions();
    
    // 載入訂單列表
    loadOrdersList();
});

// 保存管理者消息
function saveAdminMessage() {
    console.log('Saving admin message...');
    const message = document.getElementById('adminMessage').value.trim();

    if (!message) {
        showToast('warning', '請輸入公告內容');
        return;
    }

    const formData = new FormData();
    formData.append('message', message); // 保持換行符號

    fetch('../api/admin/save_admin_message.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            showToast('success', '公告已更新');
            document.getElementById('adminMessage').value = '';
            loadAdminMessages();
        } else {
            throw new Error(data.message || '保存失敗');
        }
    })
    .catch(error => {
        console.error('Error saving message:', error);
        showToast('error', error.message);
    });
}


// 顯示提示訊息
function showToast(type, message) {
    const toast = document.createElement('div');
    toast.className = `alert alert-${type} alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3`;
    toast.style.zIndex = '9999';
    toast.textContent = message;
    
    document.body.appendChild(toast);
    
    // 3秒後自動消失
    setTimeout(() => {
        toast.remove();
    }, 3000);
}

// 刪除菜單項目
function deleteMenuItem(menuId, restaurantId) {
    if (!confirm('確定要刪除此菜單項目？')) {
        return;
    }

    fetch('../api/admin/delete_menu_item.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ id: menuId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            alert('刪除成功');
            editRestaurantMenu(restaurantId); // 現在有 restaurantId 參數了
        } else {
            alert(data.message || '刪除失敗');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('刪除失敗');
    });
}

function editCategory(categoryId, categoryName) {
    console.log(`正在編輯分類：ID=${categoryId}, 名稱=${categoryName}`);

    const idField = document.getElementById('editCategoryId');
    const nameField = document.getElementById('editCategoryName');
    
    console.log('editCategoryId:', idField);
    console.log('editCategoryName:', nameField);

    if (!idField || !nameField) {
        console.error('❌ 找不到 editCategoryId 或 editCategoryName');
        return;
    }

    idField.value = categoryId;
    nameField.value = categoryName;

    const modal = new bootstrap.Modal(document.getElementById('editCategoryModal'));
    modal.show();
}

// 新增品項區域的HTML
function getNewItemRowHtml() {
    return `
        <div class="menu-item-row mb-2">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <input type="text" class="form-control item-name" placeholder="品項名稱" required>
                </div>
                <div class="col">
                    <input type="number" class="form-control item-price" placeholder="價格" required min="0">
                </div>
                <div class="col-auto">
                    <button type="button" class="btn btn-outline-danger btn-sm remove-item">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    `;
}

// 顯示新增品項的表單
function showAddItemForm(restaurantId) {
    const modalContent = `
        <div class="modal fade" id="addItemModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">新增品項</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="addItemForm">
                            <div class="mb-3">
                                <label class="form-label">選擇分類</label>
                                <select class="form-select" id="categorySelect" required>
                                    <option value="">選擇分類</option>
                                </select>
                            </div>
                            <div id="itemsList">
                                ${getNewItemRowHtml()}
                            </div>
                            <div class="text-center mt-3">
                                <button type="button" class="btn btn-outline-primary btn-sm" id="addMoreItem">
                                    <i class="bi bi-plus-circle"></i> 新增更多品項
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                        <button type="button" class="btn btn-primary" onclick="submitItems()">儲存</button>
                    </div>
                </div>
            </div>
        </div>
    `;

    // 添加modal到頁面
    document.body.insertAdjacentHTML('beforeend', modalContent);

    // 獲取分類列表
    fetch('api/admin/get_categories.php?restaurant_id=' + restaurantId)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const categorySelect = document.getElementById('categorySelect');
                data.categories.forEach(category => {
                    const option = new Option(category.name, category.id);
                    categorySelect.add(option);
                });
            }
        });

    // 綁定新增更多品項按鈕事件
    document.getElementById('addMoreItem').addEventListener('click', function() {
        document.getElementById('itemsList').insertAdjacentHTML('beforeend', getNewItemRowHtml());
    });

    // 綁定移除品項按鈕事件
    document.getElementById('itemsList').addEventListener('click', function(e) {
        if (e.target.closest('.remove-item')) {
            const itemRow = e.target.closest('.menu-item-row');
            // 確保至少保留一個品項輸入框
            if (document.querySelectorAll('.menu-item-row').length > 1) {
                itemRow.remove();
            }
        }
    });

    // 顯示modal
    const modal = new bootstrap.Modal(document.getElementById('addItemModal'));
    modal.show();

    // modal關閉時移除元素
    document.getElementById('addItemModal').addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
}

// 提交新增品項
function submitItems(event) {
    event.preventDefault();
    
    const categoryId = document.getElementById('categorySelect').value;
    if (!categoryId) {
        showMessage('error', '請選擇分類');
        return;
    }

    const items = [];
    const itemRows = document.querySelectorAll('.menu-item-row');
    
    for (const row of itemRows) {
        const name = row.querySelector('.item-name').value.trim();
        const price = row.querySelector('.item-price').value;
        
        if (!name || !price) {
            showMessage('error', '請填寫所有品項資料');
            return;
        }
        
        items.push({
            name: name,
            price: parseFloat(price),
            category_id: categoryId
        });
    }

    fetch('api/admin/add_menu_items.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            items: items
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            showMessage('success', '品項新增成功');
            bootstrap.Modal.getInstance(document.getElementById('addItemModal')).hide();
            // 重新載入菜單
            loadMenu();
        } else {
            throw new Error(data.message || '品項新增失敗');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('error', error.message);
    });
}

// 顯示菜單管理 Modal
function showMenuModal(restaurantId) {
    const menuModal = document.getElementById('menuModal');
    
    // 設置餐廳 ID
    menuModal.setAttribute('data-restaurant-id', restaurantId);
    
    // 顯示 Modal
    const modal = new bootstrap.Modal(menuModal);
    modal.show();
}

// 載入分類
function loadCategories(restaurantId) {
    if (!restaurantId) return;

    fetch(`../api/admin/get_categories.php?restaurant_id=${restaurantId}`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const categoryList = document.getElementById('categoryList');
                if (!categoryList) return;

                categoryList.innerHTML = '';
                
                if (data.categories && data.categories.length > 0) {
                    data.categories.forEach(category => {
                        const item = document.createElement('div');
                        item.className = 'list-group-item d-flex justify-content-between align-items-center';
                        item.innerHTML = `
                            <span>${category.name}</span>
                            <div>
                                <button class="btn btn-sm btn-primary" onclick="editCategory(${category.id}, '${category.name}')">
                                    編輯
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="deleteCategory(${category.id})">
                                    刪除
                                </button>
                            </div>
                        `;
                        categoryList.appendChild(item);
                    });
                } else {
                    categoryList.innerHTML = '<div class="text-center text-muted">尚未新增任何分類</div>';
                }
            }
        })
        .catch(error => {
            console.error('Error loading categories:', error);
            showMessage('error', '載入分類失敗');
        });
}

// 載入菜單
function loadMenu(restaurantId) {
    // 同時載入菜單和分類資料
    Promise.all([
        fetch(`../api/admin/get_menu.php?restaurant_id=${restaurantId}`).then(r => r.json()),
        fetch(`../api/admin/get_categories.php?restaurant_id=${restaurantId}`).then(r => r.json())
    ])
    .then(([menuData, categoryData]) => {
        if (menuData.status === 'success' && categoryData.status === 'success') {
            const menuList = document.getElementById('menuList');
            if (!menuList) return;
            
            menuList.innerHTML = '';

            // 按分類分組顯示菜單項目
            const menuByCategory = {};
            const categoryMap = {};

            // 建立分類映射
            categoryData.categories.forEach(category => {
                categoryMap[category.id] = category;
                menuByCategory[category.id] = [];
            });

            // 新增一個未分類的分類
            menuByCategory['uncategorized'] = [];

            // 將菜單項目分組到對應的分類
            if (menuData.menu) {
                menuData.menu.forEach(item => {
                    const categoryId = item.category_id || 'uncategorized';
                    if (!menuByCategory[categoryId]) {
                        menuByCategory[categoryId] = [];
                    }
                    menuByCategory[categoryId].push(item);
                });
            }

            // 按分類顯示菜單項目
            Object.keys(menuByCategory).forEach(categoryId => {
                const items = menuByCategory[categoryId];
                if (items.length === 0) return; // 跳過沒有項目的分類

                const categoryName = categoryId === 'uncategorized' ? '未分類' : 
                    (categoryMap[categoryId] ? categoryMap[categoryId].name : '未分類');
                
                const categoryDiv = document.createElement('div');
                categoryDiv.className = 'menu-category mb-4';
                categoryDiv.innerHTML = `<h5 class="mb-3">${categoryName}</h5>`;
                
                const itemsDiv = document.createElement('div');
                itemsDiv.className = 'row';
                
                items.forEach(item => {
                    itemsDiv.innerHTML += `
                        <div class="col-md-6 mb-3">
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="card-title">${item.name}</h6>
                                    <p class="card-text">
                                        價格: $${item.price}<br>
                                        分類: ${categoryName}
                                    </p>
                                    <div class="form-check form-switch mb-2">
                                        <input type="checkbox" class="form-check-input" 
                                            ${item.is_available ? 'checked' : ''}
                                            onchange="updateMenuItemStatus(${item.id}, this.checked)">
                                    </div>
                                    <button class="btn btn-sm btn-primary me-2" onclick="editMenuItem(${item.id})">
                                        編輯
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger" onclick="deleteMenuItem(${item.id}, ${restaurantId})">
                                        刪除
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;
                });
                
                categoryDiv.appendChild(itemsDiv);
                menuList.appendChild(categoryDiv);
            });
        } else {
            showMessage('error', menuData.message || categoryData.message || '載入資料失敗');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('error', '載入菜單失敗');
    });
}

// 更新菜單項目
function updateMenuItem(menuId, data) {
    fetch('../api/admin/update_menu_item.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            id: menuId,
            ...data
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            // 重新載入菜單
            const restaurantId = document.querySelector('#menuModal').getAttribute('data-restaurant-id');
            if (restaurantId) {
                loadMenu(restaurantId);
            }
        } else {
            alert(data.message || '更新失敗');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('更新失敗');
    });
}

// 更新菜單項目狀態
function updateMenuItemStatus(itemId, isAvailable) {
    fetch('../api/admin/update_menu_item_status.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ 
            item_id: itemId, 
            is_available: isAvailable 
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            showMessage('success', isAvailable ? '此品項已恢復供應' : '此品項已設為完售');
        } else {
            throw new Error(data.message || '更新狀態失敗');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('error', error.message);
       
    });

}

// 顯示新增分類 Modal
function showAddCategoryModal() {
    const modal = new bootstrap.Modal(document.getElementById('addCategoryModal'));
    document.getElementById('newCategoryName').value = '';
    modal.show();
}

// 顯示編輯分類 Modal
function showEditCategoryModal(id, name, orderNum) {
    document.getElementById('editCategoryId').value = id;
    document.getElementById('editCategoryName').value = name;
    
    const modal = new bootstrap.Modal(document.getElementById('editCategoryModal'));
    modal.show();
}

// 提交新增分類
function submitCategory(event) {
    event.preventDefault();
    
    const name = document.getElementById('newCategoryName').value.trim();
    const orderNum = document.getElementById('newCategoryOrder').value;
    const restaurantId = document.querySelector('#menuModal').getAttribute('data-restaurant-id');
    
    fetch('../api/admin/add_category.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            restaurant_id: restaurantId,
            name: name,
            order_num: orderNum
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            // 關閉 Modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('addCategoryModal'));
            modal.hide();
            
            // 清空表單
            document.getElementById('newCategoryName').value = '';
            document.getElementById('newCategoryOrder').value = '0';
            
            // 重新載入分類
            loadCategories(restaurantId);
            
            alert('新增成功');
        } else {
            alert(data.message || '新增失敗');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('新增失敗');
    });
}

// 提交編輯分類
function submitEditCategory(event) {
    event.preventDefault();
    
    const id = document.getElementById('editCategoryId').value;
    const name = document.getElementById('editCategoryName').value;
    const orderNum = document.getElementById('editCategoryOrder').value;
    const restaurantId = document.querySelector('#menuModal').getAttribute('data-restaurant-id');
    
    fetch('../api/admin/edit_category.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            id: id,
            name: name,
            order_num: orderNum
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            // 關閉 Modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('editCategoryModal'));
            modal.hide();
            
            // 重新載入分類
            loadCategories(restaurantId);
            
            alert('修改成功');
        } else {
            alert(data.message || '修改失敗');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('修改失敗');
    });
}

// 刪除分類
function deleteCategory(categoryId) {
    if (!confirm('確定要刪除此分類嗎？')) return;

    console.log('Deleting category ID:', categoryId); // Debug 記錄 ID

    fetch('../api/admin/delete_category.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ category_id: categoryId }) // 確保正確的 key
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            alert('分類已刪除');
            loadCategories();  // 重新載入分類列表
            editRestaurantMenu(restaurantId); // 強制刷新 UI
        } else {
            alert(data.message || '刪除失敗');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('刪除失敗');
    });
}

// 在菜單 Modal 顯示時初始化
document.getElementById('menuModal').addEventListener('hidden.bs.modal', function () {
    document.body.classList.remove('modal-open'); // 移除 Bootstrap 殘留的 modal 狀態
    document.querySelectorAll('.modal-backdrop').forEach(el => el.remove()); // 強制移除背景遮罩
});

// 顯示訊息的通用函數
function showMessage(type, message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    alertDiv.style.top = '20px';
    alertDiv.style.right = '20px';
    alertDiv.style.zIndex = '9999';
    alertDiv.role = 'alert';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    document.body.appendChild(alertDiv);
    
    // 3秒後自動移除
    setTimeout(() => {
        alertDiv.remove();
    }, 3000);
}

// 打開圖片瀏覽器
function openImageViewer(images, startIndex = 0) {
    const carousel = document.getElementById('imageCarousel');
    const carouselInner = carousel.querySelector('.carousel-inner');
    carouselInner.innerHTML = '';

    // 創建輪播項目
    images.forEach((url, index) => {
        const item = document.createElement('div');
        item.className = `carousel-item${index === startIndex ? ' active' : ''}`;
        
        const img = document.createElement('img');
        img.src = url;
        img.className = 'd-block w-100';
        img.alt = `店家圖片 ${index + 1}`;
        
        item.appendChild(img);
        carouselInner.appendChild(item);
    });

    // 初始化並顯示 Modal
    const carouselInstance = new bootstrap.Carousel(carousel, {
        interval: false, // 停用自動輪播
        keyboard: true  // 啟用鍵盤控制
    });
    
    const modal = new bootstrap.Modal(document.getElementById('imageViewerModal'));
    modal.show();

    // 添加鍵盤事件監聽
    const handleKeyPress = (e) => {
        if (e.key === 'ArrowLeft') {
            carouselInstance.prev();
        } else if (e.key === 'ArrowRight') {
            carouselInstance.next();
        } else if (e.key === 'Escape') {
            modal.hide();
        }
    };

    document.addEventListener('keydown', handleKeyPress);

    // Modal 關閉後移除事件監聽
    document.getElementById('imageViewerModal').addEventListener('hidden.bs.modal', () => {
        document.removeEventListener('keydown', handleKeyPress);
    }, { once: true });
}

// 載入歷史公告列表
function loadAdminMessages() {
    fetch('../api/admin/get_admin_messages.php')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const messagesList = document.getElementById('adminMessagesList');
                if (data.messages && data.messages.length > 0) {
                    messagesList.innerHTML = data.messages.map(message => `
                        <tr>
                            <td style="white-space: pre-line;">${message.message}</td> 
                            <td>${new Date(message.created_at).toLocaleString()}</td>
                            <td>
                                <button class="btn btn-danger btn-sm" onclick="deleteAdminMessage(${message.id})">
                                    <i class="fas fa-trash"></i> 刪除
                                </button>
                            </td>
                        </tr>
                    `).join('');
                } else {
                    messagesList.innerHTML = '<tr><td colspan="3" class="text-center">暫無歷史公告</td></tr>';
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('error', '載入歷史公告失敗');
        });
}


// 刪除歷史公告
function deleteAdminMessage(id) {
    if (!confirm('確定要刪除此公告嗎？')) {
        return;
    }

    const formData = new FormData();
    formData.append('id', id);

    fetch('../api/admin/delete_admin_message.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            showMessage('success', '公告已刪除');
            loadAdminMessages();
        } else {
            throw new Error(data.message || '刪除失敗');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('error', error.message);
    });
}