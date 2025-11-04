
<h1>Create account</h1>
<?= $this->Form->create($user ?? null, ['url' => ['controller' => 'Users', 'action' => 'register'],
'novalidate' => true, 'autocomplete' => 'off']) ?>

	<div class="form-row">
		<?= $this->Form->control('email', ['label' => 'Email']) ?>
	</div>
	<div class="form-row">
		<?= $this->Form->control('password', ['label' => 'Password']) ?>
	</div>
	<div class="form-row">
		<?= $this->Form->control('full_name', ['label' => 'Full Name']) ?>
	</div>
	<div class="form-row">
		<?= $this->Form->control('phone', ['label' => 'Phone']) ?>
	</div>
	<div class="form-row">
		<?= $this->Form->control('address', ['label' => 'Address']) ?>
	</div>
	<div class="form-row">
		<?= $this->Form->control('role', [
			'label' => 'Role',
			'type' => 'select',
			'options' => ['customer' => 'User', 'admin' => 'Admin'],
			'required' => true
		]) ?>
	</div>
	<div class="form-row">
		<?= $this->Form->button('Register', ['class' => 'button']) ?>
	</div>
<?= $this->Form->end() ?>
