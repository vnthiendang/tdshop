<div style="max-width: 500px; margin: 50px auto; background: white; padding: 40px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
    <h2 style="text-align: center; margin-bottom: 30px;">Login</h2>
    
    <?= $this->Form->create(null, ['url' => ['controller' => 'Users', 'action' => 'login'],
    'novalidate' => true, 'autocomplete' => 'off']) ?>
    
    <div style="margin-bottom: 20px;">
        <?= $this->Form->control('email', [
            'label' => 'Email',
            'type' => 'email',
            'style' => 'width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px;'
        ]) ?>
    </div>
    
    <div style="margin-bottom: 20px;">
        <?= $this->Form->control('password', [
            'label' => 'Password',
            'type' => 'password',
            'style' => 'width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px;'
        ]) ?>
    </div>
    
    <?= $this->Form->button('Login', ['class' => 'btn btn-primary', 'style' => 'width: 100%; padding: 12px;']) ?>
    <?= $this->Form->end() ?>
</div>