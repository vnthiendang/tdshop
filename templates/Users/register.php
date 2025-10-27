
<h1>Create account</h1>
<?= $this->Form->create($user ?? null, ['url' => ['controller' => 'Users', 'action' => 'register']]) ?>

<?php if (!empty($user) && $user->getErrors()): ?>
	<div class="error-summary">
		<ul>
		<?php foreach ($user->getErrors() as $field => $errs): ?>
			<?php foreach ($errs as $rule => $msg): ?>
				<li><?= h($field . ': ' . $msg) ?></li>
			<?php endforeach; ?>
		<?php endforeach; ?>
		</ul>
	</div>
<?php endif; ?>
	<div class="form-row">
		<?= $this->Form->control('email', ['label' => 'Email', 'required' => true]) ?>
	</div>
	<div class="form-row">
		<?= $this->Form->control('password', ['label' => 'Password', 'required' => true]) ?>
	</div>
	<div class="form-row">
		<?= $this->Form->control('full_name', ['label' => 'Full Name', 'required' => true]) ?>
	</div>
	<div class="form-row">
		<?= $this->Form->control('phone', ['label' => 'Phone', 'required' => true]) ?>
	</div>
	<div class="form-row">
		<?= $this->Form->control('address', ['label' => 'Address', 'required' => true]) ?>
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
