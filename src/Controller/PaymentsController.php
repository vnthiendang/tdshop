<?php
namespace App\Controller;

use App\Service\VNPayService;
use Cake\ORM\TableRegistry;
use Cake\Log\Log;
use Cake\Http\Exception\NotFoundException;

class PaymentsController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
        $this->Orders = TableRegistry::getTableLocator()->get('Orders');
        $this->loadComponent('PaymentLog');
    }

    /**
     * Create payment (redirect to VNPay)
     */
    public function create()
    {
        $data = $this->request->is('post') ? $this->request->getData() : $this->request->getQuery();

        $orderId = $data['order_id'] ?? ('ORDER_' . time());
        $amount = $data['amount'] ?? 100000;
        $orderInfo = $data['order_info'] ?? 'Order payment';

        $ipAddr = $this->request->clientIp();
        if ($ipAddr === '::1') {
            $ipAddr = '127.0.0.1';
        }

        $vnpayService = new VNPayService();
        $paymentUrl = $vnpayService->createPaymentUrl(
            $orderId,
            $amount,
            $orderInfo,
            $ipAddr
        );

        Log::write('info', "Creating VNPay payment - Order: $orderId, Amount: $amount");
        Log::error("Redirecting to VNPay URL: $paymentUrl");

        return $this->redirect($paymentUrl);
    }

    /**
     * VNPay return handler (customer redirect)
     */
    public function vnpayReturn()
    {
        $params = $this->request->getQueryParams();
        Log::error('VNPay Return: ' . json_encode($params));

        $vnpayService = new VNPayService();
        if (!$vnpayService->verifyReturnUrl($params)) {
            $this->Flash->error('Invalid signature! The transaction may be forged.');
            Log::write('error', 'VNPay signature verification failed');
            return $this->redirect(['controller' => 'Pages', 'action' => 'display', 'home']);
        }

        $orderId = $params['vnp_TxnRef'];
        $responseCode = $params['vnp_ResponseCode'];
        $amount = $params['vnp_Amount'] / 100;
        $bankCode = $params['vnp_BankCode'] ?? '';
        $transactionNo = $params['vnp_TransactionNo'] ?? '';

        if ($responseCode == '00') {
            $this->Flash->success('Payment successful!');
            Log::write('info', "VNPay payment success - Order: $orderId, Transaction: $transactionNo");
            try {
                $order = $this->Orders->find()->where(['order_code' => $orderId])->first();
                if ($order) {
                    $order->payment_status = 'paid';
                    $this->Orders->save($order);
                    Log::write('info', "Order $orderId payment_status set to paid");
                }
            } catch (\Exception $e) {
                Log::write('info', "Payment successfully for $orderId: " . $e->getMessage());
            }

            $this->set(compact('orderId', 'amount', 'bankCode', 'transactionNo'));
            $this->viewBuilder()->setTemplate('payment_success');
        } else {
            $message = $vnpayService->getResponseMessage($responseCode);
            $this->Flash->error('Payment failed: ' . $message);

            try {
                $order = $this->Orders->find()->where(['order_code' => $orderId])->first();
                if ($order) {
                    $order->payment_status = 'failed';
                    $this->Orders->save($order);
                    Log::write('info', "Order $orderId payment_status set to failed");
                }
            } catch (\Exception $e) {
                Log::error('error', "Failed to update payment_status for $orderId: " . $e->getMessage());
            }

            Log::error('warning', "VNPay payment failed - Order: $orderId, Code: $responseCode");

            $this->set(compact('orderId', 'message', 'responseCode'));
            $this->viewBuilder()->setTemplate('payment_failed');
        }
    }

    /**
     * VNPay IPN webhook
     */
    public function vnpayIpn()
    {
        $params = $this->request->getQueryParams();

        $vnpayService = new VNPayService();
        $returnData = [];

        if (!$vnpayService->verifyReturnUrl($params)) {
            $returnData['RspCode'] = '97';
            $returnData['Message'] = 'Invalid signature';
        } else {
            $orderId = $params['vnp_TxnRef'];
            $responseCode = $params['vnp_ResponseCode'];

            // TODO: check order existence, verify amount, check status
            if ($responseCode == '00') {
                $returnData['RspCode'] = '00';
                $returnData['Message'] = 'Confirm Success';
                Log::write('info', "VNPay IPN confirmed - Order: $orderId");
            } else {
                $returnData['RspCode'] = '00';
                $returnData['Message'] = 'Confirm Success (Failed payment)';
            }
        }

        $this->autoRender = false;
        $this->response = $this->response
            ->withType('application/json')
            ->withStringBody(json_encode($returnData));

        return $this->response;
    }

    /**
     * Upload payment proof (bank transfer)
     */
    public function uploadPaymentProof($id = null)
    {
        $this->request->allowMethod(['post']);

        $order = $this->Orders->get($id);
        if ($this->request->getData('payment_proof')) {
            $file = $this->request->getData('payment_proof');
            $fileName = $this->uploadFile($file, 'payment_proofs');

            if ($fileName) {
                $order->payment_proof = $fileName;
                $order->payment_status = 'pending';

                if ($this->Orders->save($order)) {
                    $this->Flash->success('Upload successful! We will confirm shortly.');
                    $this->PaymentLog->writeLog($order->id, 'bank_transfer', 'proof_uploaded', $order->total_amount, $this->request->clientIp());
                } else {
                    $this->Flash->error('Could not save information!');
                }
            } else {
                $this->Flash->error('File upload failed!');
            }
        }

        return $this->redirect(['controller' => 'Orders', 'action' => 'view', $id]);
    }

    /**
     * Admin confirms payment
     */
    public function adminConfirmPayment($id = null)
    {
        $this->request->allowMethod(['post']);
        $deny = $this->requireAdmin();
        if ($deny) {
            return $deny;
        }

        $order = $this->Orders->get($id);
        $order->payment_status = 'paid';
        $order->payment_date = date('Y-m-d H:i:s');
        $order->order_status = 'confirmed';

        if ($this->Orders->save($order)) {
            $this->Flash->success('Payment has been confirmed!');
            $this->PaymentLog->writeLog($order->id, $order->payment_method, 'confirmed', $order->total_amount, $this->request->clientIp());
        } else {
            $this->Flash->error('Could not confirm payment!');
        }

        return $this->redirect(['controller' => 'Orders', 'action' => 'view', $id]);
    }

    /**
     * Small helper to upload a file - copied from OrdersController
     */
    private function uploadFile($file, $folder)
    {
        if ($file->getError() === UPLOAD_ERR_OK) {
            $extension = pathinfo($file->getClientFilename(), PATHINFO_EXTENSION);
            $fileName = uniqid() . '.' . $extension;
            $uploadPath = WWW_ROOT . 'uploads' . DS . $folder . DS;

            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0777, true);
            }

            $file->moveTo($uploadPath . $fileName);

            return $fileName;
        }

        return false;
    }
}
