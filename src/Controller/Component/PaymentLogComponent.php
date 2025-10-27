<?php
namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\ORM\TableRegistry;

/**
 * PaymentLog component - centralize payment logging
 */
class PaymentLogComponent extends Component
{
    protected $PaymentLogs;

    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->PaymentLogs = TableRegistry::getTableLocator()->get('PaymentLogs');
    }

    /**
     * Write a payment log entry
     *
     * @param int|string $orderId
     * @param string $method
     * @param string $status
     * @param float $amount
     * @param string|null $ip
     * @return void
     */
    public function writeLog($orderId, $method, $status, $amount, $ip = null)
    {
        $log = $this->PaymentLogs->newEntity([
            'order_id' => $orderId,
            'payment_method' => $method,
            'status' => $status,
            'amount' => $amount,
            'ip_address' => $ip,
        ]);

        $this->PaymentLogs->save($log);
    }
}
