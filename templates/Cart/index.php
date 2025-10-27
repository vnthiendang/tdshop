<div class="cart-container">
    <h1>Your Cart</h1>
    
    <?php if (!empty($cart->cart_items)): ?>
        <table class="cart-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Subtotal</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cart->cart_items as $item): ?>
                    <tr>
                        <td>
                            <div class="cart-item-info">
                                <div>
                                    <a href="/products/view/<?= $item->product->id ?>" style="color: #2c3e50; font-weight: 500;">
                                        <?= h($item->product->name) ?>
                                    </a>
                                    <div class="muted small"><?= $item->product->stock ?> items in stock</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <strong><?= number_format($item->price) ?>₫</strong>
                        </td>
                        <td>
                            <div class="quantity-input" style="display: inline-flex;">
                                <button type="button" onclick="updateQty(<?= $item->id ?>, -1)" class="button secondary">-</button>
                                <input type="number" name="quantity" id="qty-<?= $item->id ?>" 
                                    value="<?= $item->quantity ?>" 
                                    min="1" 
                                    max="<?= $item->product->stock ?>" 
                                    class="" 
                                    onchange="updateCartItem(<?= $item->id ?>, this.value)">
                                <button type="button" onclick="updateQty(<?= $item->id ?>, 1)" class="button">+</button>
                            </div>
                        </td>
                        <td>
                            <strong class="price-highlight"><?= number_format($item->subtotal) ?>₫</strong>
                        </td>
                        <td>
                            <?= $this->Form->postButton('🗑️', ['action' => 'remove', $item->id], ['confirm' => 'Remove this item?', 'class' => 'button secondary']) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <a href="/products" class="button secondary">← Continue Shopping</a>
                <?= $this->Form->postButton('Clear Cart', ['action' => 'clear'], ['confirm' => 'Clear entire cart?', 'class' => 'button secondary', 'style' => 'margin-left: 10px;']) ?>
            </div>

            <div class="cart-summary">
                <h3>Order Summary</h3>

                <div class="summary-row">
                    <span>Subtotal (<?= $cart->total_items ?> items):</span>
                    <span><?= number_format($cart->total) ?>₫</span>
                </div>

                <div class="summary-row">
                    <span>Shipping:</span>
                    <span>0,000₫</span>
                </div>

                <div class="summary-row total">
                    <span>Total:</span>
                    <span><?= number_format($cart->total) ?>₫</span>
                </div>

                <?php if ($currentUser): ?>
                    <!-- <a href="/orders/checkout" class="button" style="width: 100%; margin-top: 20px; text-align: center;">Thanh toán</a> -->
                    <a href="/orders/checkout?cart_id=<?= $cart->id ?>" class="button" style="width: 100%; margin-top: 20px; text-align: center;">Checkout</a>
                <?php else: ?>
                    <a href="/users/login?redirect=/checkout" class="button" style="width: 100%; margin-top: 20px; text-align: center;">Login to Checkout</a>
                <?php endif; ?>
            </div>
        </div>
        
    <?php else: ?>
        <div style="text-align: center; padding: 80px 20px;">
            <div style="font-size: 80px; margin-bottom: 20px;">🛒</div>
            <h2 style="margin-bottom: 10px;">Your Cart is Empty</h2>
            <p style="color: #7f8c8d; margin-bottom: 30px;">Add some products to your cart!</p>
            <a href="/products" class="btn btn-primary">Shop Now</a>
        </div>
    <?php endif; ?>
</div>

<script>
function updateQty(itemId, change) {
    const input = document.getElementById('qty-' + itemId);
    const currentQty = parseInt(input.value);
    const max = parseInt(input.max);
    const newQty = currentQty + change;
    
    if (newQty >= 1 && newQty <= max) {
        input.value = newQty;
        updateCartItem(itemId, newQty);
    }
}

function formatNumber(number) {
    return new Intl.NumberFormat('vi-VN').format(number);
}

const _cartUpdatePending = {};

function updateCartItem(itemId, quantity) {
    // prevent concurrent requests for same item
    if (_cartUpdatePending[itemId]) return;
    _cartUpdatePending[itemId] = true;
    // Hiển thị loading state nếu cần
    const formData = new FormData();
    formData.append('quantity', quantity);
    
    // Thêm CSRF token
    const csrfToken = document.querySelector('meta[name="csrfToken"]')?.content;
    
    fetch('/cart/update/' + itemId, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-Token': csrfToken
        },
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            // Update UI
            const row = document.getElementById('qty-' + itemId).closest('tr');
            const subtotalCell = row.querySelector('.price-highlight');
            if (subtotalCell) {
                subtotalCell.textContent = formatNumber(result.data.itemSubtotal) + '₫';
            }

            // Update cart totals
            const cartTotal = document.querySelector('.summary-row:first-child span:last-child');
            const cartTotalWithShipping = document.querySelector('.summary-row.total span:last-child');
            const totalItems = document.querySelector('.summary-row:first-child span:first-child');

            if (cartTotal) cartTotal.textContent = formatNumber(result.data.cartTotal) + '₫';
            if (cartTotalWithShipping) cartTotalWithShipping.textContent = formatNumber(result.data.cartTotalWithShipping) + '₫';
            if (totalItems) totalItems.textContent = `Subtotal (${result.data.totalItems} items):`;
        } else {
            alert(result.error);
            // Revert to old value if error occurs
            location.reload();
        }
        _cartUpdatePending[itemId] = false;
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while updating the cart');
        location.reload();
        _cartUpdatePending[itemId] = false;
    });
}
</script>