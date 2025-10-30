<?php
namespace App\Model\Table;

use Cake\ORM\Table;

class OrdersTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('orders');
        $this->setDisplayField('order_code');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('OrderItems', [
            'foreignKey' => 'order_id',
            'dependent' => true,
        ]);
    }

    public function createFromCart($cart, $userData, $paymentMethod)
    {
        $orderData = [
            'user_id' => $cart->user_id,
            'order_code' => 'ORD' . date('YmdHis') . rand(100, 999),
            'customer_name' => $userData['customer_name'],
            'customer_email' => $userData['customer_email'],
            'customer_phone' => $userData['customer_phone'],
            'shipping_address' => $userData['shipping_address'],
            'subtotal' => $cart->total,
            'shipping_fee' => $userData['shipping_fee'] ?? 0,
            'discount' => 0,
            'total_amount' => $cart->total + ($userData['shipping_fee'] ?? 0),
            'payment_method' => $paymentMethod,
            'payment_status' => 'pending',
            'order_status' => 'pending',
            'notes' => $userData['notes'] ?? null,
        ];

        $order = $this->newEntity($orderData);
        
        if ($this->save($order)) {
            $this->loadModel('OrderItems');
            foreach ($cart->cart_items as $cartItem) {
                $orderItem = $this->OrderItems->newEntity([
                    'order_id' => $order->id,
                    'product_id' => $cartItem->product_id,
                    'product_name' => $cartItem->product->name,
                    'product_image' => $cartItem->product->image,
                    'quantity' => $cartItem->quantity,
                    'price' => $cartItem->price,
                    'total' => $cartItem->subtotal,
                ]);
                $this->OrderItems->save($orderItem);
            }

            return $order;
        }

        return false;
    }
}
