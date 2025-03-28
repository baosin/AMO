<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>員工訂餐系統</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .order-form {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            border-radius: 8px;
        }
        .menu-item {
            border: 1px solid #ddd;
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 4px;
        }
        .restaurant-info {
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #eee;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="order-form">
            <div id="restaurantInfo" class="restaurant-info">
                <!-- 動態載入店家資訊 -->
            </div>
            
            <div id="orderStatus" class="alert d-none"></div>
            
            <form id="orderForm" class="d-none">
                <div class="mb-3">
                    <label for="employeeName" class="form-label">員工姓名</label>
                    <input type="text" class="form-control" id="employeeName" required>
                </div>

                <div id="menuItems" class="mb-4">
                    <!-- 動態載入菜單項目 -->
                </div>

                <div class="text-end">
                    <h4>總計: <span id="totalAmount">0</span> 元</h4>
                    <button type="submit" class="btn btn-primary">提交訂單</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', async function() {
            // 載入今日店家和菜單
            try {
                const response = await fetch('api/get_daily_menu.php');
                const data = await response.json();
                
                if (data.status === 'error') {
                    showMessage(data.message, 'danger');
                    return;
                }

                // 顯示店家資訊
                const restaurantInfo = document.getElementById('restaurantInfo');
                restaurantInfo.innerHTML = `
                    <h2 class="text-center">${data.restaurant.name}</h2>
                    <p class="text-center mb-0">
                        ${data.restaurant.phone ? `電話：${data.restaurant.phone}` : ''}
                    </p>
                    <p class="text-center text-danger mb-0">
                        訂餐截止時間：${data.deadline}
                    </p>
                `;

                // 顯示菜單項目
                const menuItems = document.getElementById('menuItems');
                data.menu.forEach(item => {
                    const hasRiceOption = item.name.endsWith('飯');
                    const menuItem = `
                        <div class="menu-item">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h5>${item.name}</h5>
                                    <p class="mb-0">價格: ${item.price} 元</p>
                                </div>
                                <div class="col-auto">
                                    <div class="d-flex align-items-center gap-2">
                                        ${hasRiceOption ? `
                                        <select class="form-select rice-option" data-id="${item.id}" style="width: 100px;">
                                            <option value="">正常飯量</option>
                                            <option value="半飯">半飯</option>
                                            <option value="多飯">多飯</option>
                                            <option value="不要飯">不要飯</option>
                                        </select>
                                        ` : ''}
                                        <input type="number" 
                                               class="form-control quantity-input" 
                                               data-price="${item.price}"
                                               data-id="${item.id}"
                                               min="0" 
                                               value="0"
                                               style="width: 80px;">
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    menuItems.insertAdjacentHTML('beforeend', menuItem);
                });

                // 顯示訂單表單
                document.getElementById('orderForm').classList.remove('d-none');

                // 監聽數量變更
                document.querySelectorAll('.quantity-input').forEach(input => {
                    input.addEventListener('change', updateTotal);
                });
            } catch (error) {
                showMessage('載入菜單失敗，請重新整理頁面', 'danger');
            }
        });

        function updateTotal() {
            let total = 0;
            document.querySelectorAll('.quantity-input').forEach(input => {
                total += input.value * input.dataset.price;
            });
            document.getElementById('totalAmount').textContent = total;
        }

        document.getElementById('orderForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const orderItems = [];
            document.querySelectorAll('.quantity-input').forEach(input => {
                if (parseInt(input.value) > 0) {
                    const menuId = input.dataset.id;
                    const riceOption = document.querySelector(`.rice-option[data-id="${menuId}"]`);
                    orderItems.push({
                        menu_id: menuId,
                        quantity: input.value,
                        price: input.dataset.price,
                        riceSelection: riceOption ? riceOption.value : null
                    });
                }
            });

            if (orderItems.length === 0) {
                showMessage('請至少選擇一個項目', 'warning');
                return;
            }

            try {
                const response = await fetch('api/submit_order.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        employee_name: document.getElementById('employeeName').value,
                        items: orderItems
                    })
                });

                const data = await response.json();
                if (data.status === 'success') {
                    showMessage('訂單提交成功！', 'success');
                    document.getElementById('orderForm').reset();
                    updateTotal();
                } else {
                    showMessage(data.message, 'danger');
                }
            } catch (error) {
                showMessage('訂單提交失敗，請稍後再試', 'danger');
            }
        });

        function showMessage(message, type) {
            const statusDiv = document.getElementById('orderStatus');
            statusDiv.textContent = message;
            statusDiv.className = `alert alert-${type}`;
            statusDiv.classList.remove('d-none');
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
