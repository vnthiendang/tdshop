<?php
namespace App\Listener;


use Cake\Event\EventInterface;


class PushNotificationListener
{
    public function implementedEvents(): array
    {
        return ['Order.placed' => 'onOrderPlaced'];
    }


    public function onOrderPlaced(EventInterface $event)
    {
        $data = $event->getData();
        $order = $data['order'];

        // call push emulator
        $payload = ['orderId' => $order->id, 'message' => 'Order placed'];

        $ch = curl_init('http://push-emulator:3000/push');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        curl_close($ch);
    }
}