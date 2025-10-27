<div class="orders-create">
    <h2>Tạo đơn hàng mới</h2>
    
    <?= $this->Form->create($order) ?>
    
    <fieldset>
        <legend>Thông tin khách hàng</legend>
        
        <?= $this->Form->control('customer_name', [
            'label' => 'Họ tên',
            'required' => true
        ]) ?>
        
        <?= $this->Form->control('customer_phone', [
            'label' => 'Số điện thoại',
            'required' => true
        ]) ?>
        
        <?= $this->Form->control('customer_email', [
            'label' => 'Email',
            'type' => 'email'
        ]) ?>
        
        <?= $this->Form->control('shipping_address', [
            'label' => 'Địa chỉ giao hàng',
            'type' => 'textarea',
            'required' => true
        ]) ?>
    </fieldset>
    
    <fieldset>
        <legend>Thông tin đơn hàng</legend>
        
        <?= $this->Form->control('total_amount', [
            'label' => 'Tổng tiền (VND)',
            'type' => 'number',
            'required' => true
        ]) ?>
        
        <?= $this->Form->control('notes', [
            'label' => 'Ghi chú',
            'type' => 'textarea'
        ]) ?>
    </fieldset>
    
    <fieldset>
        <legend>Phương thức thanh toán</legend>
        <p>Vui lòng chọn phương thức thanh toán phù hợp với bạn:</p>
        
        <?= $this->Form->radio('payment_method', [
            ['value' => 'cod', 'text' => '💵 COD - Thanh toán khi nhận hàng'],
            ['value' => 'bank_transfer', 'text' => '🏦 Chuyển khoản ngân hàng'],
            ['value' => 'vnpay', 'text' => '💳 VNPay (ATM/QR)'],
            ['value' => 'momo', 'text' => '📱 Ví MoMo'],
        ], [
            'value' => 'cod',
            'legend' => false
        ]) ?>
    </fieldset>
    
    <?= $this->Form->button('Tạo đơn hàng', ['class' => 'btn-primary']) ?>
    <?= $this->Form->end() ?>
</div>