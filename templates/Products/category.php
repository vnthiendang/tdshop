<h1 style="margin-bottom:16px;">Product Categories</h1>

<div style="display:flex;gap:24px;align-items:flex-start;">

    <!-- Sidebar: Categories -->
    <div style="width:220px;border-right:1px solid #ddd;padding-right:16px;">
        <h3 style="margin-bottom:12px;">Categories</h3>
        <ul style="list-style:none;padding:0;margin:0;">
            <li style="margin-bottom:6px;">
                <a href="/products/category" 
                   style="<?= empty($selectedCategory) ? 'font-weight:bold;color:#27ae60;' : 'color:#333;' ?>">
                   All Products
                </a>
            </li>
            <?php foreach ($categories as $cat): ?>
                <li style="margin-bottom:6px;">
                    <a href="/products/category/<?= h($cat->slug) ?>"
                       style="<?= (!empty($selectedCategory) && $selectedCategory->id === $cat->id) ? 'font-weight:bold;color:#27ae60;' : 'color:#333;' ?>">
                        <?= h($cat->name) ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <!-- Main content: Product list -->
    <div style="flex:1;">
        <?php if (!empty($selectedCategory)): ?>
            <h2 style="margin-bottom:12px;"><?= h($selectedCategory->name) ?></h2>
        <?php else: ?>
            <h2 style="margin-bottom:12px;">All Products</h2>
        <?php endif; ?>

        <?php if (!$products->isEmpty()): ?>
            <div style="display:flex;gap:16px;flex-wrap:wrap;">
                <?php foreach ($products as $product): ?>
                    <div style="width:200px;border:1px solid #eee;padding:12px;border-radius:8px;">
                        <div style="margin-top:8px;font-weight:600;"><?= h($product->name) ?></div>
                        <div style="color:#e74c3c;font-weight:700;margin-top:6px;">
                            <?= number_format($product->price) ?>₫
                        </div>
                        <a href="/products/view/<?= $product->id ?>" 
                           class="button" 
                           style="display:block;text-align:center;margin-top:8px;background:#27ae60;color:#fff;padding:6px 0;border-radius:4px;text-decoration:none;">
                           View
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>No products found in this category.</p>
        <?php endif; ?>
    </div>
</div>
