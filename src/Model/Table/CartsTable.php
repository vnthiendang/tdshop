<?php
namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;use Cake\Log\Log;

class CartsTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('carts');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('CartItems', [
            'foreignKey' => 'cart_id',
            'dependent' => true,
        ]);
    }

    /**
     * get cart for user/session
     */
    public function getOrCreateCart($userId = null, $sessionId = null)
    {
        $conditions = [];
        if ($userId) {
            $conditions['user_id'] = $userId;
        } else {
            $conditions['session_id'] = $sessionId;
        }

        $cart = $this->find()
            ->where($conditions)
            ->contain(['CartItems' => ['Products']])
            ->first();

        if (!$cart) {
            $cart = $this->newEntity([
                'user_id' => $userId,
                'session_id' => $sessionId,
            ]);
            $this->save($cart);
            $cart->cart_items = [];
        }

        return $cart;
    }
}
