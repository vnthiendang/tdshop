<?php
namespace App\Controller;
use Cake\Http\Client;
use Cake\Log\Log;
use App\Service\VNPayService;
use App\Controller\AppController;
use Cake\ORM\TableRegistry;
use Cake\Http\Exception\ForbiddenException;
use Cake\Http\Exception\NotFoundException;

class OrdersController extends AppController
{
    // HTTP client setup
    public function initialize(): void
    {
        parent::initialize();
        // Load shared payment logging component
        $this->loadComponent('PaymentLog');
    }

    public function dashboard()
    {
        // Only admins can access dashboard
        if ($deny = $this->requireAdmin()) {
            return $deny;
        }

        $stats = $this->Orders->getDashboardStats();
        $pendingOrders = $this->Orders->getPendingOrders();
        $pendingPayments = $this->Orders->getPendingPayments();

        if ($this->request->is('ajax') || $this->request->accepts('application/json')) {
            return $this->response
                ->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => true,
                    'data' => [
                        'stats' => $stats,
                        'pending_orders' => $pendingOrders,
                        'pending_payments' => $pendingPayments
                    ]
                ]));
        }

        // $this->set(compact('stats', 'pendingOrders', 'pendingPayments'));
    }

    public function index()
    {
        $user = $this->Authentication->getIdentity();
        // For test form display only
        $orders = $this->Orders->find()
            ->where(['user_id' => $user->id])
            ->order(['created' => 'DESC'])
            ->all();
        return $this->response
            ->withType('application/json')
            ->withStringBody(json_encode([
                'success' => true,
                'data' => $orders,
            ]));
        // $this->set(compact('orders'));
    }

    public function payments()
    {
        $order = $this->Orders->newEmptyEntity();
        
        if ($this->request->is('post')) {
            $data = $this->request->getData();
            $user = $this->Authentication->getIdentity();
            $order = $this->Orders->createOrderFromUser($user, $data);

            if ($order) {
                // Log order creation
                $this->PaymentLog->writeLog($order->id, $data['payment_method'], 'created', $order->total_amount, $this->request->clientIp());

                // solve payment method
                switch ($data['payment_method']) {
                    case 'cod':
                        return $this->handleCOD($order);

                    case 'bank_transfer':
                        return $this->handleBankTransfer($order);

                    case 'vnpay':
                        // Redirect to Payments controller to handle VNPay flow
                        return $this->redirect([
                            'controller' => 'Payments',
                            'action' => 'create',
                            '?' => [
                                'order_id' => $order->order_code,
                                'amount' => $order->total_amount,
                                'order_info' => 'VNPay payment',
                            ]
                        ]);

                    default:
                           $this->Flash->error('Invalid payment method!');
                        return $this->redirect(['action' => 'index']);
                }
            } else {
                //    $this->Flash->error('Could not create order!');
                return $this->response
                    ->withType('application/json')
                    ->withStatus(400)
                    ->withStringBody(json_encode([
                        'success' => false,
                        'message' => 'Could not create order!',
                    ]));
            }
        }
        
        $this->set(compact('order'));
        return $this->response
            ->withType('application/json')
            ->withStringBody(json_encode([
                'success' => true,
                'data' => $order,
            ]));
    }

    /**
     * Show checkout page (data from cart)
     */
    public function checkout()
    {
        $cartItemsTable = $this->fetchTable('CartItems');
        $cartItems = $cartItemsTable->find()
            ->where(['cart_id' => $this->request->getQuery('cart_id')])
            ->contain(['Products'])
            ->all();

        if (empty($cartItems->toArray())) {
            $this->Flash->error('Cart is empty. Please add products before checkout.');
            return $this->redirect(['controller' => 'Products', 'action' => 'index']);
        }

        $total = 0;
        foreach ($cartItems as $item) {
            $total += ($item->quantity * ($item->price ?? $item->product->price));
        }

        $order = $this->Orders->newEmptyEntity();
        $order->total_amount = $total;

        $user = $this->Authentication->getIdentity();
        if ($user) {
            $order->customer_name = $user->full_name ?? null;
            $order->customer_email = $user->email ?? null;
            $order->customer_phone = $user->phone ?? null;
        }

        $this->set(compact('cartItems', 'order', 'total'));
    }

    /**
     * Handle COD payment
     */
    private function handleCOD($order)
    {
        // COD does not require immediate payment

        // Send confirmation email (optional)
        $this->sendOrderConfirmationEmail($order);
        return $this->response
            ->withType('application/json')
            ->withStringBody(json_encode([
                'success' => true,
                'message' => 'Order created! Payment will be collected on delivery.',
                'data' => ['order_id' => $order->id],
            ]));
    }
    
    /**
     * Handle bank transfer payment
     */
    private function handleBankTransfer($order)
    {
        return $this->response
            ->withType('application/json')
            ->withStringBody(json_encode([
                'success' => true,
                'message' => 'Order created! Please transfer using the provided bank account.',
                'data' => ['order_id' => $order->id],
            ]));
    }
    
    /**
     * View order details
     */
    public function view($id = null)
    {
        $user = $this->Authentication->getIdentity();
        $order = $this->Orders->get(
            primaryKey: $id,
            options: ['contain' => ['OrderItems']]
        );
        if ($order->user_id != $user->id && $user->role != 'admin') {
            throw new ForbiddenException('You do not have permission to view this order!');
        }
        // get order items
        $orderItems = $this->Orders->OrderItems->find()
            ->where(['order_id' => $order->id])
            ->all();
        return $this->response
            ->withType('application/json')
            ->withStringBody(json_encode([
                'success' => true,
                'data' => [
                    'order' => $order,
                    'order_items' => $orderItems
                ],
            ]));
    }
    
    /**
     * Show bank transfer information
     */
    public function bankTransferInfo($id = null)
    {
        // $user = $this->Authentication->getIdentity();
        $order = $this->Orders->get($id);
        
        if ($order->payment_method !== 'bank_transfer') {
            throw new NotFoundException('Order is not a bank transfer payment');
        }
        
        // Bank account information
        $bankInfo = [
            'bank_name' => 'Vietcombank',
            'account_number' => '1234567890',
            'account_name' => 'CONG TY ABC',
            'transfer_content' => 'TT ' . $order->order_code,
        ];
        
        return $this->response
            ->withType('application/json')
            ->withStringBody(json_encode([
                'success' => true,
                'data' => [
                    'order' => $order,
                    'bank_info' => $bankInfo
                ],
            ]));
    }
    
        
    /**
     * Update order status
     */
    public function updateStatus($id = null)
    {
        $this->request->allowMethod(['post']);
        if ($deny = $this->requireAdmin()) {
            return $deny;
        }

        try {
            $order = $this->Orders->updateStatus($id, $this->request->getData('order_status'));
            return $this->response
                ->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => true,
                    'message' => 'Order status updated',
                    'data' => ['order' => $order]
                ]));
        } catch (RecordNotFoundException) {
            return $this->response
                ->withType('application/json')
                ->withStatus(404)
                ->withStringBody(json_encode([
                    'success' => false,
                    'message' => 'Order not found',
                ]));
        } catch (\Throwable $e) {
            return $this->response
                ->withType('application/json')
                ->withStatus(500)
                ->withStringBody(json_encode([
                    'success' => false,
                    'message' => 'Update failed: ' . $e->getMessage(),
                ]));
        }
    }
    public function cancel($id)
    {
        $this->request->allowMethod(['post']);
        $user = $this->Authentication->getIdentity();

        try {
            $order = $this->Orders->cancelOrder($id, $user);
            return $this->response
                ->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => true,
                    'message' => 'Order cancelled successfully',
                    'data' => ['order' => $order]
                ]));
        } catch (\Throwable $e) {
            return $this->response
                ->withType('application/json')
                ->withStatus(400)
                ->withStringBody(json_encode([
                    'success' => false,
                    'message' => 'Cancellation failed: ' . $e->getMessage(),
                ]));
        }
    }
    
    /**
     * Helper: Send emails (implement based on your email service)
     */
    private function sendOrderConfirmationEmail($order)
    {
        // TODO: Implement email sending
        Log::write('info', "Email sent - Order confirmation: " . $order->order_code);
    }
    
    private function sendPaymentConfirmedEmail($order)
    {
        // TODO: Implement email sending
        Log::write('info', "Email sent - Payment confirmed: " . $order->order_code);
    }
    
    private function sendStatusUpdateEmail($order)
    {
        // TODO: Implement email sending
        Log::write('info', "Email sent - Status update: " . $order->order_code);
    }
}