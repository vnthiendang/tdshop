<?php
namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Datasource\ConnectionManager;
use Cake\Log\Log;

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
        $connection = ConnectionManager::get('default');

        return $connection->transactional(function ($conn) use ($cartId, $productId, $quantity, $price) {
            try {
                // get existing item
                $item = $this->find()
                    ->where([
                        'cart_id' => $cartId,
                        'product_id' => $productId
                    ])
                    ->first();

                if ($item) {
                    $item->quantity += $quantity;
                    $item->price = $price;
                } else {
                    $item = $this->newEntity([
                        'cart_id' => $cartId,
                        'product_id' => $productId,
                        'quantity' => $quantity,
                        'price' => $price,
                    ]);
                }

                if (!$this->save($item)) {
                    throw new \RuntimeException('Failed to save cart item.');
                }

                return $item;
            } catch (\Throwable $e) {
                Log::error("CartItems::addOrUpdate failed: " . $e->getMessage());
                // auto rollback by transactional()
                throw $e;
            }
        });
    }

    public function getItemWithProduct($itemId)
    {
        return $this->find()
            ->where(['CartItems.id' => $itemId])
            ->contain(['Products'])
            ->firstOrFail();
    }

    public function updateQuantity($item, int $quantity)
    {
        $item->quantity = $quantity;
        return $this->save($item);
    }
}
