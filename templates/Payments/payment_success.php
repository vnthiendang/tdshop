<div class="payment-result success">
    <div class="icon">✓</div>
    <h2>Payment success!</h2>
    
    <div class="transaction-info">
        <p><strong>Order code:</strong> <?= h($orderId) ?></p>
        <p><strong>Amount:</strong> <?= number_format($amount) ?> VND</p>
        <p><strong>Bank:</strong> <?= h($bankCode) ?></p>
        <p><strong>Transaction code:</strong> <?= h($transactionNo) ?></p>
    </div>
    
    <div class="actions">
        <?= $this->Html->link('Continue order', ['action' => 'index'], ['class' => 'btn btn-primary']) ?>
        <?= $this->Html->link('View order', `/orders/view/$orderId`, ['class' => 'btn btn-secondary']) ?>
    </div>
</div>

<style>
.payment-result {
    max-width: 600px;
    margin: 50px auto;
    padding: 40px;
    text-align: center;
    border-radius: 8px;
}

.payment-result.success {
    background: #d4edda;
    border: 2px solid #28a745;
}

.payment-result .icon {
    font-size: 80px;
    color: #28a745;
    margin-bottom: 20px;
}

.transaction-info {
    background: white;
    padding: 20px;
    border-radius: 5px;
    margin: 20px 0;
    text-align: left;
}

.transaction-info p {
    margin: 10px 0;
}

.actions {
    margin-top: 30px;
}

.btn {
    display: inline-block;
    padding: 10px 30px;
    margin: 0 10px;
    border-radius: 5px;
    text-decoration: none;
}

.btn-primary {
    background: #007bff;
    color: white;
}

.btn-secondary {
    background: #6c757d;
    color: white;
}
</style>