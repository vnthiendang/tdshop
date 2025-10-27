<h1>Product List</h1>
<table class="product-table">
    <tr>
        <th>Name</th>
        <th>Price</th>
        <th>Action</th>
    </tr>
    <?php foreach ($products as $product): ?>
    <tr>
        <td><?= h($product->name) ?></td>
        <td><?= number_format($product->price, 0, ',', '.') ?> VND</td>
        <td>
            <?= $this->Form->create(null, [
                'url' => ['controller' => 'Cart', 'action' => 'add'],
                'type' => 'post',
                'class' => 'add-to-cart-form',
            ]) ?>
                <?= $this->Form->hidden('product_id', ['value' => $product->id]) ?>
                <?= $this->Form->control('quantity', [
                    'type' => 'number',
                    'min' => 1,
                    'value' => 1,
                    'label' => 'Quantity',
                    'style' => 'width:80px;display:inline-block;margin-right:8px;'
                ]) ?>
                <?= $this->Form->button('Add to Cart', ['class' => 'button']) ?>
            <?= $this->Form->end() ?>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
<a href="/cart">View Cart & Checkout</a>
