<?php
namespace App\Service\Payment;


use App\Model\Entity\Order;


class PayPalPayment implements PaymentMethodInterface
{
    public function pay(Order $order): array
    {
        // mock call to PayPal
        return ['status' => 'success', 'tx' => 'PP-' . uniqid()];
    }
}