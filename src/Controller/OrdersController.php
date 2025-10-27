<?php
namespace App\Controller;
use Cake\Http\Client;
use Cake\Log\Log;
use App\Service\VNPayService;
use App\Controller\AppController;
use Cake\ORM\TableRegistry;
use Cake\Http\Exception\ForbiddenException;

class OrdersController extends AppController
{
    // HTTP client setup
    public function initialize(): void
    {
        parent::initialize();
        // $this->httpClient = new Client();
        $this->Orders = TableRegistry::getTableLocator()->get('Orders');
        $this->PaymentLogs = TableRegistry::getTableLocator()->get('PaymentLogs');
        $this->Carts = TableRegistry::getTableLocator()->get('Carts');
        // Load shared payment logging component
        $this->loadComponent('PaymentLog');
    }

    public function dashboard()
    {
        // Only admins can access dashboard
        $deny = $this->requireAdmin();
        if ($deny) {
            return $deny;
        }
    // Overview statistics
        $stats = [
            'total_orders' => $this->Orders->find()->count(),
            'pending_orders' => $this->Orders->find()->where(['order_status' => 'pending'])->count(),
            'paid_orders' => $this->Orders->find()->where(['payment_status' => 'paid'])->count(),
            'total_revenue' => $this->Orders->find()
                ->where(['payment_status' => 'paid'])
                ->select(['total' => 'SUM(total_amount)'])
                ->first()
                ->total ?? 0,
            'today_orders' => $this->Orders->find()
                ->where(['DATE(created)' => date('Y-m-d')])
                ->count(),
        ];
        
        // Orders needing processing
        $pendingOrders = $this->Orders->find()
            ->where(['order_status IN' => ['pending', 'confirmed']])
            ->order(['created' => 'DESC'])
            ->limit(10)
            ->all();
        
        // Orders pending payment confirmation
        $pendingPayments = $this->Orders->find()
            ->where([
                'payment_status' => 'pending',
                'payment_proof IS NOT' => null
            ])
            ->order(['modified' => 'DESC'])
            ->limit(10)
            ->all();
        
        $this->set(compact('stats', 'pendingOrders', 'pendingPayments'));
    }

    public function index()
    {
        $user = $this->Authentication->getIdentity();
        // For test form display only
        $orders = $this->Orders->find()
            ->where(['user_id' => $user->id])
            ->order(['created' => 'DESC'])
            ->all();
        
        $this->set(compact('orders'));
    }

    public function createCod()
    {
        $order = $this->Orders->newEmptyEntity();
        
        if ($this->request->is('post')) {
            $data = $this->request->getData();
            $user = $this->Authentication->getIdentity();
            $data['user_id'] = $user->id;
            Log::error('Creating order with data: ' . json_encode($data));
            
            // Generate unique order code
            $data['order_code'] = 'ORD' . date('YmdHis') . rand(100, 999);

            // Default statuses
            $data['order_status'] = 'pending';
            $data['payment_status'] = 'pending';
            $data['subtotal'] = $data['total_amount'];

            // If VNPay selected, normalize data for redirect
            if ($data['payment_method'] === 'vnpay') {
                $data['order_info'] = 'VNPay payment';
                $data['amount'] = $data['total_amount'] ?? 100000;
                return $this->redirect([
                    'controller' => 'Payments',
                    'action' => 'create',
                    '?' => [
                        'order_id' => $data['order_code'] ?? null,
                        'amount' => $data['total_amount'] ?? null,
                        'order_info' => $data['order_info'],
                    ]
                ]);
            }

            $order = $this->Orders->patchEntity($order, $data);

            if ($this->Orders->save($order)) {
                // del cart by user id
                $this->Carts->deleteAll(['user_id' => $user->id]);
                // Log order creation
                $this->PaymentLog->writeLog($order->id, $data['payment_method'], 'created', $order->total_amount, $this->request->clientIp());

                // Xử lý theo phương thức thanh toán
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
                   $this->Flash->error('Could not create order!');
            }
        }
        
        $this->set(compact('order'));
    }

    /**
     * Show checkout page (data from cart)
     */
    public function checkout()
    {
        $sessionId = $this->request->getSession()->id();
        $cartItemsTable = TableRegistry::getTableLocator()->get('CartItems');
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
     * callback from VNPay (return_url)
     */
    public function vnpayReturn()
    {
        // Forward to Payments controller (route should be updated to Payments)
        return $this->redirect([
            'controller' => 'Payments',
            'action' => 'vnpayReturn',
            '?' => $this->request->getQueryParams()
        ]);
    }
    
    /**
     * VNPay IPN webhook (to confirm payments)
     * VNPay will call this URL to report results
     */
    public function vnpayIpn()
    {
        // Forward to Payments controller IPN handler
        return $this->redirect([
            'controller' => 'Payments',
            'action' => 'vnpayIpn',
            '?' => $this->request->getQueryParams()
        ]);
    }

    /**
     * Handle COD payment
     */
    private function handleCOD($order)
    {
        // COD does not require immediate payment
        // Just confirm the order
        $this->Flash->success('Order created! Payment will be collected on delivery.');

        // Send confirmation email (optional)
        $this->sendOrderConfirmationEmail($order);

        return $this->redirect(['action' => 'view', $order->id]);
    }
    
    /**
     * Handle bank transfer payment
     */
    private function handleBankTransfer($order)
    {
        // Show bank transfer information
        $this->Flash->info('Please transfer using the account below and upload the proof.');

        return $this->redirect(['action' => 'bankTransferInfo', $order->id]);
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
        $this->set(compact('order'));
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
        
        $this->set(compact('order', 'bankInfo'));
    }
    
        
    /**
     * Update order status
     */
    public function updateStatus($id = null)
    {
        $this->request->allowMethod(['post']);
        // Require admin
        $deny = $this->requireAdmin();
        if ($deny) {
            return $deny;
        }

        $order = $this->Orders->get($id);
        $newStatus = $this->request->getData('order_status');

        $order->order_status = $newStatus;

        if ($this->Orders->save($order)) {
            $this->Flash->success('Order status updated successfully!');

            // Send notification email
            $this->sendStatusUpdateEmail($order);
        } else {
            $this->Flash->error('Could not update status!');
        }

        return $this->redirect(['action' => 'view', $id]);
    }

    public function cancel($id = null)
    {
        $this->request->allowMethod(['post']);
        
        $user = $this->Authentication->getIdentity();
        $order = $this->Orders->get($id);
        
        if ($user->role === 'admin') {
            $canCancel = true;
        } else {
            if ($order->user_id != $user->id) {
                throw new ForbiddenException('You do not have permission to cancel this order!');
            }
            
            $canCancel = ($order->order_status === 'pending');
        }
        
        if (!$canCancel) {
            $this->Flash->error('Cannot cancel order in the current status!');
            return $this->redirect(['action' => 'view', $id]);
        }
        
        $order->order_status = 'cancelled';
        // $order->payment_status = 'failed';
        
        if ($this->Orders->save($order)) {
            $this->Flash->success('Order has been cancelled!');

            // Log action
            $actorRole = $user->role === 'admin' ? 'Admin' : 'Customer';
            Log::write('info', "$actorRole cancelled order #{$order->order_code}");
        } else {
            $this->Flash->error('Could not cancel order!');
        }
        
        return $this->redirect(['action' => 'view', $id]);
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