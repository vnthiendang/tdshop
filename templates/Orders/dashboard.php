<?php
/**
 * Admin Dashboard for Order Management
 */
?>
<div class="orders-dashboard">
    <!-- Statistics Overview -->
    <div class="stats-overview mb-4">
        <div class="row">
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-title">Total Orders</div>
                    <div class="stat-value"><?= number_format($stats['total_orders']) ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-title">Pending Orders</div>
                    <div class="stat-value"><?= number_format($stats['pending_orders']) ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-title">Total Revenue</div>
                    <div class="stat-value"><?= number_format($stats['total_revenue']) ?> VNĐ</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-title">Orders Today</div>
                    <div class="stat-value"><?= number_format($stats['today_orders']) ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Orders that need attention -->
    <div class="row">
        <!-- Pending Orders -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3>Orders to Process</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Order Code</th>
                                    <th>Customer</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pendingOrders as $order): ?>
                                <tr>
                                    <td><?= h($order->order_code) ?></td>
                                    <td><?= h($order->customer_name) ?></td>
                                    <td><?= number_format($order->total_amount) ?> VNĐ</td>
                                    <td><?= getStatusBadge($order->order_status) ?></td>
                                    <td>
                                        <?= $this->Html->link(
                                            'Details',
                                            ['action' => 'view', $order->id],
                                            ['class' => 'btn btn-sm btn-primary']
                                        ) ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Payment Confirmations -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3>Pending Payment Confirmations</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Order Code</th>
                                    <th>Customer</th>
                                    <th>Total</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pendingPayments as $order): ?>
                                <tr>
                                    <td><?= h($order->order_code) ?></td>
                                    <td><?= h($order->customer_name) ?></td>
                                    <td><?= number_format($order->total_amount) ?> VNĐ</td>
                                    <td>
                                        <?= $this->Html->link(
                                            'View & Confirm',
                                            ['action' => 'view', $order->id],
                                            ['class' => 'btn btn-sm btn-success']
                                        ) ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.orders-dashboard {
    padding: 20px;
}

.stat-card {
    background: #fff;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 20px;
    text-align: center;
}

.stat-title {
    color: #666;
    font-size: 14px;
    margin-bottom: 10px;
}

.stat-value {
    font-size: 24px;
    font-weight: bold;
    color: #333;
}

.card {
    border: none;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}

.card-header {
    background: #f8f9fa;
    border-bottom: 1px solid #eee;
    padding: 15px 20px;
}

.card-header h3 {
    margin: 0;
    font-size: 18px;
    color: #333;
}

.table {
    margin: 0;
}

.table th {
    border-top: none;
    font-weight: 600;
    color: #666;
}

.table td {
    vertical-align: middle;
}

.badge {
    padding: 5px 10px;
    border-radius: 4px;
    font-size: 12px;
}

.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

<?php
// Include badge styles from view.php
?>
.badge-warning { background: #ffc107; color: #000; }
.badge-info { background: #17a2b8; color: #fff; }
.badge-primary { background: #007bff; color: #fff; }
.badge-success { background: #28a745; color: #fff; }
.badge-danger { background: #dc3545; color: #fff; }
</style>

<?php
// Include helper functions from view.php
function getStatusBadge($status) {
    $badges = [
        'pending' => '<span class="badge badge-warning">Pending</span>',
        'confirmed' => '<span class="badge badge-info">Confirmed</span>',
        'processing' => '<span class="badge badge-primary">Processing</span>',
        'shipping' => '<span class="badge badge-primary">Shipping</span>',
        'delivered' => '<span class="badge badge-success">Delivered</span>',
        'cancelled' => '<span class="badge badge-danger">Cancelled</span>',
    ];
    return $badges[$status] ?? $status;
}
?>