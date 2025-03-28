// 載入訂單
function loadOrders() {
    fetch('../api/admin/get_order.php')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                updateOrderList(data.orders);
            } else {
                showMessage('error', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('error', '載入訂單失敗');
        });
}

// 更新訂單列表
function updateOrderList(orders) {
    const orderList = document.getElementById('orderList');
    let totalAmount = 0;

    orderList.innerHTML = orders.map(order => {
        const orderTime = new Date(order.created_at).toLocaleString('zh-TW', {
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit'
        });
        
        totalAmount += order.price * order.quantity;
        
        return `
            <tr>
                <td>${orderTime}</td>
                <td>${order.user_name}</td>
                <td>${order.menu_name} (${order.category_name || '未分類'}) x ${order.quantity}</td>
                <td>${order.rice_option || '-'}</td>
                <td>${order.note || '-'}</td>
                <td>${order.price * order.quantity}</td>
                <td>
                    <button class="btn btn-sm btn-primary me-1" onclick="editOrder(${order.id})">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="deleteOrder(${order.id})">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>
        `;
    }).join('');

    document.getElementById('totalAmount').textContent = totalAmount;
}

// 編輯訂單
function editOrder(orderId) {
    fetch(`../api/admin/get_order.php?id=${orderId}`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const order = data.order;
                document.getElementById('editOrderId').value = order.id;
                document.getElementById('editUserName').value = order.user_name;
                document.getElementById('editQuantity').value = order.quantity;
                document.getElementById('editRiceOption').value = order.rice_option || '';
                document.getElementById('editNote').value = order.note || '';
                
                // 顯示菜品名稱和分類
                const menuInfo = document.createElement('div');
                menuInfo.className = 'mb-3';
                menuInfo.innerHTML = `
                    <label class="form-label">餐點資訊</label>
                    <div class="form-control-plaintext">
                        ${order.menu_name} (${order.category_name || '未分類'})
                    </div>
                `;
                
                // 插入到表單的開頭
                const form = document.getElementById('editOrderForm');
                form.insertBefore(menuInfo, form.firstChild);
                
                new bootstrap.Modal(document.getElementById('editOrderModal')).show();
            } else {
                showMessage('error', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('error', '載入訂單失敗');
        });
}

// 儲存訂單編輯
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('saveOrderEdit').addEventListener('click', function() {
        const orderId = document.getElementById('editOrderId').value;
        const formData = {
            id: orderId,
            user_name: document.getElementById('editUserName').value,
            quantity: document.getElementById('editQuantity').value,
            rice_option: document.getElementById('editRiceOption').value,
            note: document.getElementById('editNote').value
        };

        fetch('../api/admin/update_order.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(formData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                bootstrap.Modal.getInstance(document.getElementById('editOrderModal')).hide();
                loadOrders();
                showMessage('success', '訂單已更新');
            } else {
                showMessage('error', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('error', '更新訂單失敗');
        });
    });

    // 收單功能
    document.getElementById('closeOrders').addEventListener('click', function() {
        if (!confirm('確定要收單嗎？這將會關閉今日訂單。')) return;

        fetch('../api/admin/close_orders.php', {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                showMessage('success', '已收單');
                window.parent.postMessage({ type: 'orderClosed' }, '*');
            } else {
                showMessage('error', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('error', '收單失敗');
        });
    });

    // 下載 Excel 按鈕點擊事件
    document.getElementById('exportExcel').addEventListener('click', function() {
        loadAvailableDates();
        new bootstrap.Modal(document.getElementById('downloadExcelModal')).show();
    });

    // 確認下載按鈕點擊事件
    document.getElementById('confirmDownload').addEventListener('click', function() {
        const selectedDate = document.getElementById('downloadDate').value;
        if (!selectedDate) {
            showMessage('error', '請選擇日期');
            return;
        }
        
        window.location.href = `../api/admin/export_excel.php?date=${selectedDate}`;
        bootstrap.Modal.getInstance(document.getElementById('downloadExcelModal')).hide();
    });

    // 載入可下載的日期
    function loadAvailableDates() {
        fetch('../api/admin/get_order_dates.php')
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    const dateSelect = document.getElementById('downloadDate');
                    dateSelect.innerHTML = '<option value="">請選擇日期...</option>';
                    
                    data.dates.forEach(date => {
                        const formattedDate = new Date(date.order_date).toLocaleDateString('zh-TW', {
                            year: 'numeric',
                            month: '2-digit',
                            day: '2-digit'
                        });
                        dateSelect.innerHTML += `<option value="${date.order_date}">${formattedDate}</option>`;
                    });
                } else {
                    showMessage('error', '載入日期失敗');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessage('error', '載入日期失敗');
            });
    }

    // 刪除訂單
    function deleteOrder(orderId) {
        if (!confirm('確定要刪除這筆訂單嗎？')) return;

        fetch('../api/admin/delete_order.php', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id: orderId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                loadOrders();
                showMessage('success', '訂單已刪除');
            } else {
                showMessage('error', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('error', '刪除訂單失敗');
        });
    }

    // 顯示系統訊息
    function showMessage(type, message) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const messageHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        document.getElementById('systemMessage').innerHTML = messageHtml;
    }

    // 初始載入
    loadOrders();
});
