<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Order[] $orders
 */
?>
<div class="orders index content">
    <h3 class="heading">My Orders</h3>
    
    <?php if (!empty($orders)): ?>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Order Date</th>
                        <th>Total</th>
                        <th>Payment Method</th>
                        <th>Order Status</th>
                        <th>Payment Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td>
                                <?= h($order->order_code) ?>
                            </td>
                            <td>
                                <?= $order->created->format('M d, Y H:i') ?>
                            </td>
                            <td class="price-highlight">
                                <?= number_format($order->total_amount) ?>₫
                            </td>
                            <td>
                                <?php 
                                $paymentMethods = [
                                    'cod' => 'Cash on Delivery',
                                    'bank_transfer' => 'Bank Transfer',
                                    'vnpay' => 'VNPay'
                                ];
                                echo $paymentMethods[$order->payment_method] ?? $order->payment_method;
                                ?>
                            </td>
                            <td>
                                <?php
                                $statusBadges = [
                                    'pending' => 'badge-warning',
                                    'confirmed' => 'badge-info',
                                    'processing' => 'badge-info',
                                    'shipping' => 'badge-info',
                                    'completed' => 'badge-success',
                                    'cancelled' => 'badge-danger'
                                ];
                                $statusLabels = [
                                    'pending' => 'Pending',
                                    'confirmed' => 'Confirmed',
                                    'processing' => 'Processing',
                                    'shipping' => 'Shipping',
                                    'completed' => 'Completed',
                                    'cancelled' => 'Cancelled'
                                ];
                                ?>
                                <span class="badge <?= $statusBadges[$order->order_status] ?? '' ?>">
                                    <?= $statusLabels[$order->order_status] ?? $order->order_status ?>
                                </span>
                            </td>
                            <td>
                                <?php
                                $paymentStatusBadges = [
                                    'pending' => 'badge-warning',
                                    'pending_confirmation' => 'badge-info',
                                    'paid' => 'badge-success',
                                    'failed' => 'badge-danger'
                                ];
                                $paymentStatusLabels = [
                                    'pending' => 'Pending',
                                    'pending_confirmation' => 'Pending Confirmation',
                                    'paid' => 'Paid',
                                    'failed' => 'Failed'
                                ];
                                ?>
                                <span class="badge <?= $paymentStatusBadges[$order->payment_status] ?? '' ?>">
                                    <?= $paymentStatusLabels[$order->payment_status] ?? $order->payment_status ?>
                                </span>
                            </td>
                            <td class="actions">
                                <?= $this->Html->link(
                                    'View',
                                    ['action' => 'view', $order->id],
                                    ['class' => 'button button-outline']
                                ) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <div class="empty-state-icon">📦</div>
            <h4>No Orders Yet</h4>
            <p>You haven't placed any orders yet.</p>
            <a href="/products" class="button">Start Shopping</a>
        </div>
    <?php endif; ?>
</div>

<style>
.orders.index {
    padding: 20px;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.heading {
    margin-bottom: 30px;
    color: #2c3e50;
}

.table-responsive {
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 20px;
}

th {
    background: #f8f9fa;
    color: #2c3e50;
    font-weight: 600;
    text-align: left;
    padding: 12px;
}

td {
    padding: 12px;
    border-bottom: 1px solid #eee;
    vertical-align: middle;
}

.price-highlight {
    color: #e74c3c;
    font-weight: 500;
}

.badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 500;
}

.badge-warning {
    background: #ffeeba;
    color: #856404;
}

.badge-info {
    background: #bee5eb;
    color: #0c5460;
}

.badge-success {
    background: #c3e6cb;
    color: #155724;
}

.badge-danger {
    background: #f5c6cb;
    color: #721c24;
}

.actions {
    white-space: nowrap;
}

.button-outline {
    padding: 4px 12px;
    font-size: 14px;
}

.empty-state {
    text-align: center;
    padding: 40px 20px;
}

.empty-state-icon {
    font-size: 48px;
    margin-bottom: 20px;
}

.empty-state h4 {
    color: #2c3e50;
    margin-bottom: 10px;
}

.empty-state p {
    color: #7f8c8d;
    margin-bottom: 20px;
}
</style>