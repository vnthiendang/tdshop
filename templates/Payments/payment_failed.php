<div class="payment-result failed">
    <div class="icon">✗</div>
    <h2>Payment failed!</h2>
    
    <div class="error-info">
        <p><strong>Order code:</strong> <?= h($orderId) ?></p>
        <p><strong>Error code:</strong> <?= h($responseCode) ?></p>
        <p><strong>Reason:</strong> <?= h($message) ?></p>
    </div>
    
    <div class="actions">
        <?= $this->Html->link('Try again', ['action' => 'index'], ['class' => 'btn btn-primary']) ?>
        <?= $this->Html->link('Back to homepage', '/', ['class' => 'btn btn-secondary']) ?>
    </div>
</div>

<style>
.payment-result.failed {
    background: #f8d7da;
    border: 2px solid #dc3545;
}

.payment-result.failed .icon {
    font-size: 80px;
    color: #dc3545;
    margin-bottom: 20px;
}

.error-info {
    background: white;
    padding: 20px;
    border-radius: 5px;
    margin: 20px 0;
    text-align: left;
}

.error-info p {
    margin: 10px 0;
}
</style>