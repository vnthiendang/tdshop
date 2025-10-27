<div class="bank-transfer container">
    <h1>Bank transfer instructions</h1>

    <p>Please transfer the total amount to the following account and upload the proof using the form below.</p>

    <ul>
        <li><strong>Bank:</strong> <?= h($bankInfo['bank_name']) ?></li>
        <li><strong>Account number:</strong> <?= h($bankInfo['account_number']) ?></li>
        <li><strong>Account name:</strong> <?= h($bankInfo['account_name']) ?></li>
        <li><strong>Transfer content:</strong> <?= h($bankInfo['transfer_content']) ?></li>
    </ul>

    <h3>Upload transfer proof</h3>
    <?= $this->Form->create(null, ['type' => 'file', 'url' => ['controller' => 'Payments', 'action' => 'uploadPaymentProof', $order->id]]) ?>
        <?= $this->Form->control('payment_proof', ['type' => 'file', 'label' => 'Proof image']) ?>
        <?= $this->Form->button('Upload', ['class' => 'button']) ?>
    <?= $this->Form->end() ?>
</div>
