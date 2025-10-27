<h1>Checkout</h1>
<?php if (empty($cart)): ?>
    <p>Your cart is empty.</p>
<?php else: ?>
    <p>Thank you for your order!</p>
    <table border="1" cellpadding="8">
        <tr>
            <th>Name</th>
            <th>Price</th>
            <th>Quantity</th>
            <th>Subtotal</th>
        </tr>
        <?php $total = 0; ?>
        <?php foreach ($cart as $item): ?>
            <tr>
                <td><?= h($item['name']) ?></td>
                <td><?= number_format($item['price'], 0, ',', '.') ?> VND</td>
                <td><?= $item['quantity'] ?></td>
                <td><?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?> VND</td>
            </tr>
            <?php $total += $item['price'] * $item['quantity']; ?>
        <?php endforeach; ?>
        <tr>
            <td colspan="3"><strong>Total</strong></td>
            <td><strong><?= number_format($total, 0, ',', '.') ?> VND</strong></td>
        </tr>
    </table>
    <p><a href="/products">Continue Shopping</a></p>
<?php endif; ?>
