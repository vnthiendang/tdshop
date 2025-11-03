<div class="checkout container">
    <h1>Checkout</h1>

    <h3>Items</h3>
    <table class="product-table">
        <tr>
            <th>Product</th>
            <th>Price</th>
            <th>Qty</th>
            <th>Subtotal</th>
        </tr>
        <?php foreach ($cartItems as $item): ?>
        <tr>
            <td><?= h($item->product->name) ?></td>
            <td><?= number_format($item->product->price) ?>₫</td>
            <td><?= $item->quantity ?></td>
            <td><?= number_format($item->product->price * $item->quantity) ?>₫</td>
        </tr>
        <?php endforeach; ?>
        <tr>
            <td colspan="3"><strong>Total</strong></td>
            <td><strong><?= number_format($total) ?>₫</strong></td>
        </tr>
    </table>

    <h3>Billing & Shipping</h3>
    <?= $this->Form->create($order, ['url' => ['controller' => 'Orders', 'action' => 'payments']]) ?>
        <div class="form-row">
            <?= $this->Form->control('customer_name', ['label' => 'Full name']) ?>
        </div>
        <div class="form-row">
            <?= $this->Form->control('customer_phone', ['label' => 'Phone']) ?>
        </div>
        <div class="form-row">
            <?= $this->Form->control('customer_email', ['label' => 'Email']) ?>
        </div>
        <div class="form-row">
            <?= $this->Form->control('shipping_address', ['type' => 'textarea', 'label' => 'Shipping address']) ?>
        </div>

        <?= $this->Form->hidden('total_amount', ['value' => $total]) ?>

        <div class="form-row">
            <legend>Payment method</legend>
            <?= $this->Form->radio('payment_method', [
                ['value' => 'cod', 'text' => 'COD - Pay on delivery'],
                ['value' => 'bank_transfer', 'text' => 'Bank transfer'],
                ['value' => 'vnpay', 'text' => 'VNPay (ATM/QR)'],
            ], ['value' => 'cod', 'legend' => false]) ?>
        </div>

        <div class="form-row">
            <?= $this->Form->button('Place order', ['class' => 'button']) ?>
        </div>
    <?= $this->Form->end() ?>
</div>
