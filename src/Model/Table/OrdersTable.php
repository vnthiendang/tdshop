<?php
namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Log\Log;

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
            'cascadeCallbacks' => true,
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

    public function createOrder(array $data)
    {
        $order = $this->newEntity($data);
        if (!$this->save($order)) {
            throw new \RuntimeException('Unable to create order');
        }
        return $order;
    }

    public function updateStatus($orderId, $status)
    {
        $order = $this->get($orderId);
        $order->order_status = $status;
        if (!$this->save($order)) {
            throw new \RuntimeException('Failed to update order status');
        }
        return $order;
    }

    /**
     * Create order from user cart
     */
    public function createOrderFromUser($user, array $data)
    {
        $data['user_id'] = $user->id;

        $data['order_code'] = 'ORD' . date('YmdHis') . rand(100, 999);

        $data['order_status'] = 'pending';
        $data['payment_status'] = 'pending';
        $data['subtotal'] = $data['total_amount'] ?? 0;

        $order = $this->newEntity($data);

        if (!$this->save($order)) {
            Log::error('Failed to save order: ' . json_encode($order->getErrors()));
            throw new \RuntimeException('Cannot create order.');
        }

        // === get user cart ===
        $cartsTable = TableRegistry::getTableLocator()->get('Carts');
        $cart = $cartsTable->find()
            ->where(['user_id' => $user->id])
            ->contain(['CartItems.Products'])
            ->first();

        if (!$cart) {
            throw new \RuntimeException('Cart not found for user.');
        }

        Log::error('Cart data: ' . json_encode($cart));

        // === save cart to order_items ===
        $orderItemsTable = TableRegistry::getTableLocator()->get('OrderItems');

        foreach ($cart->cart_items as $cartItem) {
            $orderItem = $orderItemsTable->newEntity([
                'order_id' => $order->id,
                'product_id' => $cartItem->product_id,
                'product_name' => $cartItem->product->name ?? '',
                'product_image' => $cartItem->product->image ?? '',
                'quantity' => $cartItem->quantity,
                'price' => $cartItem->price,
                'total' => $cartItem->quantity * $cartItem->price,
            ]);

            if (!$orderItemsTable->save($orderItem)) {
                Log::error('Failed to save order item: ' . json_encode($orderItem->getErrors()));
            } else {
                Log::error('Saved order item successfully: ' . json_encode($orderItem));
            }
        }

        $cartsTable->deleteAll(['user_id' => $user->id]);

        return $order;
    }

    public function cancelOrder($orderId, $user)
    {
        $order = $this->get($orderId);
        if ($order->order_status !== 'pending' && $user->role !== 'admin') {
            throw new \RuntimeException('Cannot cancel this order');
        }
        $order->order_status = 'cancelled';
        if (!$this->save($order)) {
            throw new \RuntimeException('Failed to cancel order');
        }
        return $order;
    }
    
    /**
     * dashboard
     */
    public function getDashboardStats(): array
    {
        $stats = [
            'total_orders' => $this->find()->count(),
            'pending_orders' => $this->find()->where(['order_status' => 'pending'])->count(),
            'paid_orders' => $this->find()->where(['payment_status' => 'paid'])->count(),
            'total_revenue' => (float)$this->find()
                ->where(['payment_status' => 'paid'])
                ->select(['total' => 'SUM(total_amount)'])
                ->first()
                ->total ?? 0,
            'today_orders' => $this->find()
                ->where(['DATE(created)' => date('Y-m-d')])
                ->count(),
        ];

        return $stats;
    }

    /**
     * get pending orders list
     */
    public function getPendingOrders(int $limit = 10)
    {
        return $this->find()
            ->where(['order_status IN' => ['pending', 'confirmed']])
            ->order(['created' => 'DESC'])
            ->limit($limit)
            ->all();
    }

    /**
     * get waiting list of confirm payments
     */
    public function getPendingPayments(int $limit = 10)
    {
        return $this->find()
            ->where(['payment_status' => 'pending', 'payment_proof IS NOT' => null])
            ->order(['modified' => 'DESC'])
            ->limit($limit)
            ->all();
    }
}
