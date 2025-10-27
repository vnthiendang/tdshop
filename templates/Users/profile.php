<div style="max-width: 800px; margin: 50px auto;">
    <div style="background: white; padding: 40px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
        <h2 style="margin-bottom: 30px;">Personal information</h2>
        
        <?= $this->Form->create($user) ?>
        
        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 500;">Email</label>
            <input type="text" 
                   value="<?= h($user->email) ?>" 
                   disabled 
                   style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px; background: #f5f7fa;">
            <small style="color: #7f8c8d;">Email cannot be changed</small>
        </div>
        
        <div style="margin-bottom: 20px;">
            <?= $this->Form->control('full_name', [
                'label' => 'Full Name',
                'required' => true,
                'style' => 'width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px;'
            ]) ?>
        </div>
        
        <div style="margin-bottom: 20px;">
            <?= $this->Form->control('phone', [
                'label' => 'Phone Number',
                'style' => 'width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px;'
            ]) ?>
        </div>
        
        <div style="margin-bottom: 20px;">
            <?= $this->Form->control('address', [
                'label' => 'Address',
                'type' => 'textarea',
                'style' => 'width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px; min-height: 100px;'
            ]) ?>
        </div>
        
        <?= $this->Form->button('Update', ['class' => 'btn btn-primary']) ?>
        <?= $this->Form->end() ?>
        
        <hr style="margin: 30px 0;">
        
        <div style="display: flex; gap: 15px;">
            <a href="/users/change-password" class="btn btn-outline">Change Password</a>
            <a href="/orders" class="btn btn-outline">My orders</a>
        </div>
    </div>
</div>