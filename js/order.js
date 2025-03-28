// 全局變量
let currentOrders = new Map(); // 用於存儲當前用戶的訂單

// 載入訂單動態
function loadOrderFeed() {
    fetch('api/get_recent_orders.php')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                updateOrderFeed(data.orders);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
}

// 更新訂單動態顯示
function updateOrderFeed(orders) {
    const feedContent = document.getElementById('orderFeedContent');
    if (!feedContent) return;

    // 保留最新的 10 筆訂單
    const recentOrders = orders.slice(0, 10);
    
    let html = '';
    recentOrders.forEach(order => {
        const time = new Date(order.created_at).toLocaleTimeString();
        html += `
            <div class="border-bottom p-2">
                <small class="text-muted">${time}</small>
                <div>${order.employee_name} 點了 ${order.menu_name} ${order.quantity} 份</div>
            </div>
        `;
    });

    feedContent.innerHTML = html || '<div class="p-3 text-muted">暫無訂單</div>';
}

// 提交訂單
function submitOrder(menuId, menuName, price) {
    const employeeName = document.getElementById('employeeName').value;
    const quantity = document.getElementById('quantity' + menuId).value;
    const note = document.getElementById('note' + menuId).value;

    if (!employeeName) {
        alert('請輸入姓名');
        return;
    }

    if (!quantity || quantity < 1) {
        alert('請輸入有效的數量');
        return;
    }

    // 儲存當前用戶的訂單
    currentOrders.set(menuId, {
        employee_name: employeeName,
        menu_id: menuId,
        menu_name: menuName,
        quantity: quantity,
        note: note,
        price: price
    });

    // 發送訂單到伺服器
    fetch('api/submit_order.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            employee_name: employeeName,
            menu_id: menuId,
            quantity: quantity,
            note: note
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            // 更新訂單動態
            loadOrderFeed();
            
            // 顯示成功消息
            showMessage(`已點餐：${menuName} ${quantity} 份`, 'success');
            
            // 更新訂單摘要
            updateOrderSummary();
        } else {
            throw new Error(data.message || '點餐失敗');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage(error.message || '點餐失敗', 'danger');
    });
}

// 更新訂單摘要
function updateOrderSummary() {
    let totalAmount = 0;
    let summaryHtml = '';

    currentOrders.forEach(order => {
        const amount = order.price * order.quantity;
        totalAmount += amount;
        summaryHtml += `
            <div class="mb-2">
                <div class="d-flex justify-content-between">
                    <span>${order.menu_name} x ${order.quantity}</span>
                    <span>$${amount}</span>
                </div>
                ${order.note ? `<small class="text-muted">備註：${order.note}</small>` : ''}
            </div>
        `;
    });

    const orderSummary = document.getElementById('orderSummary');
    if (orderSummary) {
        orderSummary.innerHTML = summaryHtml;
    }

    const totalAmountElement = document.getElementById('totalAmount');
    if (totalAmountElement) {
        totalAmountElement.textContent = `$${totalAmount}`;
    }
}

// 顯示訊息
function showMessage(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3`;
    alertDiv.style.zIndex = '1050';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(alertDiv);
    
    setTimeout(() => {
        alertDiv.remove();
    }, 3000);
}

// 每 30 秒更新一次訂單動態
setInterval(loadOrderFeed, 30000);

// 頁面載入時初始化
document.addEventListener('DOMContentLoaded', function() {
    loadOrderFeed();
});
