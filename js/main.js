// 全局變量
let selectedItems = new Map();
let currentRestaurant = null;
let isOrderClosed = false;
let orderFeedInterval = null;
let orderItems = [];
let lastOrderCount = 0; // 追蹤上次的訂單數量

// 提交訂單
function submitOrder() {
    if (orderItems.length === 0) {
        showMessage('warning', '請先添加餐點');
        return;
    }

    const customerName = document.getElementById('userName').value.trim();

    if (!customerName) {
        showMessage('warning', '請輸入姓名');
        document.getElementById('userName').focus();
        return;
    }

    // 計算總金額
    const totalAmount = orderItems.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    
    // 準備訂單摘要
    const orderSummary = orderItems.map(item => 
        `${item.name} x ${item.quantity}${item.riceOption ? ` (${item.riceOption})` : ''}${item.note ? ` (${item.note})` : ''}`
    ).join('<br>');

    // 先關閉訂單 Modal
    const orderModal = bootstrap.Modal.getInstance(document.getElementById('orderModal'));
    if (orderModal) {
        orderModal.hide();
    }

    // 等待訂單 Modal 完全關閉後再顯示確認 Modal
    setTimeout(() => {
        // 創建確認 Modal
        const confirmModal = document.createElement('div');
        confirmModal.className = 'modal fade';
        confirmModal.id = 'confirmOrderModal';
        confirmModal.innerHTML = `
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">確認訂單</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <strong>訂餐人：</strong>${customerName}
                        </div>
                        <div class="mb-3">
                            <strong>訂單內容：</strong><br>${orderSummary}
                        </div>
                        <div class="mb-3">
                            <strong>總金額：</strong>$${totalAmount}
                        </div>
                        <div class="alert alert-warning">
                            確定要送出訂單嗎？
                            以最後送出的訂單為主!!
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="cancelOrder()">取消</button>
                        <button type="button" class="btn btn-primary" id="confirmSubmitBtn">確定送出</button>
                    </div>
                </div>
            </div>
        `;

        // 移除舊的 Modal（如果存在）
        const oldModal = document.getElementById('confirmOrderModal');
        if (oldModal) {
            oldModal.remove();
        }

        // 添加新的 Modal 到 body
        document.body.appendChild(confirmModal);

        // 初始化 Modal
        const modal = new bootstrap.Modal(confirmModal);
        modal.show();

        // 綁定確認按鈕事件
        document.getElementById('confirmSubmitBtn').addEventListener('click', function() {
            submitOrderToServer(customerName, orderItems, modal);
        });

        // Modal 關閉後清理 DOM
        confirmModal.addEventListener('hidden.bs.modal', function () {
            confirmModal.remove();
        });
    }, 300); // 等待 300ms 確保前一個 Modal 已完全關閉
}

// 取消訂單
function cancelOrder() {
    // 關閉確認 Modal
    const confirmModal = bootstrap.Modal.getInstance(document.getElementById('confirmOrderModal'));
    if (confirmModal) {
        confirmModal.hide();
    }
    
    // 重新打開訂單 Modal
    setTimeout(() => {
        const orderModal = new bootstrap.Modal(document.getElementById('orderModal'));
        orderModal.show();
    }, 300);
}

// 提交訂單到伺服器
function submitOrderToServer(customerName, orderItems, confirmModal) {
    const orderData = {
        user_name: customerName,
        items: orderItems.map(item => ({
            id: item.id,
            name: item.name,
            price: item.price,
            quantity: item.quantity,
            category: item.category,
            rice_option: item.riceOption,
            note: item.note
        }))
    };

    console.log('Submitting order data:', orderData);

    // 發送訂單到伺服器
    fetch('api/submit_order.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(orderData)
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(data => {
                throw new Error(data.message || '送出訂單失敗');
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.status === 'success') {
            // 清空訂單
            orderItems = [];
            // 清空表單
            document.getElementById('userName').value = '';
            // 更新顯示
            updateOrderDisplay();
            // 關閉確認 Modal
            confirmModal.hide();
            showMessage('success', '訂單已送出');
            
            // 立即更新訂單動態
            loadOrderFeed();
        } else {
            showMessage('error', data.message || '送出訂單失敗');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('error', error.message || '送出訂單失敗');
    });
}

// 當頁面載入完成時
document.addEventListener('DOMContentLoaded', function() {
    console.log('Page loaded, initializing...');
    
    // 先創建必要的資料表
    fetch('api/create_tables.php')
        .then(response => response.json())
        .then(data => {
            console.log('Tables creation response:', data);
            // 檢查管理者消息
            checkAdminMessage();
        })
        .catch(error => {
            console.error('Error creating tables:', error);
            // 即使創建表格失敗，也嘗試檢查消息
            checkAdminMessage();
        });

    // 綁定訂單表單提交事件
    const orderForm = document.getElementById('orderForm');
    if (orderForm) {
        orderForm.addEventListener('submit', function(e) {
            e.preventDefault();
            submitOrder();
        });
    }

    // 先檢查是否已收單
    checkOrderStatus().then(() => {
        console.log('Order status checked, isOrderClosed:', isOrderClosed);
        // 只有在未收單的情況下才載入餐廳資訊和訂單動態
        if (!isOrderClosed) {
            loadTodayRestaurant();
            // 立即載入一次訂單動態
            loadOrderFeed();
            // 設定定時更新訂單動態（每 10 秒）
            orderFeedInterval = setInterval(loadOrderFeed, 10000);
        }
    });

    if ('serviceWorker' in navigator && 'PushManager' in window) {
        registerPushNotification();
    }
});

// 檢查管理者消息
function checkAdminMessage() {
    console.log('Checking admin message...');
    fetch('api/get_admin_message.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            console.log('Admin message response:', data);
            if (data.status === 'success' && data.message) {
                // **確保 HTML 正確解析**
                const messageContent = document.getElementById('adminMessageContent');
                if (messageContent) {
                    messageContent.innerHTML = data.message; // ✅ 使用 `.innerHTML` 確保 `<br>` 正確解析
                    const modal = new bootstrap.Modal(document.getElementById('adminMessageModal'));
                    modal.show();
                } else {
                    console.error('Admin message content element not found');
                }
            }
        })
        .catch(error => {
            console.error('Error checking admin message:', error);
        });
}

// 更新訂單動態
function loadOrderFeed() {
    fetch('api/get_today_orders.php')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                updateOrderFeed(data.orders);
                
                // 檢查是否有新訂單
                if (data.orders.length > lastOrderCount && lastOrderCount > 0) {
                    const newOrders = data.orders.length - lastOrderCount;
                    const badge = document.getElementById('newOrderCount');
                    badge.textContent = newOrders;
                    badge.style.display = 'inline-block';
                    playNotificationSound();
                }
                lastOrderCount = data.orders.length;
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
}

// 調整數量
function adjustQuantity(index, change) {
    if (index < 0 || index >= orderItems.length) return;
    
    const item = orderItems[index];
    const newQuantity = item.quantity + change;
    
    if (newQuantity <= 0) {
        // 如果數量為 0，移除項目
        orderItems.splice(index, 1);
    } else {
        item.quantity = newQuantity;
    }
    
    updateOrderDisplay();
}

// 移除訂單項目
function removeOrderItem(index) {
    if (index < 0 || index >= orderItems.length) return;
    orderItems.splice(index, 1);
    updateOrderDisplay();
}

// 更新訂單顯示函數
function updateOrderDisplay() {
    const orderList = document.getElementById('orderItems');
    const totalAmountSpan = document.getElementById('totalAmount');
    
    if (!orderList || !totalAmountSpan) {
        console.error('訂單顯示元素未找到');
        return;
    }

    if (!orderItems.length) {
        orderList.innerHTML = '<div class="text-center text-muted py-4">尚未添加任何餐點</div>';
        totalAmountSpan.textContent = '$0';
        return;
    }

    // 按分類組織訂單項目
    const groupedItems = {};
    orderItems.forEach(item => {
        if (!groupedItems[item.category]) {
            groupedItems[item.category] = [];
        }
        groupedItems[item.category].push(item);
    });

    let total = 0;
    orderList.innerHTML = Object.entries(groupedItems).map(([category, items]) => {
        const categoryItems = items.map(item => {
            total += item.price * item.quantity;
            return `
                <div class="card mb-2">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <h6 class="mb-0">${item.name}</h6>
                                <small class="text-muted">${category}</small>
                            </div>
                            <span class="text-primary">$${item.price}</span>
                        </div>
                        <div class="mb-2">
                            <textarea class="form-control form-control-sm item-note" 
                                    placeholder="餐點備註（選填）" 
                                    data-index="${orderItems.indexOf(item)}"
                                    rows="1">${item.note || ''}</textarea>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-secondary" onclick="adjustQuantity(${orderItems.indexOf(item)}, -1)">-</button>
                                <button class="btn btn-outline-secondary" disabled>${item.quantity}</button>
                                <button class="btn btn-outline-secondary" onclick="adjustQuantity(${orderItems.indexOf(item)}, 1)">+</button>
                            </div>
                            <button class="btn btn-sm btn-outline-danger" onclick="removeOrderItem(${orderItems.indexOf(item)})">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                        ${item.riceOption ? `<small class="text-muted d-block mt-1">飯量：${item.riceOption}</small>` : ''}
                    </div>
                </div>
            `;
        }).join('');

        return categoryItems;
    }).join('');

    totalAmountSpan.textContent = `$${total}`;

    // 綁定備註輸入事件
    document.querySelectorAll('.item-note').forEach(textarea => {
        textarea.addEventListener('input', function() {
            const index = parseInt(this.dataset.index);
            if (index >= 0 && index < orderItems.length) {
                orderItems[index].note = this.value.trim();
            }
        });
    });
}

// 添加到訂單
function addToOrder(itemId, itemName, price, categoryName) {
    if (isOrderClosed) {
        showMessage('warning', '點餐時間已結束');
        return;
    }

    // 從 DOM 抓取飯量選項
    const riceSelect = document.getElementById(`riceOption_${itemId}`);
    const riceOption = riceSelect ? riceSelect.value : '';

    const existingItemIndex = orderItems.findIndex(item =>
        item.id === itemId &&
        item.riceOption === riceOption
    );

    if (existingItemIndex !== -1) {
        orderItems[existingItemIndex].quantity += 1;
    } else {
        orderItems.push({
            id: itemId,
            name: itemName,
            price: price,
            quantity: 1,
            category: categoryName,
            riceOption: riceOption,
            note: ''
        });
    }

    updateOrderDisplay();
    showMessage('success', '已加入訂單');

    const orderModal = new bootstrap.Modal(document.getElementById('orderModal'));
    orderModal.show();
}

// 檢查訂單狀態
function checkOrderStatus() {
    console.log('Checking order status...');
    return fetch('api/check_order_status.php')
        .then(response => response.json())
        .then(data => {
            console.log('Order status data:', data);
            if (data.status === 'success') {
                isOrderClosed = data.is_closed;
                console.log('Updated isOrderClosed:', isOrderClosed);
                
                // 如果訂單未關閉，發送推送通知
                if (!isOrderClosed) {
                    sendPushNotification();
                }
            }
            return data;
        })
        .catch(error => {
            console.error('Error checking order status:', error);
            showMessage('檢查訂單狀態失敗', 'danger');
        });
}

// 註冊推送通知
async function registerPushNotification() {
    try {
        const registration = await navigator.serviceWorker.ready;
        const permission = await Notification.requestPermission();
        
        if (permission === 'granted') {
            const subscription = await registration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: 'YOUR_VAPID_PUBLIC_KEY' // 需要替換為您的 VAPID 公鑰
            });
            
            // 將訂閱資訊發送到伺服器
            await fetch('api/save_push_subscription.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(subscription)
            });
            
            console.log('Push notification subscription successful');
        }
    } catch (error) {
        console.error('Error registering push notification:', error);
    }
}

// 發送推送通知
async function sendPushNotification() {
    try {
        const response = await fetch('api/send_push_notification.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                title: '點餐提醒',
                message: '記得點餐喔！',
                icon: '/images/icon-192x192.png'
            })
        });
        
        const data = await response.json();
        if (data.status === 'success') {
            console.log('Push notification sent successfully');
        } else {
            console.error('Failed to send push notification:', data.message);
        }
    } catch (error) {
        console.error('Error sending push notification:', error);
    }
}

// 定期檢查訂單狀態（每30分鐘）
setInterval(checkOrderStatus, 30 * 60 * 1000);

// 載入今日店家
function loadTodayRestaurant() {
    fetch('api/get_today_restaurant.php')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success' && data.restaurant) {
                currentRestaurant = data.restaurant;
                updatePageElements(data.restaurant);
                
                // 載入菜單
                if (data.restaurant.id) {
                    loadMenu(data.restaurant.id);
                }
            } else {
                updatePageElements(null);
                showMessage('info', '目前沒有開放點餐的店家');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            updatePageElements(null);
            showMessage('error', '載入店家資訊失敗');
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
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">${title || '圖片預覽'}</h5>
                    <div class="ms-3">
                        <button type="button" class="btn btn-sm btn-outline-secondary me-2" id="zoomIn">
                            <i class="bi bi-zoom-in"></i> 放大
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary me-2" id="zoomOut">
                            <i class="bi bi-zoom-out"></i> 縮小
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="resetZoom">
                            <i class="bi bi-arrow-counterclockwise"></i> 重置
                        </button>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center p-0" style="overflow: hidden; position: relative;">
                    <div id="imageContainer" style="transform-origin: 0 0;">
                        <img src="${imageSrc}" alt="${title}" style="max-width: 100%; max-height: 90vh; width: auto; height: auto;">
                    </div>
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
    
    // 初始化並顯示 Modal
    const bsModal = new bootstrap.Modal(modal);
    bsModal.show();
    
    // 獲取需要的元素
    const imageContainer = modal.querySelector('#imageContainer');
    const img = imageContainer.querySelector('img');
    const zoomInBtn = modal.querySelector('#zoomIn');
    const zoomOutBtn = modal.querySelector('#zoomOut');
    const resetZoomBtn = modal.querySelector('#resetZoom');
    
    // 初始化變量
    let scale = 1;
    let isDragging = false;
    let startX, startY, translateX = 0, translateY = 0;
    
    // 縮放功能
    function updateTransform() {
        imageContainer.style.transform = `translate(${translateX}px, ${translateY}px) scale(${scale})`;
    }
    
    // 滑鼠滾輪縮放
    modal.querySelector('.modal-body').addEventListener('wheel', function(e) {
        e.preventDefault();
        const delta = e.deltaY > 0 ? 0.9 : 1.1;
        scale = Math.min(Math.max(0.5, scale * delta), 5);
        updateTransform();
    });
    
    // 拖曳功能
    imageContainer.addEventListener('mousedown', function(e) {
        isDragging = true;
        startX = e.clientX - translateX;
        startY = e.clientY - translateY;
        imageContainer.style.cursor = 'grabbing';
    });
    
    document.addEventListener('mousemove', function(e) {
        if (!isDragging) return;
        translateX = e.clientX - startX;
        translateY = e.clientY - startY;
        updateTransform();
    });
    
    document.addEventListener('mouseup', function() {
        isDragging = false;
        imageContainer.style.cursor = 'grab';
    });
    
    // 按鈕控制
    zoomInBtn.addEventListener('click', function() {
        scale = Math.min(scale * 1.2, 5);
        updateTransform();
    });
    
    zoomOutBtn.addEventListener('click', function() {
        scale = Math.max(scale * 0.8, 0.5);
        updateTransform();
    });
    
    resetZoomBtn.addEventListener('click', function() {
        scale = 1;
        translateX = 0;
        translateY = 0;
        updateTransform();
    });
    
    // Modal 關閉後清理 DOM
    modal.addEventListener('hidden.bs.modal', function () {
        modal.remove();
    });
}

// 檢查餐點名稱是否以「飯」結尾
function endsWithRice(name) {
    return name.endsWith('飯') || name.endsWith('便當');
}

// 載入菜單
function loadMenu(restaurantId) {
    const menuContainer = document.getElementById('menuContainer');
    const categoryNavLinks = document.getElementById('categoryNavLinks');
    
    if (!menuContainer || !categoryNavLinks) {
        console.error('Required elements not found');
        return;
    }

    // 顯示載入中狀態
    menuContainer.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">載入中...</span>
            </div>
            <div class="mt-2">載入菜單中...</div>
        </div>
    `;

    fetch(`api/get_menu.php?restaurant_id=${restaurantId}`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                if (!Array.isArray(data.menu) || data.menu.length === 0) {
                    menuContainer.innerHTML = '<div class="alert alert-info">目前沒有可用的菜單項目</div>';
                    return;
                }

                // 清空現有菜單和導覽
                menuContainer.innerHTML = '';
                categoryNavLinks.innerHTML = '';

                // 為每個分類創建一個卡片和導覽連結
                data.menu.forEach((category, index) => {
                    // 創建導覽連結
                    const navLink = document.createElement('a');
                    navLink.href = `#category_${index}`;
                    navLink.className = 'nav-link';
                    navLink.textContent = category.name;
                    navLink.onclick = (e) => {
                        e.preventDefault();
                        const targetElement = document.querySelector(`#category_${index}`);
                        const offset = 80; // 調整這個數值，讓滾動位置更合適
                        const elementPosition = targetElement.getBoundingClientRect().top + window.pageYOffset;
                        window.scrollTo({
                            top: elementPosition - offset,
                            behavior: 'smooth'
                        });

                        // 更新活動狀態
                        document.querySelectorAll('.category-nav .nav-link').forEach(link => {
                            link.classList.remove('active');
                        });
                        navLink.classList.add('active');
                    };
                    categoryNavLinks.appendChild(navLink);

                    // 創建分類區塊
                    const categorySection = document.createElement('div');
                    categorySection.id = `category_${index}`;
                    
                    categorySection.innerHTML = `
                        <h5>${category.name}</h5>
                        <div class="category-section">
                            ${category.items.map(item => {
                                const hasRiceOption = item.name.endsWith('飯') || item.name.endsWith('便當');
                                return `
                                    <div class="menu-item-card">
                                        <div class="row g-2">
                                            <div class="col-12">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <div>
                                                        <h6 class="mb-0">${item.name}</h6>
                                                        <small class="text-muted">${category.name}</small>
                                                    </div>
                                                    <span class="text-primary">$${item.price}</span>
                                                </div>
                                                <div class="input-group input-group-sm">
                                                    ${hasRiceOption ? `
                                                        <select class="form-select form-select-sm" id="riceOption_${item.id}"
                                                                aria-label="選擇飯量">
                                                            <option value="">正常飯量</option>
                                                            <option value="半飯">半飯</option>
                                                            <option value="不要飯">不要飯</option>
                                                        </select>
                                                    ` : ''}
                                                    <button class="btn btn-sm btn-outline-primary" 
    onclick="addToOrder(${item.id}, '${item.name.replace(/'/g, "\\'")}', ${item.price}, '${category.name.replace(/'/g, "\\'")}', ${item.id})">
    <i class="bi bi-plus-circle"></i>
</button>

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                `;
                            }).join('')}
                        </div>
                    `;
                    
                    menuContainer.appendChild(categorySection);
                });

                // 添加滾動監聽，更新導覽連結的活動狀態
                window.addEventListener('scroll', () => {
                    const sections = document.querySelectorAll('.category-section');
                    const navLinks = document.querySelectorAll('.category-nav .nav-link');
                    
                    let currentSectionId = '';
                    sections.forEach((section) => {
                        const sectionTop = section.offsetTop;
                        const sectionHeight = section.clientHeight;
                        if (window.pageYOffset >= sectionTop - 100) {
                            currentSectionId = section.parentElement.id;
                        }
                    });

                    navLinks.forEach(link => {
                        link.classList.remove('active');
                        if (link.getAttribute('href') === `#${currentSectionId}`) {
                            link.classList.add('active');
                        }
                    });
                });

            } else {
                menuContainer.innerHTML = '<div class="alert alert-warning">載入菜單失敗</div>';
                showMessage('error', data.message || '載入菜單失敗');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            menuContainer.innerHTML = '<div class="alert alert-danger">載入菜單失敗</div>';
            showMessage('error', '載入菜單失敗');
        });
}

// 更新頁面元素
function updatePageElements(restaurant) {
    const restaurantName = document.getElementById('restaurantName');
    const restaurantImage = document.getElementById('restaurantImage');
    const orderDeadline = document.getElementById('orderDeadline');
    
    // 更新餐廳名稱
    if (restaurantName) {
        restaurantName.textContent = restaurant ? restaurant.name : '目前沒有開放點餐的店家';
    }
    
    // 更新結單時間
    if (orderDeadline) {
        if (restaurant && restaurant.order_deadline) {
            orderDeadline.textContent = `結單時間：${restaurant.order_deadline}`;
            orderDeadline.style.display = 'block';
        } else {
            orderDeadline.style.display = 'none';
        }
    }
    
    // 更新餐廳圖片
    if (restaurantImage) {
        if (restaurant && restaurant.image_url) {
            restaurantImage.style.backgroundImage = `url(${restaurant.image_url})`;
            restaurantImage.style.cursor = 'pointer';
            restaurantImage.innerHTML = '';
            // 添加點擊事件
            restaurantImage.onclick = () => previewImage(restaurant.image_url, restaurant.name);
        } else {
            restaurantImage.style.backgroundImage = 'none';
            restaurantImage.style.cursor = 'default';
            restaurantImage.innerHTML = '<div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center;"><span>尚未選擇店家</span></div>';
            restaurantImage.onclick = null;
        }
    }
}

// 更新訂單動態顯示
function updateOrderFeed(orders) {
    const container = document.getElementById('orderFeedContent');
    container.innerHTML = '';
    
    if (!orders || orders.length === 0) {
        container.innerHTML = '<div class="order-feed-item text-muted">目前沒有訂單</div>';
        return;
    }
    
    orders.forEach(order => {
        const orderDiv = document.createElement('div');
        orderDiv.className = 'order-feed-item';
        
        // 格式化時間
        const orderTime = new Date(order.created_at);
        const timeString = orderTime.toLocaleTimeString('zh-TW', { 
            hour: '2-digit', 
            minute: '2-digit',
            second: '2-digit'
        });
        
        // 處理訂單內容
        let itemsHtml = '';
        if (order.item_name) {
            // 單一品項的情況
            itemsHtml = order.item_name;
        }
        
        orderDiv.innerHTML = `
            <div class="d-flex justify-content-between align-items-start mb-1">
                <strong>${order.user_name}</strong>
                <small class="text-muted">${timeString}</small>
            </div>
            <div class="small">${itemsHtml}</div>
        `;
        
        container.appendChild(orderDiv);
    });
}

// 播放通知音效
function playNotificationSound() {
    const audio = new Audio('assets/notification.mp3');
    audio.play().catch(error => {
        console.log('無法播放通知音效:', error);
    });
}

// 清除新訂單通知
function clearNotificationBadge() {
    const badge = document.getElementById('newOrderCount');
    badge.style.display = 'none';
    badge.textContent = '0';
}

// 當 Modal 開啟時清除通知
document.getElementById('orderFeedModal').addEventListener('show.bs.modal', function () {
    clearNotificationBadge();
});

// 顯示提示訊息
function showMessage(type, message) {
    // 創建提示元素
    const toast = document.createElement('div');
    toast.className = `alert alert-${type === 'success' ? 'success' : 'danger'} position-fixed top-0 start-50 translate-middle-x mt-3`;
    toast.style.zIndex = '1050';
    toast.innerHTML = message;
    
    // 添加到頁面
    document.body.appendChild(toast);
    
    // 3秒後移除
    setTimeout(() => {
        toast.remove();
    }, 3000);
}
