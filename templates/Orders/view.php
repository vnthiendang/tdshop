<?php
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

function getPaymentBadge($status) {
    $badges = [
        'pending' => '<span class="badge badge-warning">Payment Pending</span>',
        'paid' => '<span class="badge badge-success">Paid</span>',
        'failed' => '<span class="badge badge-danger">Failed</span>',
        'refunded' => '<span class="badge badge-info">Refunded</span>',
    ];
    return $badges[$status] ?? $status;
}
?>

<div class="order-view">
    <div class="order-header">
        <h2>Order #<?= h($order->order_code) ?></h2>
        <div class="order-badges">
            <?= getStatusBadge($order->order_status) ?>
            <?= getPaymentBadge($order->payment_status) ?>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3>Customer Information</h3>
                </div>
                <div class="card-body">
                    <p><strong>Name:</strong> <?= h($order->customer_name) ?></p>
                    <p><strong>Phone:</strong> <?= h($order->customer_phone) ?></p>
                    <p><strong>Email:</strong> <?= h($order->customer_email) ?></p>
                    <p><strong>Address:</strong> <?= h($order->shipping_address) ?></p>
                </div>
            </div>
            
            <div class="card mt-3">
                <div class="card-header">
                        <h3>Order Details</h3>
                    </div>
                    <div class="card-body">
                        <p><strong>Total Amount:</strong> <?= number_format($order->total_amount) ?> VND</p>
                        <p><strong>Payment Method:</strong> 
                            <?php
                            $methods = [
                                'cod' => 'COD - Pay on Delivery',
                                'bank_transfer' => 'Bank Transfer',
                                'vnpay' => 'VNPay',
                            ];
                            echo $methods[$order->payment_method] ?? $order->payment_method;
                            ?>
                        </p>
                    
                        <?php if ($order->payment_date): ?>
                            <p><strong>Payment Date:</strong> <?= $order->payment_date->format('d/m/Y H:i') ?></p>
                        <?php endif; ?>
                    
                        <?php if ($order->transaction_id): ?>
                            <p><strong>Transaction ID:</strong> <?= h($order->transaction_id) ?></p>
                        <?php endif; ?>
                    
                        <?php if ($order->notes): ?>
                            <p><strong>Notes:</strong> <?= h($order->notes) ?></p>
                        <?php endif; ?>
                    </div>
            </div>
            
            <!-- Display payment proof -->
            <?php if ($order->payment_method === 'bank_transfer' && $order->payment_proof): ?>
            <div class="card mt-3">
                <div class="card-header">
                    <h3>Payment Proof</h3>
                </div>
                <div class="card-body">
                    <img src="/uploads/payment_proofs/<?= h($order->payment_proof) ?>" 
                         alt="Payment Proof" 
                         class="img-fluid"
                         style="max-width: 400px;">
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Actions -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3>Actions</h3>
                </div>
                <div class="card-body">
                    
                    <!-- Upload payment proof (customer) -->
                    <?php if ($order->payment_method === 'bank_transfer' && $order->payment_status === 'pending' && !$order->payment_proof): ?>
                    <div class="action-section">
                        <h4>Upload Proof</h4>
                        <?= $this->Form->create(null, [
                            'url' => ['controller' => 'Payments', 'action' => 'uploadPaymentProof', $order->id],
                            'type' => 'file'
                        ]) ?>
                        <?= $this->Form->file('payment_proof', ['required' => true]) ?>
                        <?= $this->Form->button('Upload', ['class' => 'btn btn-primary btn-block mt-2']) ?>
                        <?= $this->Form->end() ?>
                    </div>
                    <hr>
                    <?php endif; ?>
                    
                    <!-- Admin confirm payment -->
                    <?php if (!empty($currentUser) && $currentUser->role === 'admin' && $order->payment_status === 'pending' && $order->payment_proof): ?>
                    <div class="action-section">
                        <h4>Admin: Confirm Payment</h4>
                        <?= $this->Form->postButton(
                            'Confirm Paid',
                            ['controller' => 'Payments', 'action' => 'adminConfirmPayment', $order->id],
                            [
                                'class' => 'btn btn-success btn-block',
                                'confirm' => 'Confirm this order has been paid?'
                            ]
                        ) ?>
                    </div>
                    <hr>
                    <?php endif; ?>
                    
                    <!-- Update order status (Admin only) -->
                    <?php if (!empty($currentUser) && $currentUser->role === 'admin' && $order->order_status !== 'cancelled' && $order->order_status !== 'delivered'): ?>
                    <div class="action-section">
                        <h4>Update Status</h4>
                        <?= $this->Form->create(null, ['url' => ['action' => 'updateStatus', $order->id]]) ?>
                        <?= $this->Form->select('order_status', [
                            'pending' => 'Pending',
                            'confirmed' => 'Confirmed',
                            'processing' => 'Processing',
                            'shipping' => 'Shipping',
                            'delivered' => 'Delivered',
                        ], [
                            'value' => $order->order_status,
                            'class' => 'form-control'
                        ]) ?>
                        <?= $this->Form->button('Update', ['class' => 'btn btn-primary btn-block mt-2']) ?>
                        <?= $this->Form->end() ?>
                    </div>
                    <hr>
                    <?php endif; ?>
                    
                    <!-- Cancel order -->
                    <?php 
                    $canCancel = false;
                    if (!empty($currentUser)) {
                        if ($currentUser->role === 'admin') {
                            $canCancel = !in_array($order->order_status, ['cancelled', 'delivered']);
                        } else {
                            // Customers can only cancel their orders with pending status
                            $canCancel = ($order->user_id === $currentUser->id && $order->order_status === 'pending');
                        }
                    }
                    ?>
                    <?php if ($canCancel): ?>
                    <div class="action-section">
                        <?= $this->Form->create(null, [
                            'url' => ['controller' => 'Orders', 'action' => 'cancel', $order->id],
                            'method' => 'post'
                        ]) ?>
                        <?= $this->Form->button('Cancel order', [
                            'class' => 'btn btn-danger btn-block',
                            'onclick' => 'return confirm("Do you really want to cancel this order?")'
                        ]) ?>
                        <?= $this->Form->end() ?>
                    </div>
                    <?php endif; ?>
                    
                </div>
            </div>
            
            <!-- Status Timeline -->
            <div class="card mt-3">
                <div class="card-header">
                    <h3>Order History</h3>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <strong>Order Created</strong>
                            <br>
                            <small><?= $order->created->format('d/m/Y H:i') ?></small>
                        </div>
                        
                        <?php if ($order->payment_date): ?>
                        <div class="timeline-item">
                            <strong>Payment Received</strong>
                            <br>
                            <small><?= $order->payment_date->format('d/m/Y H:i') ?></small>
                        </div>
                        <?php endif; ?>
                        
                        <div class="timeline-item">
                            <strong>Current Status</strong>
                            <br>
                            <?= getStatusBadge($order->order_status) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.order-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.order-badges .badge {
    margin-left: 10px;
}

.card {
    border: 1px solid #ddd;
    border-radius: 8px;
    margin-bottom: 20px;
}

.card-header {
    background: #f8f9fa;
    padding: 15px;
    border-bottom: 1px solid #ddd;
}

.card-header h3 {
    margin: 0;
    font-size: 18px;
}

.card-body {
    padding: 20px;
}

.badge {
    padding: 5px 10px;
    border-radius: 4px;
    font-size: 14px;
}

.badge-warning { background: #ffc107; color: #000; }
.badge-info { background: #17a2b8; color: #fff; }
.badge-primary { background: #007bff; color: #fff; }
.badge-success { background: #28a745; color: #fff; }
.badge-danger { background: #dc3545; color: #fff; }

.timeline {
    border-left: 2px solid #007bff;
    padding-left: 20px;
}

.timeline-item {
    margin-bottom: 20px;
    position: relative;
}

.timeline-item:before {
    content: '';
    position: absolute;
    left: -26px;
    width: 10px;
    height: 10px;
    background: #007bff;
    border-radius: 50%;
}

.btn-block {
    width: 100%;
}

.action-section {
    margin-bottom: 15px;
}

.action-section h4 {
    font-size: 16px;
    margin-bottom: 10px;
}
</style>