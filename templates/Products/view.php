<div class="product-page container">
    <div class="product-main" style="display:flex;gap:24px;align-items:flex-start;">
        <div class="product-gallery" style="flex:1;max-width:420px;">
            <?php $primary = null; ?>
            <?php if (!empty($product->product_images)): ?>
                <?php foreach ($product->product_images as $img): ?>
                    <?php if ($img->is_primary) { $primary = $img; break; } ?>
                <?php endforeach; ?>
                <?php if (!$primary) { $primary = $product->product_images[0]; } ?>
                <img src="/img/products/<?= h($primary->file_name) ?>" alt="<?= h($product->name) ?>" style="width:100%;border-radius:8px;" onerror="this.src='/img/placeholder.jpg'">
            <?php else: ?>
                <img src="/img/placeholder.jpg" alt="<?= h($product->name) ?>" style="width:100%;border-radius:8px;">
            <?php endif; ?>
        </div>

        <div class="product-info" style="flex:1.2;">
            <h1><?= h($product->name) ?></h1>
            <div class="price" style="font-size:24px;font-weight:700;color:#e74c3c;"><?= number_format($product->price) ?>₫</div>
            <div class="stock" style="margin-top:8px;color:#7f8c8d;">Stock: <?= $product->stock ?></div>
            <div class="description" style="margin-top:16px;"><?= nl2br(h($product->description)) ?></div>

            <div style="margin-top:20px;">
                <?= $this->Form->create(null, ['url' => ['controller' => 'Cart', 'action' => 'add']]) ?>
                    <?= $this->Form->hidden('product_id', ['value' => $product->id]) ?>
                    <?= $this->Form->control('quantity', ['type' => 'number', 'min' => 1, 'value' => 1, 'label' => 'Quantity', 'style' => 'width:100px;display:inline-block;margin-right:12px;']) ?>
                    <?= $this->Form->button('Add to Cart', ['class' => 'button']) ?>
                <?= $this->Form->end() ?>
            </div>
        </div>
    </div>

    <hr style="margin:24px 0;">

    <h3>Related products</h3>
    <div style="display:flex;gap:16px;flex-wrap:wrap;">
        <?php foreach ($relatedProducts as $rp): ?>
            <div style="width:200px;border:1px solid #eee;padding:12px;border-radius:8px;">
                <?php $img = $rp->product_images[0] ?? null; ?>
                <!-- <img src="/img/products/<?= h($img->file_name ?? 'placeholder.jpg') ?>" alt="<?= h($rp->name) ?>" style="width:100%;height:120px;object-fit:cover;border-radius:6px;" onerror="this.src='/img/placeholder.jpg'"> -->
                <div style="margin-top:8px;font-weight:600;"><?= h($rp->name) ?></div>
                <div style="color:#e74c3c;font-weight:700;margin-top:6px;"><?= number_format($rp->price) ?>₫</div>
                <a href="/products/view/<?= $rp->id ?>" class="button" style="display:block;text-align:center;margin-top:8px;">View</a>
            </div>
        <?php endforeach; ?>
    </div>
</div>
