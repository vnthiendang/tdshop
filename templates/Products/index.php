<h1 class="page-title">Product List</h1>
<?php use Cake\Collection\Collection?>
<!-- Filter + Search Bar -->
<div class="filter-bar">
    <?= $this->Form->create(null, ['type' => 'get', 'class' => 'filter-form']) ?>
        <div class="filter-group">
            <?= $this->Form->control('category_id', [
                'label' => false,
                'options' => (new \Cake\Collection\Collection($categories))->combine('id', 'name')->toArray(),
                'empty' => 'All Categories',
                'default' => $this->request->getQuery('category_id'),
                'class' => 'filter-select'
            ]) ?>
        </div>
        <div class="filter-group">
            <?= $this->Form->control('search', [
                'label' => false,
                'value' => $this->request->getQuery('search'),
                'placeholder' => 'Search product name...',
                'class' => 'search-input'
            ]) ?>
        </div>
        <?= $this->Form->button('Filter', ['class' => 'filter-button']) ?>
    <?= $this->Form->end() ?>
</div>

<!-- Product Grid -->
<?php if ($products->count()): ?>
    <div class="product-grid">
        <?php foreach ($products as $product): ?>
            <div class="product-card">
                <div class="product-image">
                    <?php if (!empty($product->image)): ?>
                        <img src="/img/products/<?= h($product->image) ?>" alt="<?= h($product->name) ?>">
                    <?php else: ?>
                        <img src="/img/no-image.png" alt="No image">
                    <?php endif; ?>
                </div>
                <div class="product-info">
                    <h3><?= h($product->name) ?></h3>
                    <p class="price"><?= number_format($product->price, 0, ',', '.') ?>₫</p>

                    <?= $this->Form->create(null, [
                        'url' => ['controller' => 'Cart', 'action' => 'add'],
                        'type' => 'post',
                        'class' => 'add-to-cart-form'
                    ]) ?>
                        <?= $this->Form->hidden('product_id', ['value' => $product->id]) ?>
                        <?= $this->Form->control('quantity', [
                            'type' => 'number',
                            'min' => 1,
                            'value' => 1,
                            'label' => false,
                            'class' => 'qty-input'
                        ]) ?>
                        <?= $this->Form->button('Add to Cart', ['class' => 'add-button']) ?>
                    <?= $this->Form->end() ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="pagination">
        <?= $this->Paginator->prev('← Previous') ?>
        <?= $this->Paginator->numbers() ?>
        <?= $this->Paginator->next('Next →') ?>
    </div>
<?php else: ?>
    <p class="no-result">No products found.</p>
<?php endif; ?>

<!-- Link to cart -->
<a href="/cart" class="cart-link">🛒 View Cart & Checkout</a>

<!-- CSS Styling -->
<style>
.page-title {
    text-align: center;
    margin-bottom: 24px;
    font-size: 28px;
    color: #333;
}

.filter-bar {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 12px;
    margin-bottom: 24px;
    flex-wrap: wrap;
}

.filter-select, .search-input {
    padding: 8px 10px;
    border: 1px solid #ccc;
    border-radius: 8px;
    font-size: 15px;
}

.search-input {
    width: 200px;
}

.filter-button {
    padding: 8px 16px;
    background: #27ae60;
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    transition: background 0.2s;
}

.filter-button:hover {
    background: #219150;
}

/* Product Grid */
.product-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 20px;
}

.product-card {
    background: white;
    border: 1px solid #eee;
    border-radius: 12px;
    overflow: hidden;
    transition: transform 0.2s, box-shadow 0.2s;
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 12px;
}

.product-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.product-image img {
    width: 100%;
    height: 160px;
    object-fit: cover;
    border-radius: 8px;
}

.product-info {
    text-align: center;
    margin-top: 10px;
}

.product-info h3 {
    font-size: 16px;
    margin: 6px 0;
    color: #333;
}

.price {
    color: #e74c3c;
    font-weight: 700;
    margin-bottom: 8px;
}

.qty-input {
    width: 60px;
    padding: 4px;
    text-align: center;
    border-radius: 4px;
    border: 1px solid #ccc;
    margin-right: 6px;
}

.add-button {
    background: #27ae60;
    color: white;
    border: none;
    padding: 6px 12px;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 600;
}

.add-button:hover {
    background: #219150;
}

.pagination {
    text-align: center;
    margin-top: 20px;
}

.pagination a, .pagination span {
    display: inline-block;
    margin: 0 4px;
    padding: 6px 10px;
    text-decoration: none;
    color: #27ae60;
    border-radius: 4px;
}

.pagination .current {
    background: #27ae60;
    color: white;
    font-weight: bold;
}

.no-result {
    text-align: center;
    color: #888;
}

.cart-link {
    display: inline-block;
    margin-top: 12px;
    text-decoration: none;
    background: #2980b9;
    color: white;
    padding: 8px 14px;
    border-radius: 6px;
    font-weight: 600;
    transition: background 0.2s;
}

.cart-link:hover {
    background: #21699c;
}
</style>