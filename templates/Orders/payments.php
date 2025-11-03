<div class="orders-create">
    <h2>Create New Order</h2>
    
    <?= $this->Form->create($order, ['class' => 'order-form']) ?>
    
    <fieldset>
        <legend>Customer Information</legend>
        
        <?= $this->Form->control('customer_name', [
            'label' => 'Full Name',
            'required' => true,
            'class' => 'form-input'
        ]) ?>
        
        <?= $this->Form->control('customer_phone', [
            'label' => 'Phone Number',
            'required' => true,
            'class' => 'form-input'
        ]) ?>
        
        <?= $this->Form->control('customer_email', [
            'label' => 'Email',
            'type' => 'email',
            'class' => 'form-input'
        ]) ?>
        
        <?= $this->Form->control('shipping_address', [
            'label' => 'Shipping Address',
            'type' => 'textarea',
            'required' => true,
            'class' => 'form-textarea'
        ]) ?>
    </fieldset>
    
    <fieldset>
        <legend>Order Details</legend>
        
        <?= $this->Form->control('total_amount', [
            'label' => 'Total Amount (VND)',
            'type' => 'number',
            'required' => true,
            'class' => 'form-input'
        ]) ?>
        
        <?= $this->Form->control('notes', [
            'label' => 'Notes',
            'type' => 'textarea',
            'class' => 'form-textarea'
        ]) ?>
    </fieldset>
    
    <fieldset>
        <legend>Payment Method</legend>
        <p>Please select your preferred payment method:</p>
        
        <?= $this->Form->radio('payment_method', [
            ['value' => 'cod', 'text' => '💵 COD - Cash on Delivery'],
            ['value' => 'bank_transfer', 'text' => '🏦 Bank Transfer'],
            ['value' => 'vnpay', 'text' => '💳 VNPay (ATM/QR)'],
        ], [
            'value' => 'cod',
            'legend' => false,
            'class' => 'radio-group'
        ]) ?>
    </fieldset>
    
    <?= $this->Form->button('Create Order', ['class' => 'btn-primary']) ?>
    <?= $this->Form->end() ?>
</div>
<style>
.orders-create {
    max-width: 700px;
    margin: 40px auto;
    padding: 30px 40px;
    background: #fff;
    border-radius: 20px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    font-family: 'Inter', sans-serif;
}

.orders-create h2 {
    text-align: center;
    font-size: 1.8rem;
    color: #222;
    margin-bottom: 1.5rem;
}

fieldset {
    border: none;
    margin-bottom: 2rem;
}

legend {
    font-size: 1.2rem;
    font-weight: 600;
    color: #444;
    margin-bottom: 0.8rem;
}

.form-input,
.form-textarea {
    width: 100%;
    padding: 12px 14px;
    margin-bottom: 1rem;
    border: 1px solid #ddd;
    border-radius: 10px;
    font-size: 0.95rem;
    transition: all 0.2s ease;
}

.form-input:focus,
.form-textarea:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 3px rgba(0,123,255,0.1);
    outline: none;
}

.radio-group label {
    display: flex;
    align-items: center;
    gap: 8px;
    margin: 8px 0;
    font-size: 0.95rem;
    cursor: pointer;
}

.radio-group input[type="radio"] {
    accent-color: #007bff;
    transform: scale(1.1);
}

.btn-primary {
    display: inline-block;
    background: linear-gradient(135deg, #007bff, #00b4d8);
    color: #fff;
    padding: 12px 24px;
    border: none;
    border-radius: 12px;
    font-size: 1rem;
    cursor: pointer;
    transition: all 0.3s ease;
    width: 100%;
    text-transform: uppercase;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #0056d2, #0096c7);
    transform: translateY(-2px);
}

p {
    font-size: 0.9rem;
    color: #666;
    margin-bottom: 0.5rem;
}
</style>