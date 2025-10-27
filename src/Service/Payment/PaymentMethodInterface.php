<?php
namespace App\Service\Payment;


use App\Model\Entity\Order;


interface PaymentMethodInterface
{
    public function pay(Order $order): array; // return ['status' => 'success', 'tx' => '...']
}