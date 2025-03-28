<?php
session_start();
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>訂單管理</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-dark bg-dark">
        <div class="container-fluid">
            <span class="navbar-brand">訂單管理</span>
            <div class="d-flex">
                <button id="exportExcel" class="btn btn-secondary me-2">表格</button>
                <button id="closeOrders" class="btn btn-danger">收單</button>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- 系統訊息 -->
        <div id="systemMessage"></div>

        <!-- 訂單列表 -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>訂餐時間</th>
                                <th>姓名</th>
                                <th>餐點</th>
                                <th>白飯</th>
                                <th>備註</th>
                                <th>價格</th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody id="orderList">
                            <!-- 訂單將由 JavaScript 動態添加 -->
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="5" class="text-end"><strong>總計：</strong></td>
                                <td id="totalAmount" colspan="2"><strong>0</strong></td>
                            </tr>
                        </tfoot>
                    </table>
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
                        <input type="hidden" id="editOrderId">
                        <!-- 菜品信息將由 JavaScript 動態插入到這裡 -->
                        <div class="mb-3">
                            <label for="editUserName" class="form-label">姓名</label>
                            <input type="text" class="form-control" id="editUserName" required>
                        </div>
                        <div class="mb-3">
                            <label for="editQuantity" class="form-label">數量</label>
                            <input type="number" class="form-control" id="editQuantity" min="1" required>
                        </div>
                        <div class="mb-3">
                            <label for="editRiceOption" class="form-label">白飯選項</label>
                            <select class="form-select" id="editRiceOption">
                                <option value="">正常飯量</option>
                                <option value="不要飯">不要飯</option>
                                <option value="半飯">半飯</option>
                                <option value="多飯">多飯</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="editNote" class="form-label">備註</label>
                            <textarea class="form-control" id="editNote" rows="2"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                    <button type="button" class="btn btn-primary" id="saveOrderEdit">儲存</button>
                </div>
            </div>
        </div>
    </div>



    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/orders.js"></script>
</body>
</html>
