<?php
namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class CartItemsTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('cart_items');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Carts', [
            'foreignKey' => 'cart_id',
        ]);
        $this->belongsTo('Products', [
            'foreignKey' => 'product_id',
        ]);
    }

    /**
     * Update item in cart
     */
    public function addOrUpdate($cartId, $productId, $quantity, $price)
    {
        $item = $this->find()
            ->where([
                'cart_id' => $cartId,
                'product_id' => $productId
            ])
            ->first();

        if ($item) {
            // update quantity
            $item->quantity += $quantity;
            $item->price = $price; // change price
        } else {
            // new
            $item = $this->newEntity([
                'cart_id' => $cartId,
                'product_id' => $productId,
                'quantity' => $quantity,
                'price' => $price,
            ]);
        }

        return $this->save($item);
    }
}
