<?php
namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class PaymentLogsTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->setTable('payment_logs');
        $this->setPrimaryKey('id');
        $this->belongsTo('Orders', [
            'foreignKey' => 'order_id',
            'joinType' => 'INNER',
        ]);
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->integer('id')
            ->allowEmptyString('id', null, 'create')
            ->requirePresence('order_id', 'create')
            ->notEmptyString('order_id')
            ->requirePresence('payment_method', 'create')
            ->notEmptyString('payment_method')
            ->requirePresence('status', 'create')
            ->notEmptyString('status')
            ->requirePresence('amount', 'create')
            ->notEmptyString('amount')
            ->requirePresence('ip_address', 'create')
            ->notEmptyString('ip_address');

        return $validator;
    }
}
